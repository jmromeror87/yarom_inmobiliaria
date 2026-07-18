<?php

namespace App\Console\Commands;

use App\Models\AccountingAccount;
use App\Models\AccountingEntry;
use App\Models\AccountingEntryLine;
use App\Models\AccountingPeriod;
use App\Models\SiinmobHistoricoNota;
use App\Models\Third;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class IntegrarContabilidadHistoricoSiinmob extends Command
{
    protected $signature = 'siinmob:integrar-contabilidad {--dry-run}';

    protected $description = 'Carga las notas contables históricas de Siinmob (2025-2026) como AccountingEntry/AccountingEntryLine reales';

    private array $cuentasPorCodigo = [];
    private array $tercerosPorNombre = [];
    private array $periodosPorLlave = [];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $this->crearCuentasFaltantes($dryRun);
        $this->crearPeriodos($dryRun);
        $this->cargarCuentas();
        $this->cargarTerceros();

        $notas = SiinmobHistoricoNota::with('lineas')->orderBy('fecha')->get();
        $this->info("Notas a procesar: {$notas->count()}");

        $creadas = 0;
        $yaExistian = 0;
        $errores = [];

        foreach ($notas as $nota) {
            $existe = AccountingEntry::where('referencia_tipo', 'historico_siinmob')
                ->where('referencia', $nota->ver_ref)->exists();
            if ($existe) { $yaExistian++; continue; }

            try {
                DB::transaction(function () use ($nota, $dryRun, &$creadas) {
                    $periodoId = $this->periodoId($nota->fecha);
                    $tipo = $this->clasificarTipo($nota->lineas);
                    $thirdId = $this->matchTercero($nota->detalle ?? $nota->concepto ?? '');

                    if ($dryRun) return;

                    $entry = AccountingEntry::create([
                        'tipo' => $tipo,
                        'fecha' => $nota->fecha,
                        'descripcion' => Str::limit($nota->concepto ?: $nota->detalle ?: 'Nota histórica Siinmob', 250),
                        'period_id' => $periodoId,
                        'third_id' => $thirdId,
                        'referencia' => $nota->ver_ref,
                        'referencia_tipo' => 'historico_siinmob',
                        'referencia_id' => $nota->id,
                        'estado' => 'contabilizado',
                        'contabilizado_en' => now(),
                    ]);

                    $orden = 1;
                    foreach ($nota->lineas as $linea) {
                        $accountId = $this->cuentasPorCodigo[$linea->cuenta_codigo] ?? null;
                        if (!$accountId) return; // cuenta desconocida, abortar esta nota (raro, ya casi todo mapeado)

                        AccountingEntryLine::create([
                            'entry_id' => $entry->id,
                            'account_id' => $accountId,
                            'third_id' => $thirdId,
                            'descripcion' => Str::limit($linea->descripcion_linea ?: '', 250),
                            'debito' => $linea->debito,
                            'credito' => $linea->credito,
                            'orden' => $orden++,
                        ]);
                    }

                    $entry->recalcularTotales();
                    $creadas++;
                });
            } catch (\Throwable $e) {
                $errores[] = "{$nota->ver_ref}: " . $e->getMessage();
            }
        }

        $this->info("\nCreadas: {$creadas} | Ya existían: {$yaExistian} | Errores: " . count($errores));
        foreach (array_slice($errores, 0, 20) as $e) $this->error("  {$e}");

        return self::SUCCESS;
    }

    private function crearCuentasFaltantes(bool $dryRun): void
    {
        $codigosUsados = \App\Models\SiinmobHistoricoLinea::select('cuenta_codigo', 'cuenta_nombre')
            ->distinct()->get();

        $existentes = AccountingAccount::pluck('id', 'codigo');
        $creadas = 0;

        foreach ($codigosUsados as $c) {
            if (isset($existentes[$c->cuenta_codigo])) continue;

            $codigo = $c->cuenta_codigo;
            $clase = (string) $codigo[0];
            $naturaleza = in_array($clase, ['1', '5', '6', '7']) ? 'debito' : 'credito';

            // Buscar el padre mas cercano (prefijo mas largo existente)
            $parentId = null;
            for ($len = strlen($codigo) - 1; $len >= 2; $len--) {
                $prefijo = substr($codigo, 0, $len);
                if (isset($existentes[$prefijo])) { $parentId = $existentes[$prefijo]; break; }
            }

            $this->line(($dryRun ? '[DRY-RUN] ' : '') . "Creando cuenta faltante: {$codigo} - {$c->cuenta_nombre}");

            if (!$dryRun) {
                $nueva = AccountingAccount::create([
                    'codigo' => $codigo,
                    'nombre' => Str::ucfirst(Str::lower($c->cuenta_nombre ?: $codigo)),
                    'nivel' => strlen($codigo),
                    'parent_id' => $parentId,
                    'clase' => $clase,
                    'naturaleza' => $naturaleza,
                    'acepta_movimiento' => true,
                    'requiere_tercero' => false,
                ]);
                $existentes[$codigo] = $nueva->id;
            }
            $creadas++;
        }

        $this->info("Cuentas nuevas creadas: {$creadas}");
    }

    private function crearPeriodos(bool $dryRun): void
    {
        $rangos = SiinmobHistoricoNota::selectRaw('MIN(fecha) as min_fecha, MAX(fecha) as max_fecha')->first();
        if (!$rangos->min_fecha) return;

        $inicio = \Carbon\Carbon::parse($rangos->min_fecha)->startOfMonth();
        $fin = \Carbon\Carbon::parse($rangos->max_fecha)->startOfMonth();

        $existentes = AccountingPeriod::get()->keyBy(fn ($p) => $p->anio . '-' . $p->mes);
        $creados = 0;

        for ($cursor = $inicio->copy(); $cursor->lte($fin); $cursor->addMonth()) {
            $llave = $cursor->year . '-' . $cursor->month;
            if (isset($existentes[$llave])) continue;

            $this->line(($dryRun ? '[DRY-RUN] ' : '') . "Creando periodo: {$cursor->year}-{$cursor->month}");
            if (!$dryRun) {
                AccountingPeriod::create([
                    'anio' => $cursor->year,
                    'mes' => $cursor->month,
                    'estado' => 'cerrado',
                    'notas' => 'Periodo histórico migrado de Siinmob',
                ]);
            }
            $creados++;
        }
        $this->info("Periodos nuevos creados: {$creados}");
    }

    private function cargarCuentas(): void
    {
        $this->cuentasPorCodigo = AccountingAccount::pluck('id', 'codigo')->all();
    }

    private function cargarTerceros(): void
    {
        foreach (Third::get(['id', 'nombre_completo']) as $t) {
            if ($t->nombre_completo) {
                $this->tercerosPorNombre[$this->normalizar($t->nombre_completo)] = $t->id;
            }
        }
    }

    private function normalizar(string $s): string
    {
        return trim(preg_replace('/\s+/', ' ', Str::upper($s)));
    }

    private function matchTercero(string $texto): ?int
    {
        // El campo "detalle"/"concepto" a veces empieza con el nombre del tercero
        $candidato = trim(explode('|', $texto)[0]);
        $norm = $this->normalizar($candidato);
        return $this->tercerosPorNombre[$norm] ?? null;
    }

    private function periodoId(\Carbon\Carbon $fecha): int
    {
        $llave = $fecha->year . '-' . $fecha->month;
        if (!isset($this->periodosPorLlave[$llave])) {
            $p = AccountingPeriod::where('anio', $fecha->year)->where('mes', $fecha->month)->first();
            $this->periodosPorLlave[$llave] = $p?->id;
        }
        return $this->periodosPorLlave[$llave];
    }

    private function clasificarTipo($lineas): string
    {
        $ingresos = 0; $gastos = 0;
        foreach ($lineas as $l) {
            if (str_starts_with($l->cuenta_codigo, '4')) $ingresos += (float) $l->credito;
            if (str_starts_with($l->cuenta_codigo, '5') || str_starts_with($l->cuenta_codigo, '6')) $gastos += (float) $l->debito;
        }
        if ($ingresos > 0 && $ingresos >= $gastos) return 'CI';
        if ($gastos > 0) return 'CE';
        return 'CC';
    }
}

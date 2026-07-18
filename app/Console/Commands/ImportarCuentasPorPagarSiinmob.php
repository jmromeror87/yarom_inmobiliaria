<?php

namespace App\Console\Commands;

use App\Models\CuentaPorPagarPropietario;
use App\Models\Third;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ImportarCuentasPorPagarSiinmob extends Command
{
    protected $signature = 'siinmob:importar-cuentas-por-pagar {--dry-run}';

    protected $description = 'Importa el saldo pendiente por girar a cada propietario (corte 30-jun-2026) de Siinmob como Cuentas por Pagar iniciales';

    private function normalizar(string $s): string
    {
        return trim(preg_replace('/\s+/', ' ', Str::upper($s)));
    }

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $ruta = storage_path('app/cartera_cxp_30jun2026.json');
        if (!file_exists($ruta)) {
            $this->error("No existe {$ruta}");
            return self::FAILURE;
        }

        $registros = json_decode(file_get_contents($ruta), true);
        $this->info('Registros con saldo pendiente en Siinmob: ' . count($registros));

        $terceros = Third::get(['id', 'nombre_completo']);
        $porNombre = [];
        foreach ($terceros as $t) {
            if ($t->nombre_completo) {
                $porNombre[$this->normalizar($t->nombre_completo)] = $t->id;
            }
        }

        $creados = 0;
        $sinMatch = [];

        foreach ($registros as $r) {
            $saldo = (float) str_replace(',', '', $r['saldo_actual']);
            if ($saldo <= 0) continue;

            $nombreNorm = $this->normalizar($r['tercero']);
            $thirdId = $porNombre[$nombreNorm] ?? null;

            if (!$thirdId) {
                $sinMatch[] = $r['tercero'] . ' ($' . number_format($saldo, 0, ',', '.') . ')';
                continue;
            }

            $existe = CuentaPorPagarPropietario::where('third_id', $thirdId)
                ->where('tipo', 'saldo_inicial_siinmob')->exists();
            if ($existe) continue;

            $this->line(($dryRun ? '[DRY-RUN] ' : '') . "Creando CxP: {$r['tercero']} -> \${$saldo}");

            if (!$dryRun) {
                CuentaPorPagarPropietario::create([
                    'tipo' => 'saldo_inicial_siinmob',
                    'concepto' => 'Saldo pendiente de girar heredado del sistema anterior (Siinmob) — corte 30/06/2026',
                    'third_id' => $thirdId,
                    'valor_original' => $saldo,
                    'valor_pagado' => 0,
                    'estado' => 'pendiente',
                    'fecha_origen' => '2026-06-30',
                    'notas' => 'Migrado automáticamente desde el reporte de cartera consolidada (CxP) de Siinmob.',
                ]);
            }
            $creados++;
        }

        $this->info("\nCreados: {$creados}");
        $this->warn('Sin match (revisar manualmente): ' . count($sinMatch));
        foreach ($sinMatch as $s) {
            $this->line("  - {$s}");
        }

        return self::SUCCESS;
    }
}

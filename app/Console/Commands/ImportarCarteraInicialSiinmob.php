<?php

namespace App\Console\Commands;

use App\Models\CuentaPorCobrar;
use App\Models\Third;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ImportarCarteraInicialSiinmob extends Command
{
    protected $signature = 'siinmob:importar-cartera-inicial {--dry-run}';

    protected $description = 'Importa el saldo pendiente por arrendatario (corte 30-jun-2026) de Siinmob como Cuentas por Cobrar iniciales';

    private function normalizar(string $s): string
    {
        $s = Str::upper(trim($s));
        $s = preg_replace('/\s+/', ' ', $s);
        return $s;
    }

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $ruta = storage_path('app/cartera_cxc_30jun2026.json');
        if (!file_exists($ruta)) {
            $this->error("No existe {$ruta}");
            return self::FAILURE;
        }

        $registros = json_decode(file_get_contents($ruta), true);
        $this->info('Registros con saldo pendiente en Siinmob: ' . count($registros));

        // Mapa de terceros locales por nombre normalizado (no filtramos por rol:
        // hay terceros que son propietarios en el sistema nuevo pero también
        // arrendaban otro inmueble en Siinmob, y viceversa)
        $terceros = Third::get(['id', 'nombre_completo']);
        $porNombre = [];
        foreach ($terceros as $t) {
            $porNombre[$this->normalizar($t->nombre_completo)] = $t->id;
        }

        $creados = 0;
        $sinMatch = [];

        foreach ($registros as $r) {
            $saldo = (float) str_replace(',', '', $r['saldo_actual']);
            if ($saldo <= 0) continue; // solo nos interesa lo que deben (positivo)

            $nombreNorm = $this->normalizar($r['tercero']);
            $thirdId = $porNombre[$nombreNorm] ?? null;

            if (!$thirdId) {
                $sinMatch[] = $r['tercero'] . ' ($' . number_format($saldo, 0, ',', '.') . ')';
                continue;
            }

            // Evitar duplicar si ya se importó antes
            $existe = CuentaPorCobrar::where('third_id', $thirdId)
                ->where('tipo', 'saldo_inicial_siinmob')
                ->exists();
            if ($existe) continue;

            $this->line(($dryRun ? '[DRY-RUN] ' : '') . "Creando CxC: {$r['tercero']} -> \${$saldo}");

            if (!$dryRun) {
                CuentaPorCobrar::create([
                    'tipo' => 'saldo_inicial_siinmob',
                    'concepto' => 'Saldo pendiente heredado del sistema anterior (Siinmob) — corte 30/06/2026',
                    'third_id' => $thirdId,
                    'valor_original' => $saldo,
                    'valor_pagado' => 0,
                    'estado' => 'pendiente',
                    'fecha_origen' => '2026-06-30',
                    'notas' => 'Migrado automáticamente desde el reporte de cartera consolidada de Siinmob.',
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

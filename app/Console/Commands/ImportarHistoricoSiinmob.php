<?php

namespace App\Console\Commands;

use App\Models\SiinmobHistoricoLinea;
use App\Models\SiinmobHistoricoNota;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportarHistoricoSiinmob extends Command
{
    protected $signature = 'siinmob:importar-historico {--archivo=storage/app/siinmob_detalle_2025_2026.json}';

    protected $description = 'Importa el archivo JSON de notas contables extraídas de Siinmob a las tablas de histórico';

    public function handle(): int
    {
        $ruta = base_path($this->option('archivo'));
        if (!file_exists($ruta)) {
            $this->error("No existe el archivo: {$ruta}");
            return self::FAILURE;
        }

        $notas = json_decode(file_get_contents($ruta), true);
        $this->info('Notas en el archivo: ' . count($notas));

        $creadas = 0;
        $actualizadas = 0;

        DB::transaction(function () use ($notas, &$creadas, &$actualizadas) {
            foreach ($notas as $n) {
                $totalDeb = array_sum(array_column($n['lineas'], 'debito'));
                $totalCred = array_sum(array_column($n['lineas'], 'credito'));

                $nota = SiinmobHistoricoNota::updateOrCreate(
                    ['ver_ref' => $n['ver_ref']],
                    [
                        'fecha' => $n['fecha'],
                        'tipo' => $n['tipo'],
                        'nota_numero' => $n['nota_numero'] ?? null,
                        'transaccion' => $n['transaccion'] ?? null,
                        'detalle' => $n['detalle'] ?? null,
                        'creada_por' => $n['creada_por'] ?? null,
                        'concepto' => $n['concepto'] ?? null,
                        'total_debito' => $totalDeb,
                        'total_credito' => $totalCred,
                    ]
                );

                $nota->wasRecentlyCreated ? $creadas++ : $actualizadas++;

                $nota->lineas()->delete();
                foreach ($n['lineas'] as $l) {
                    SiinmobHistoricoLinea::create([
                        'nota_id' => $nota->id,
                        'cuenta_codigo' => $l['cuenta_codigo'],
                        'cuenta_nombre' => $l['cuenta_nombre'],
                        'descripcion_linea' => $l['descripcion_linea'],
                        'debito' => $l['debito'],
                        'credito' => $l['credito'],
                    ]);
                }
            }
        });

        $this->info("Creadas: {$creadas} | Actualizadas: {$actualizadas}");

        return self::SUCCESS;
    }
}

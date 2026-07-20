<?php

namespace App\Console\Commands;

use App\Models\AccountingEntry;
use App\Models\Third;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class VincularHistoricoPorContrato extends Command
{
    protected $signature = 'siinmob:vincular-historico-por-contrato {--dry-run}';

    protected $description = 'Vincula notas del histórico Siinmob a su tercero cruzando el número de contrato de arrendamiento mencionado en la descripción del asiento contra el mapa de contratos scrapeado';

    private function normalizar(string $s): string
    {
        return trim(preg_replace('/\s+/', ' ', Str::upper($s)));
    }

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $ruta = storage_path('app/mapa_contratos_siinmob.json');

        if (!file_exists($ruta)) {
            $this->error("No existe {$ruta}");
            return self::FAILURE;
        }

        $mapaContratos = json_decode(file_get_contents($ruta), true);
        $this->info('Contratos mapeados: ' . count($mapaContratos));

        $terceros = Third::get(['id', 'nombre_completo']);
        $porNombre = [];
        foreach ($terceros as $t) {
            if ($t->nombre_completo) {
                $porNombre[$this->normalizar($t->nombre_completo)] = $t->id;
            }
        }

        $entries = AccountingEntry::where('referencia_tipo', 'historico_siinmob')
            ->whereNull('third_id')
            ->where('descripcion', 'like', '%contrato de arrendamiento%')
            ->get(['id', 'descripcion']);

        $this->info('Entries candidatas (sin third_id, con patrón de contrato): ' . $entries->count());

        $vinculados = 0;
        $sinMapa = 0;
        $sinTercero = 0;

        foreach ($entries as $entry) {
            if (!preg_match('/contrato de arrendamiento (\d+)/i', $entry->descripcion, $m)) {
                continue;
            }

            $numeroContrato = $m[1];
            $nombreInquilino = $mapaContratos[$numeroContrato] ?? null;

            if (!$nombreInquilino) {
                $sinMapa++;
                continue;
            }

            $thirdId = $porNombre[$this->normalizar($nombreInquilino)] ?? null;

            if (!$thirdId) {
                $sinTercero++;
                continue;
            }

            if (!$dryRun) {
                AccountingEntry::where('id', $entry->id)->update(['third_id' => $thirdId]);
                \App\Models\AccountingEntryLine::where('entry_id', $entry->id)->update(['third_id' => $thirdId]);
            }
            $vinculados++;
        }

        $this->info("\nVinculados: {$vinculados}");
        $this->warn("Sin contrato en el mapa: {$sinMapa}");
        $this->warn("Contrato mapeado pero tercero no encontrado por nombre: {$sinTercero}");

        return self::SUCCESS;
    }
}

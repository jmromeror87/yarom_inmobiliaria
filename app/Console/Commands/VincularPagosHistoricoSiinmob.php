<?php

namespace App\Console\Commands;

use App\Models\AccountingEntryLine;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class VincularPagosHistoricoSiinmob extends Command
{
    protected $signature = 'siinmob:vincular-pagos-por-periodo {--dry-run}';

    protected $description = 'Vincula recibos de ingreso (RI) del histórico Siinmob a su tercero cruzando período+monto contra cargos (autocargas) ya identificados, ya que Siinmob no guarda el nombre del inquilino en la nota de pago';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $cargos = AccountingEntryLine::whereHas('account', fn ($q) => $q->where('codigo', '13050501'))
            ->where('debito', '>', 0)
            ->whereNotNull('third_id')
            ->select('id', 'entry_id', 'third_id', 'debito', 'descripcion')
            ->get();

        $map = [];
        foreach ($cargos as $c) {
            if (preg_match('/(\d{4}-\d{1,2}-\d{1,2})\s*AL\s*(\d{4}-\d{1,2}-\d{1,2})/i', $c->descripcion, $m)) {
                $key = $m[1] . '|' . $m[2] . '|' . number_format((float) $c->debito, 2, '.', '');
                $map[$key][] = $c->third_id;
            }
        }

        $pagos = AccountingEntryLine::whereHas('account', fn ($q) => $q->where('codigo', '13050501'))
            ->where('credito', '>', 0)
            ->whereNull('third_id')
            ->select('id', 'entry_id', 'credito', 'descripcion')
            ->get();

        $vinculados = 0;
        $ambiguos = 0;

        foreach ($pagos as $p) {
            if (!preg_match('/(\d{4}-\d{1,2}-\d{1,2})\s*AL\s*(\d{4}-\d{1,2}-\d{1,2})/i', $p->descripcion, $m)) {
                continue;
            }

            $key = $m[1] . '|' . $m[2] . '|' . number_format((float) $p->credito, 2, '.', '');
            if (!isset($map[$key])) {
                continue;
            }

            $distinct = array_unique($map[$key]);
            if (count($distinct) !== 1) {
                $ambiguos++;
                continue;
            }

            $thirdId = $distinct[0];

            $this->line(($dryRun ? '[DRY-RUN] ' : '') . "Vinculando entry {$p->entry_id} -> third_id {$thirdId}");

            if (!$dryRun) {
                DB::table('accounting_entry_lines')->where('entry_id', $p->entry_id)->update(['third_id' => $thirdId]);
                DB::table('accounting_entries')->where('id', $p->entry_id)->whereNull('third_id')->update(['third_id' => $thirdId]);
            }
            $vinculados++;
        }

        $this->info("\nVinculados: {$vinculados}");
        $this->warn("Ambiguos (mismo período+monto, distintos terceros — no vinculados): {$ambiguos}");

        return self::SUCCESS;
    }
}

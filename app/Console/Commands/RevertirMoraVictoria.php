<?php

namespace App\Console\Commands;

use App\Models\AccountingEntry;
use App\Models\RentBill;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Revierte la mora que VerificarMoraJob ya había acumulado sobre facturas
 * de inmuebles de "Victoria" antes de excluirlos del cálculo de mora
 * (por acuerdo: a Victoria no se le aplica mora — es un portafolio de
 * contingencia migrado de otra inmobiliaria).
 *
 * Anula los comprobantes contables de mora ya generados y resetea los
 * campos de mora en la factura, recalculando el estado real según lo
 * pagado (sin mora).
 *
 * Uso: php artisan victoria:revertir-mora [--dry-run]
 */
class RevertirMoraVictoria extends Command
{
    protected $signature = 'victoria:revertir-mora {--dry-run : Solo mostrar los cambios sin aplicarlos}';

    protected $description = 'Revierte mora ya acumulada y su contabilización en facturas de inmuebles de Victoria';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $bills = RentBill::whereHas('property.businessOrigin', fn ($q) => $q->where('nombre', 'Victoria'))
            ->where('mora_acumulada', '>', 0)
            ->get();

        $this->info('Facturas de Victoria con mora a revertir: ' . $bills->count());

        if ($bills->isEmpty()) {
            return self::SUCCESS;
        }

        $entries = AccountingEntry::whereIn('referencia_id', $bills->pluck('id'))
            ->where('referencia_tipo', 'like', 'mora_rent_bill%')
            ->where('estado', '!=', 'anulado')
            ->get();

        $this->info('Asientos contables de mora a anular: ' . $entries->count());

        foreach ($bills as $bill) {
            $totalPagado = (float) $bill->total_pagado;
            $saldo       = max(0, (float) $bill->total_factura - $totalPagado);
            $nuevoEstado = $saldo <= 0 ? 'pagada' : ($totalPagado > 0 ? 'parcial' : 'pendiente');

            $this->line(($dryRun ? '[DRY-RUN] ' : '') . "  {$bill->numero}: mora {$bill->mora_acumulada} -> 0, estado {$bill->estado} -> {$nuevoEstado}");
        }

        if ($dryRun) {
            foreach ($entries as $e) {
                $this->line("  [DRY-RUN] anular asiento {$e->numero} ({$e->referencia})");
            }
            return self::SUCCESS;
        }

        DB::transaction(function () use ($bills, $entries) {
            foreach ($entries as $e) {
                $e->update(['estado' => 'anulado']);
            }

            foreach ($bills as $bill) {
                $totalPagado = (float) $bill->total_pagado;
                $saldo       = max(0, (float) $bill->total_factura - $totalPagado);
                $nuevoEstado = $saldo <= 0 ? 'pagada' : ($totalPagado > 0 ? 'parcial' : 'pendiente');

                $bill->update([
                    'mora_acumulada'    => 0,
                    'dias_mora'         => 0,
                    'fecha_inicio_mora' => null,
                    'saldo_pendiente'   => $saldo,
                    'estado'            => $nuevoEstado,
                ]);
            }
        });

        $this->info("Revertidas: {$bills->count()} facturas, {$entries->count()} asientos anulados.");

        return self::SUCCESS;
    }
}

<?php

namespace App\Console\Commands;

use App\Models\AccountingEntry;
use App\Models\AccountingEntryLine;
use App\Models\RentBill;
use App\Models\RentPayment;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ConciliarPagosPeriodoActualHistorico extends Command
{
    protected $signature = 'facturas:conciliar-con-historico {--mes=} {--anio=} {--origen=} {--dry-run}';

    protected $description = 'Cruza las facturas del período contra el histórico Siinmob (período+monto exacto) y registra como pagadas, con su fecha y forma de pago reales, las que ya se habían cancelado en el sistema anterior antes del corte';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $mes = (int) ($this->option('mes') ?: now()->month);
        $anio = (int) ($this->option('anio') ?: now()->year);
        $origen = $this->option('origen') ? (int) $this->option('origen') : null;

        $bills = RentBill::whereIn('estado', ['pendiente', 'parcial', 'en_mora'])
            ->when($origen, fn ($q) => $q->whereHas('property', fn ($p) => $p->where('business_origin_id', $origen)))
            ->where('mes', $mes)->where('anio', $anio)
            ->with('arrendatario')
            ->get();

        $cargosTodos = AccountingEntryLine::whereHas('account', fn ($q) => $q->where('codigo', '13050501'))
            ->where('debito', '>', 0)->whereNotNull('third_id')
            ->select('entry_id', 'third_id', 'debito', 'descripcion')->get()->groupBy('third_id');

        $pagosTodos = AccountingEntryLine::whereHas('account', fn ($q) => $q->where('codigo', '13050501'))
            ->where('credito', '>', 0)->whereNotNull('third_id')
            ->select('entry_id', 'third_id', 'credito', 'descripcion')->get()->groupBy('third_id');

        $inicioMes = Carbon::create($anio, $mes, 1)->startOfMonth();
        $finMes = Carbon::create($anio, $mes, 1)->endOfMonth();

        $conciliados = 0;
        $omitidos = 0;

        foreach ($bills as $bill) {
            $thirdId = $bill->arrendatario_id;
            $cargosTercero = $cargosTodos->get($thirdId) ?? collect();
            $pagosTercero = $pagosTodos->get($thirdId) ?? collect();

            $match = null;
            foreach ($cargosTercero as $cargo) {
                if (!preg_match('/(\d{4}-\d{1,2}-\d{1,2})\s*AL\s*(\d{4}-\d{1,2}-\d{1,2})/i', $cargo->descripcion, $m)) {
                    continue;
                }
                try {
                    $d1 = Carbon::parse($m[1]);
                    $d2 = Carbon::parse($m[2]);
                } catch (\Throwable $e) {
                    continue;
                }
                if ($d1->gt($finMes) || $d2->lt($inicioMes)) {
                    continue;
                }

                foreach ($pagosTercero as $pago) {
                    if (!preg_match('/(\d{4}-\d{1,2}-\d{1,2})\s*AL\s*(\d{4}-\d{1,2}-\d{1,2})/i', $pago->descripcion, $mp)) {
                        continue;
                    }
                    try {
                        $pd1 = Carbon::parse($mp[1]);
                        $pd2 = Carbon::parse($mp[2]);
                    } catch (\Throwable $e) {
                        continue;
                    }
                    if ($pd1->eq($d1) && $pd2->eq($d2) && abs($pago->credito - $cargo->debito) < 1) {
                        $match = ['pago' => $pago, 'cargo' => $cargo];
                        break 2;
                    }
                }
            }

            if (!$match) {
                continue;
            }

            // Verificación de seguridad: el monto pagado debe coincidir con la
            // factura del sistema nuevo — si no coincide, no se toca (revisión manual).
            if (abs((float) $match['pago']->credito - (float) $bill->total_factura) > 1) {
                $this->warn("SALTADO (monto no coincide) {$bill->numero} — {$bill->arrendatario?->nombre_completo}: factura {$bill->total_factura} vs pago histórico {$match['pago']->credito}");
                $omitidos++;
                continue;
            }

            $entryPago = AccountingEntry::find($match['pago']->entry_id);
            $fechaPago = $entryPago->fecha;

            // Forma de pago: buscar la contrapartida de caja/banco en el mismo asiento
            $disponible = AccountingEntryLine::where('entry_id', $match['pago']->entry_id)
                ->whereHas('account', fn ($q) => $q->where('codigo', 'like', '11%'))
                ->with('account')->first();
            $esCaja = $disponible && str_contains(strtolower($disponible->account?->nombre ?? ''), 'caja');
            $formaPago = $esCaja ? 'efectivo' : 'transferencia';

            $this->line(($dryRun ? '[DRY-RUN] ' : '') . "Conciliando {$bill->numero} — {$bill->arrendatario?->nombre_completo} — \${$bill->total_factura} — {$fechaPago->format('Y-m-d')} — {$formaPago}");

            if (!$dryRun) {
                DB::transaction(function () use ($bill, $fechaPago, $formaPago) {
                    RentPayment::create([
                        'rent_bill_id' => $bill->id,
                        'rental_contract_id' => $bill->rental_contract_id,
                        'arrendatario_id' => $bill->arrendatario_id,
                        'registrado_por' => Auth::id(),
                        'valor_canon' => $bill->total_factura - $bill->mora_acumulada,
                        'valor_mora' => $bill->mora_acumulada,
                        'valor_administracion' => $bill->cuota_administracion,
                        'total_pagado' => $bill->total_factura + $bill->mora_acumulada,
                        'forma_pago' => $formaPago,
                        'fecha_pago' => $fechaPago,
                        'referencia_pago' => 'Conciliado desde histórico Siinmob (pre-corte)',
                        'notas' => 'Pago ya realizado en el sistema anterior antes del corte del 30/06/2026 — conciliado automáticamente por período+monto exacto.',
                    ]);
                });
            }
            $conciliados++;
        }

        $this->info("\nConciliados: {$conciliados}");
        $this->warn("Omitidos por monto no coincidente: {$omitidos}");

        return self::SUCCESS;
    }
}

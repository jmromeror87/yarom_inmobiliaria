<?php

namespace App\Console\Commands;

use App\Models\AccountingEntryLine;
use App\Models\RentalContract;
use App\Models\RentBill;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class CorregirDiaPagoYFechaLimite extends Command
{
    protected $signature = 'facturas:corregir-dia-pago {--mes=} {--anio=} {--origen=} {--dry-run}';

    protected $description = 'Repuebla dia_pago en RentalContract a partir del patrón real del histórico Siinmob y recalcula fecha_limite_pago en las facturas ya generadas del período';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $mes = (int) ($this->option('mes') ?: now()->month);
        $anio = (int) ($this->option('anio') ?: now()->year);
        $origen = $this->option('origen') ? (int) $this->option('origen') : null;

        // ── PASO 1: repoblar dia_pago desde el patrón real del histórico ──
        $this->info('=== PASO 1: repoblar dia_pago desde el histórico ===');
        $contratos = RentalContract::where('estado', 'activo')
            ->when($origen, fn ($q) => $q->whereHas('property', fn ($p) => $p->where('business_origin_id', $origen)))
            ->whereNull('dia_pago')
            ->get();

        $actualizados = 0;
        $sinHistorico = 0;

        foreach ($contratos as $contrato) {
            $thirdId = $contrato->arrendatario_id;
            $cargos = AccountingEntryLine::where('accounting_entry_lines.third_id', $thirdId)
                ->whereHas('account', fn ($q) => $q->where('codigo', '13050501'))
                ->where('debito', '>', 0)->get();

            $dias = [];
            foreach ($cargos as $cg) {
                if (preg_match('/(\d{4}-\d{1,2}-\d{1,2})\s*AL/i', $cg->descripcion, $m)) {
                    try {
                        $dias[] = Carbon::parse($m[1])->day;
                    } catch (\Throwable $e) {
                        continue;
                    }
                }
            }

            if (empty($dias)) {
                $sinHistorico++;
                continue;
            }

            $conteo = array_count_values($dias);
            arsort($conteo);
            $diaMasFrecuente = (int) array_key_first($conteo);

            $this->line(($dryRun ? '[DRY-RUN] ' : '') . "Contrato {$contrato->numero_contrato} — dia_pago -> {$diaMasFrecuente} (consistencia " . round($conteo[$diaMasFrecuente] / count($dias) * 100, 1) . "%, {$conteo[$diaMasFrecuente]}/" . count($dias) . ')');

            if (!$dryRun) {
                $contrato->update(['dia_pago' => $diaMasFrecuente]);
            }
            $actualizados++;
        }
        $this->info("Contratos actualizados: {$actualizados}");
        $this->warn("Sin histórico para determinar día de pago (quedan con el día de corte general): {$sinHistorico}");

        // ── PASO 2: recalcular fecha_limite_pago en las facturas ya generadas ──
        $this->info("\n=== PASO 2: recalcular fecha_limite_pago en facturas del período ===");
        $company = \App\Models\Company::first();
        $diaCorteGlobal = $company?->dia_corte_mensual ?? 5;
        $periodoBase = Carbon::create($anio, $mes, 1);

        $bills = RentBill::where('mes', $mes)->where('anio', $anio)
            ->when($origen, fn ($q) => $q->whereHas('property', fn ($p) => $p->where('business_origin_id', $origen)))
            ->whereIn('estado', ['pendiente', 'parcial', 'en_mora', 'vencida'])
            ->with('rentalContract')
            ->get();

        $recalculadas = 0;
        foreach ($bills as $bill) {
            $contrato = $bill->rentalContract;
            $diaPago = $contrato->dia_pago ?: $diaCorteGlobal;
            $diaPago = min($diaPago, $periodoBase->copy()->endOfMonth()->day);
            $nuevaFecha = $periodoBase->copy()->startOfMonth()->addDays($diaPago - 1);

            if ($nuevaFecha->toDateString() === $bill->fecha_limite_pago->toDateString()) {
                continue; // sin cambio
            }

            $this->line(($dryRun ? '[DRY-RUN] ' : '') . "{$bill->numero} — fecha_limite_pago {$bill->fecha_limite_pago->format('Y-m-d')} -> {$nuevaFecha->format('Y-m-d')}");

            if (!$dryRun) {
                $bill->update([
                    'fecha_limite_pago' => $nuevaFecha,
                    // Resetear mora — se recalculará correctamente con mora:verificar
                    'estado' => $bill->estado === 'en_mora' ? 'pendiente' : $bill->estado,
                    'dias_mora' => 0,
                    'mora_acumulada' => 0,
                    'fecha_inicio_mora' => null,
                ]);
            }
            $recalculadas++;
        }
        $this->info("Facturas recalculadas: {$recalculadas}");

        return self::SUCCESS;
    }
}

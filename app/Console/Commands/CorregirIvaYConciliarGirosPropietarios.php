<?php

namespace App\Console\Commands;

use App\Models\AccountingAccount;
use App\Models\AccountingEntry;
use App\Models\AccountingEntryLine;
use App\Models\OwnerLiquidation;
use App\Models\Property;
use App\Models\RentalContract;
use App\Models\Third;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CorregirIvaYConciliarGirosPropietarios extends Command
{
    protected $signature = 'facturas:corregir-iva-y-conciliar-giros {--mes=} {--anio=} {--origen=} {--dry-run}';

    protected $description = 'Corrige el third_id mal etiquetado en la cuenta 23354001, marca las propiedades que sí llevan IVA en la comisión según el histórico, corrige las liquidaciones ya generadas con IVA faltante, y concilia contra el histórico los giros a propietarios ya pagados antes del corte';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $mes = (int) ($this->option('mes') ?: now()->month);
        $anio = (int) ($this->option('anio') ?: now()->year);
        $origen = $this->option('origen') ? (int) $this->option('origen') : null;

        // ── PASO 1: corregir third_id mal etiquetado (inquilino en vez de propietario) en 23354001 ──
        $this->info('=== PASO 1: corregir third_id en línea 23354001 ===');
        $lineas = AccountingEntryLine::whereHas('account', fn ($q) => $q->where('codigo', '23354001'))
            ->where('credito', '>', 0)->whereNotNull('third_id')->get();

        $corregidasThird = 0;
        foreach ($lineas as $l) {
            if (Property::where('propietario_id', $l->third_id)->exists()) {
                continue; // ya es un propietario real
            }
            $contrato = RentalContract::where('arrendatario_id', $l->third_id)->where('estado', 'activo')->first();
            if (!$contrato || !$contrato->property || !$contrato->property->propietario_id) {
                continue;
            }
            if (!$dryRun) {
                $l->update(['third_id' => $contrato->property->propietario_id]);
            }
            $corregidasThird++;
        }
        $this->info("Líneas corregidas: {$corregidasThird}");

        // ── PASO 2: marcar propiedades que consistentemente llevan IVA en la comisión ──
        $this->info("\n=== PASO 2: marcar propiedades con IVA en comisión (según histórico) ===");
        $propiedades = Property::whereHas('rentalContracts', fn ($q) => $q->where('estado', 'activo'))
            ->when($origen, fn ($q) => $q->where('business_origin_id', $origen))
            ->get(['id', 'propietario_id']);

        $marcadas = 0;
        foreach ($propiedades as $prop) {
            $contratos = RentalContract::where('property_id', $prop->id)->pluck('arrendatario_id');
            if ($contratos->isEmpty()) continue;
            $comisiones = AccountingEntryLine::whereIn('accounting_entry_lines.third_id', $contratos)
                ->whereHas('account', fn ($q) => $q->where('codigo', '41551001'))
                ->where('credito', '>', 0)->get();
            if ($comisiones->isEmpty()) continue;

            $conIva = 0;
            $sinIva = 0;
            foreach ($comisiones as $c) {
                $tieneIva = AccountingEntryLine::where('entry_id', $c->entry_id)
                    ->whereHas('account', fn ($q) => $q->where('codigo', '24080101'))
                    ->where('credito', '>', 0)->exists();
                $tieneIva ? $conIva++ : $sinIva++;
            }

            if ($sinIva === 0 && $conIva > 0) {
                if (!$dryRun) {
                    Property::where('id', $prop->id)->update(['requiere_iva_override' => true]);
                }
                $marcadas++;
            } elseif ($conIva > 0 && $sinIva > 0) {
                $this->warn("Propiedad {$prop->id}: histórico mixto (con_iva={$conIva}, sin_iva={$sinIva}) — revisar manualmente, no se marca automático");
            }
        }
        $this->info("Propiedades marcadas requiere_iva_override=true: {$marcadas}");

        // ── PASO 3: corregir liquidaciones ya generadas con IVA faltante ──
        $this->info("\n=== PASO 3: corregir liquidaciones del período con IVA faltante ===");
        $liquidaciones = OwnerLiquidation::where('mes', $mes)->where('anio', $anio)
            ->when($origen, fn ($q) => $q->whereHas('property', fn ($p) => $p->where('business_origin_id', $origen)))
            ->get();

        $corregidasLiq = 0;
        foreach ($liquidaciones as $liq) {
            $property = $liq->property;
            if (!$property || !$property->requiereIva() || $liq->iva_comision > 0) {
                continue;
            }
            $ivaCorrecta = round($liq->comision_valor * 0.19, 2);
            $netoNuevo = round($liq->canon_cobrado - $liq->comision_valor - $ivaCorrecta - $liq->retefuente_valor - $liq->seguro_sura_deducido - $liq->otros_descuentos, 2);

            $this->line(($dryRun ? '[DRY-RUN] ' : '') . "Corrigiendo {$liq->numero} — iva 0 -> {$ivaCorrecta} | total_giro {$liq->total_giro} -> {$netoNuevo}");

            if (!$dryRun) {
                DB::transaction(function () use ($liq, $ivaCorrecta, $netoNuevo) {
                    $liq->update(['iva_comision' => $ivaCorrecta, 'total_giro' => $netoNuevo]);

                    $entry = AccountingEntry::where('referencia_tipo', 'factura_rent_bill')
                        ->whereHas('lines', fn ($q) => $q->where('third_id', $liq->propietario_id)->whereHas('account', fn ($a) => $a->where('codigo', '23354001')))
                        ->whereHas('lines', fn ($q) => $q->where('credito', $liq->total_giro + $ivaCorrecta))
                        ->first();

                    // Fallback: buscar por rental_contract/mes/anio si el filtro anterior no encuentra
                    if (!$entry) {
                        $bill = \App\Models\RentBill::where('owner_liquidation_id', $liq->id)->first();
                        if ($bill) {
                            $entry = AccountingEntry::where('referencia_tipo', 'factura_rent_bill')->where('referencia_id', $bill->id)->first();
                        }
                    }

                    if ($entry) {
                        $lineaNeto = $entry->lines()->whereHas('account', fn ($q) => $q->where('codigo', '23354001'))->first();
                        $lineaIvaExistente = $entry->lines()->whereHas('account', fn ($q) => $q->where('codigo', '24080101'))->first();

                        if (!$lineaIvaExistente && $lineaNeto) {
                            $cuentaIva = AccountingAccount::where('codigo', '24080101')->value('id');
                            $entry->lines()->create([
                                'account_id' => $cuentaIva, 'debito' => 0, 'credito' => $ivaCorrecta,
                                'descripcion' => 'IVA 19% sobre comisión (corrección)', 'orden' => 99,
                            ]);
                            $lineaNeto->update(['credito' => $netoNuevo]);
                            $entry->recalcularTotales();
                        }
                    }
                });
            }
            $corregidasLiq++;
        }
        $this->info("Liquidaciones corregidas: {$corregidasLiq}");

        // ── PASO 4: conciliar giros a propietarios ya pagados en el histórico ──
        $this->info("\n=== PASO 4: conciliar giros a propietarios ya pagados antes del corte ===");
        $girosTodos = AccountingEntryLine::whereHas('account', fn ($q) => $q->where('codigo', '23354001'))
            ->where('debito', '>', 0)->select('entry_id', 'debito', 'descripcion')->get();

        $inicioMes = Carbon::create($anio, $mes, 1)->startOfMonth();
        $finMes = Carbon::create($anio, $mes, 1)->endOfMonth();

        $conciliadas = 0;
        foreach ($liquidaciones as $liq) {
            $liq->refresh();
            if ($liq->estado === 'pagada') continue;

            $bill = \App\Models\RentBill::where('owner_liquidation_id', $liq->id)->first();
            if (!$bill) continue;
            $thirdId = $bill->arrendatario_id;

            $cargosDelInquilino = AccountingEntryLine::where('accounting_entry_lines.third_id', $thirdId)
                ->whereHas('account', fn ($q) => $q->where('codigo', '13050501'))
                ->where('debito', $bill->total_factura)->get();

            $match = null;
            foreach ($cargosDelInquilino as $cargo) {
                $lineaCxp = AccountingEntryLine::where('entry_id', $cargo->entry_id)
                    ->whereHas('account', fn ($q) => $q->where('codigo', '23354001'))->first();
                if (!$lineaCxp) continue;
                if (!preg_match('/(\d{4}-\d{1,2}-\d{1,2})\s*AL\s*(\d{4}-\d{1,2}-\d{1,2})/i', $lineaCxp->descripcion, $m)) continue;
                try {
                    $d1 = Carbon::parse($m[1]);
                    $d2 = Carbon::parse($m[2]);
                } catch (\Throwable $e) {
                    continue;
                }
                if ($d1->gt($finMes) || $d2->lt($inicioMes)) continue;

                foreach ($girosTodos as $giro) {
                    if (abs($giro->debito - $lineaCxp->credito) > 1) continue;
                    if (!preg_match('/(\d{4}-\d{1,2}-\d{1,2})\s*AL\s*(\d{4}-\d{1,2}-\d{1,2})/i', $giro->descripcion, $mg)) continue;
                    try {
                        $gd1 = Carbon::parse($mg[1]);
                        $gd2 = Carbon::parse($mg[2]);
                    } catch (\Throwable $e) {
                        continue;
                    }
                    if ($gd1->eq($d1) && $gd2->eq($d2)) {
                        $entryGiro = AccountingEntry::find($giro->entry_id);
                        $match = ['fecha' => $entryGiro->fecha];
                        break 2;
                    }
                }
            }

            if ($match) {
                $this->line(($dryRun ? '[DRY-RUN] ' : '') . "Marcando pagada {$liq->numero} — {$liq->propietario?->nombre_completo} — fecha giro {$match['fecha']->format('Y-m-d')}");
                if (!$dryRun) {
                    $liq->update([
                        'estado' => 'pagada',
                        'fecha_giro' => $match['fecha'],
                        'forma_giro' => 'transferencia',
                        'referencia_giro' => 'Conciliado desde histórico Siinmob (pre-corte)',
                    ]);
                }
                $conciliadas++;
            }
        }
        $this->info("Giros conciliados: {$conciliadas}");

        return self::SUCCESS;
    }
}

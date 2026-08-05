<?php

namespace App\Services;

use App\Models\AccountingEntry;
use App\Models\DailyReviewCheck;
use App\Models\OwnerLiquidation;
use App\Models\RentBill;
use Carbon\Carbon;

/**
 * Calcula la "planilla" diaria de cartera (inquilinos en mora / propietarios
 * pendientes de girar) y la deja guardada como snapshot por fecha en
 * daily_review_checks, para poder navegar hacia días anteriores y ver
 * exactamente cómo estaba la lista ese día, no un recálculo en vivo.
 */
class SeguimientoDiarioService
{
    public static function calcularInquilinos(): array
    {
        // Roster completo: TODOS los inquilinos con contrato de arriendo
        // activo, no solo los que deben — los que están al día se marcan
        // aparte, sin factura ni mora que mostrar.
        $contratos = \App\Models\RentalContract::where('estado', 'activo')
            ->with('arrendatario')
            ->get()
            ->keyBy('arrendatario_id');

        $bills = RentBill::whereIn('estado', ['pendiente', 'parcial', 'en_mora'])
            ->where('saldo_pendiente', '>', 0)
            ->where('fecha_limite_pago', '<', now())
            ->whereIn('arrendatario_id', $contratos->keys())
            ->with(['arrendatario', 'property'])
            ->orderBy('periodo_inicio')
            ->get();

        $porInquilino = [];

        foreach ($contratos as $arrId => $contrato) {
            $arr = $contrato->arrendatario;
            if (!$arr) continue;

            $totalHist   = RentBill::where('arrendatario_id', $arrId)->whereNotIn('estado', ['anulada'])->count();
            $conMoraHist = RentBill::where('arrendatario_id', $arrId)->whereNotIn('estado', ['anulada'])->where('dias_mora', '>', 0)->count();
            $riesgo      = $totalHist > 0 ? (int) round($conMoraHist / $totalHist * 100) : 0;

            $porInquilino[$arrId] = [
                'id'          => $arrId,
                'documento'   => $arr->numero_documento,
                'nombre'      => $arr->nombre_completo,
                'celular'     => $arr->celular,
                'dia_pago'    => $contrato->dia_pago,
                'riesgo'      => $riesgo,
                'max_dias'    => 0,
                'total_mora'  => 0.0,
                'total_debe'  => 0.0,
                'inmuebles'   => [],
            ];
        }

        foreach ($bills as $bill) {
            $arr = $bill->arrendatario;
            if (!$arr || !isset($porInquilino[$arr->id])) continue;

            $id = $arr->id;
            $porInquilino[$id]['max_dias']   = max($porInquilino[$id]['max_dias'], (int) $bill->dias_mora);
            $porInquilino[$id]['total_mora'] += (float) $bill->mora_acumulada;
            $porInquilino[$id]['total_debe'] += (float) $bill->saldo_pendiente;
            $porInquilino[$id]['inmuebles'][] = [
                'direccion' => $bill->property?->direccion ?? '—',
                'numero'    => $bill->numero,
                'mes'       => Carbon::create($bill->anio, $bill->mes, 1)->translatedFormat('F Y'),
                'dias_mora' => (int) $bill->dias_mora,
                'mora'      => (float) $bill->mora_acumulada,
                'saldo'     => (float) $bill->saldo_pendiente,
            ];
        }

        $filas = array_values($porInquilino);
        usort($filas, fn ($a, $b) => strcasecmp($a['nombre'] ?? '', $b['nombre'] ?? ''));

        return $filas;
    }

    public static function calcularPropietarios(): array
    {
        // Roster completo: TODOS los propietarios con al menos un contrato
        // de administración activo, no solo los que tienen giro pendiente.
        $contratosAdmin = \App\Models\AdministrationContract::where('estado', 'activo')
            ->with('propietario')
            ->get()
            ->unique('propietario_id');

        $liquidaciones = OwnerLiquidation::whereIn('estado', ['pendiente', 'aprobada'])
            ->with(['propietario', 'property'])
            ->orderBy('periodo_inicio')
            ->get();

        $porPropietario = [];

        foreach ($contratosAdmin as $ca) {
            $prop = $ca->propietario;
            if (!$prop) continue;

            $porPropietario[$prop->id] = [
                'id'          => $prop->id,
                'documento'   => $prop->numero_documento,
                'nombre'      => $prop->nombre_completo,
                'celular'     => $prop->celular,
                'dia_giro'    => $ca->dia_giro,
                'max_dias'    => 0,
                'total_girar' => 0.0,
                'inmuebles'   => [],
            ];
        }

        foreach ($liquidaciones as $liq) {
            $prop = $liq->propietario;
            if (!$prop || !isset($porPropietario[$prop->id])) continue;

            $id = $prop->id;

            $diasEspera = max(0, (int) Carbon::parse($liq->periodo_fin)->diffInDays(now(), false));

            $porPropietario[$id]['max_dias']    = max($porPropietario[$id]['max_dias'], $diasEspera);
            $porPropietario[$id]['total_girar'] += (float) $liq->total_giro;
            $porPropietario[$id]['inmuebles'][] = [
                'direccion'   => $liq->property?->direccion ?? '—',
                'numero'      => $liq->numero,
                'periodo'     => Carbon::parse($liq->periodo_inicio)->translatedFormat('F Y'),
                'dias_espera' => $diasEspera,
                'monto'       => (float) $liq->total_giro,
                'estado'      => $liq->estado,
            ];
        }

        $filas = array_values($porPropietario);
        usort($filas, fn ($a, $b) => strcasecmp($a['nombre'] ?? '', $b['nombre'] ?? ''));

        return $filas;
    }

    /**
     * Guarda/actualiza el snapshot del día (solo tiene sentido para HOY —
     * los días pasados ya quedaron congelados). No toca 'revisado' si el
     * registro ya existía, para no perder el check hecho durante el día.
     */
    public static function sincronizar(string $tipo, array $filas, string $fecha): void
    {
        foreach ($filas as $fila) {
            $datos = $fila;
            unset($datos['id']);

            $existente = DailyReviewCheck::where('fecha', $fecha)
                ->where('tipo', $tipo)->where('third_id', $fila['id'])
                ->first();

            if ($existente) {
                $existente->update(['datos' => $datos]);
            } else {
                DailyReviewCheck::create([
                    'fecha' => $fecha, 'tipo' => $tipo, 'third_id' => $fila['id'],
                    'datos' => $datos, 'revisado' => false,
                ]);
            }
        }
    }

    /**
     * Reconstruye la planilla de un día ya pasado a partir del snapshot
     * guardado — no recalcula nada en vivo.
     */
    public static function cargarSnapshot(string $tipo, string $fecha): array
    {
        return DailyReviewCheck::where('fecha', $fecha)->where('tipo', $tipo)
            ->with('revisadoPor')
            ->get()
            ->map(function ($check) {
                return array_merge($check->datos ?? [], [
                    'id'           => $check->third_id,
                    'revisado'     => $check->revisado,
                    'revisado_por' => $check->revisadoPor?->name,
                    'revisado_en'  => $check->revisado_en,
                ]);
            })
            ->sortBy(fn ($f) => mb_strtolower($f['nombre'] ?? ''))
            ->values()
            ->toArray();
    }

    /**
     * Comprobantes de ingreso (CI) o egreso (CE) contabilizados en la fecha
     * dada, con su estado de revisión. A diferencia de inquilinos/propietarios
     * no hace falta "congelar" un snapshot: el comprobante ya es un registro
     * fijo una vez contabilizado, así que se lee en vivo directo del libro.
     */
    public static function cargarComprobantes(string $tipoEntry, string $fecha): array
    {
        $tipoCheck = $tipoEntry === 'CI' ? 'comprobante_ingreso' : 'comprobante_egreso';

        $entries = AccountingEntry::where('tipo', $tipoEntry)
            ->where('estado', 'contabilizado')
            ->whereDate('fecha', $fecha)
            ->with(['third', 'lines.account', 'lines.third'])
            ->orderBy('numero')
            ->get();

        $checks = DailyReviewCheck::where('fecha', $fecha)->where('tipo', $tipoCheck)
            ->with('revisadoPor')
            ->get()
            ->keyBy('entry_id');

        return $entries->map(function (AccountingEntry $entry) use ($checks) {
            $check = $checks->get($entry->id);

            return [
                'id'              => $entry->id,
                'numero'          => $entry->numero,
                'fecha'           => $entry->fecha,
                'descripcion'     => $entry->descripcion,
                'tercero'         => $entry->third?->nombre_completo,
                'total_debitos'   => (float) $entry->total_debitos,
                'total_creditos'  => (float) $entry->total_creditos,
                'lineas'          => $entry->lines->map(fn ($l) => [
                    'cuenta_codigo' => $l->account?->codigo,
                    'cuenta_nombre' => $l->account?->nombre,
                    'tercero'       => $l->third?->nombre_completo,
                    'descripcion'   => $l->descripcion,
                    'debito'        => (float) $l->debito,
                    'credito'       => (float) $l->credito,
                ])->toArray(),
                'revisado'        => $check?->revisado ?? false,
                'revisado_por'    => $check?->revisadoPor?->name,
                'revisado_en'     => $check?->revisado_en,
            ];
        })->values()->toArray();
    }
}

<?php

namespace App\Services;

use App\Models\AccountingEntryLine;
use App\Models\Bank;
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
     * Movimientos REALES de plata (banco/caja) del día — no se basa en el
     * tipo de comprobante contable (CI/CE/CR...) porque eso mezcla cosas que
     * no son caja: p.ej. la factura al inquilino se contabiliza tipo CI el
     * día del período pero ahí no entra ni sale un peso, es causación. Lo
     * que sí es plata real es cualquier línea que toque una cuenta de banco
     * o caja (las mismas configuradas en Bancos): si esa línea debita, entró
     * dinero ese día (pago de inquilino, depósito, otro ingreso); si
     * acredita, salió (giro a propietario, gasto, devolución de depósito).
     */
    public static function cargarMovimientosCaja(string $direccion, string $fecha): array
    {
        $cuentasDisponible = Bank::whereNotNull('accounting_account_id')
            ->pluck('accounting_account_id')->unique()->values();

        if ($cuentasDisponible->isEmpty()) return [];

        $campoMonto = $direccion === 'ingreso' ? 'debito' : 'credito';
        $tipoCheck  = $direccion === 'ingreso' ? 'comprobante_ingreso' : 'comprobante_egreso';

        $lineas = AccountingEntryLine::whereIn('account_id', $cuentasDisponible)
            ->where($campoMonto, '>', 0)
            ->whereHas('entry', fn ($q) => $q->where('estado', 'contabilizado')->whereDate('fecha', $fecha))
            ->with(['entry.lines.account', 'entry.lines.third', 'entry.third', 'account'])
            ->get()
            ->unique('entry_id') // un comprobante puede tocar caja en más de una línea (p.ej. traspaso entre bancos)
            ->values();

        $checks = DailyReviewCheck::where('fecha', $fecha)->where('tipo', $tipoCheck)
            ->with('revisadoPor')
            ->get()
            ->keyBy('entry_id');

        return $lineas->map(function (AccountingEntryLine $linea) use ($checks, $campoMonto, $cuentasDisponible) {
            $entry = $linea->entry;
            $check = $checks->get($entry->id);

            // Monto real movido en caja/banco para este comprobante (suma
            // todas sus líneas de disponible, normalmente es solo una).
            $monto = $entry->lines
                ->whereIn('account_id', $cuentasDisponible)
                ->sum($campoMonto);

            return [
                'id'           => $entry->id,
                'numero'       => $entry->numero,
                'fecha'        => $entry->fecha,
                'descripcion'  => $entry->descripcion,
                'tercero'      => $entry->third?->nombre_completo,
                'cuenta'       => $linea->account?->nombre,
                'monto'        => (float) $monto,
                'lineas'       => $entry->lines->map(fn ($l) => [
                    'cuenta_codigo' => $l->account?->codigo,
                    'cuenta_nombre' => $l->account?->nombre,
                    'tercero'       => $l->third?->nombre_completo,
                    'descripcion'   => $l->descripcion,
                    'debito'        => (float) $l->debito,
                    'credito'       => (float) $l->credito,
                ])->toArray(),
                'revisado'     => $check?->revisado ?? false,
                'revisado_por' => $check?->revisadoPor?->name,
                'revisado_en'  => $check?->revisado_en,
            ];
        })
            ->sortBy('numero')
            ->values()
            ->toArray();
    }
}

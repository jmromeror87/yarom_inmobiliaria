<?php

namespace App\Services;

use App\Models\AccountingAccount;
use App\Models\AccountingEntry;
use App\Models\AccountingPeriod;
use App\Models\Company;
use App\Models\OwnerLiquidation;
use App\Models\RentBill;
use App\Models\RentPayment;
use App\Models\RentalContract;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ContabilidadService
{
    // ────────────────────────────────────────────────────────────────────────
    // HELPERS INTERNOS
    // ────────────────────────────────────────────────────────────────────────

    private static function cuentaId(string $codigo): ?int
    {
        static $cache = [];
        if (!isset($cache[$codigo])) {
            $cache[$codigo] = AccountingAccount::where('codigo', $codigo)
                ->where('acepta_movimiento', true)
                ->value('id');
        }
        return $cache[$codigo];
    }

    private static function periodo(): ?AccountingPeriod
    {
        $p = AccountingPeriod::actual();
        if (!$p) {
            $p = AccountingPeriod::abrirSiNoExiste(now()->year, now()->month);
        }
        return $p;
    }

    private static function yaExiste(string|int $referenciaId, string $referenciaTipo): bool
    {
        return AccountingEntry::where('referencia_id', $referenciaId)
            ->where('referencia_tipo', $referenciaTipo)
            ->whereNotIn('estado', ['anulado'])
            ->exists();
    }

    private static function crearComprobante(array $header, array $lineas): ?AccountingEntry
    {
        $periodo = static::periodo();
        if (!$periodo) {
            Log::warning("ContabilidadService: no hay período contable abierto para " . now()->format('Y-m'));
            return null;
        }

        // Validar que todas las cuentas existen
        foreach ($lineas as $l) {
            if (empty($l['account_id'])) {
                Log::warning("ContabilidadService: cuenta no encontrada en comprobante tipo {$header['tipo']} ref {$header['referencia']}");
                return null;
            }
        }

        // Validar cuadre
        $totalDeb = collect($lineas)->sum('debito');
        $totalCre = collect($lineas)->sum('credito');
        if (abs($totalDeb - $totalCre) > 0.02) {
            Log::warning("ContabilidadService: comprobante no cuadra — D:{$totalDeb} C:{$totalCre} ref:{$header['referencia']}");
            return null;
        }

        $entry = AccountingEntry::create(array_merge($header, [
            'period_id'      => $periodo->id,
            'estado'         => 'contabilizado',   // Auto-contabilizado siempre
            'total_debitos'  => $totalDeb,
            'total_creditos' => $totalCre,
            'contabilizado_por' => Auth::id(),
            'contabilizado_en'  => now(),
        ]));

        foreach ($lineas as $orden => $linea) {
            $entry->lines()->create(array_merge($linea, ['orden' => $orden + 1]));
        }

        // Recalcula totales desde BD después de crear líneas (el saving hook fue eliminado
        // porque corría antes de que existieran líneas y dejaba los totales en 0).
        $entry->recalcularTotales();

        return $entry;
    }

    // ────────────────────────────────────────────────────────────────────────
    // FIX CRÍTICO 1 — FACTURA DE ARRENDAMIENTO
    // Reconoce comisión + IVA + neto propietario al momento de facturar
    // NIIF Pymes Sección 23: ingreso en el momento del servicio
    // ────────────────────────────────────────────────────────────────────────

    public static function generarParaFactura(RentBill $bill): ?AccountingEntry
    {
        $tipo = 'factura_rent_bill';
        if (static::yaExiste($bill->id, $tipo)) return null;

        $canon   = (float) $bill->canon_base;
        $admCob  = (float) $bill->cuota_administracion;
        $total   = $canon + $admCob;
        if ($total <= 0) return null;

        $company      = Company::first();
        $comisionPct  = $bill->rentalContract?->administrationContract?->comision_porcentaje
                     ?? $company?->comision_administracion ?? 10;
        $ivaPct       = (float) ($company?->tarifa_iva ?? 19);
        $retePct      = (float) ($company?->tarifa_retefuente_arrendamiento ?? 3.5);
        $aplicaRete   = $bill->rentalContract?->arrendatario?->tipo_persona === 'juridica';

        $comision     = round($canon * ($comisionPct / 100), 2);
        $iva          = round($comision * ($ivaPct / 100), 2);
        $rete         = $aplicaRete ? round($canon * ($retePct / 100), 2) : 0;
        // netoProp NO descuenta rete: la retención es anticipo de impuesto de la inmobiliaria,
        // no un descuento al propietario. El propietario recibe (total - comision - iva).
        $netoProp     = round($total - $comision - $iva, 2);

        // Autorretención (persona jurídica obligada según Decreto 2418/2013)
        $autoRete     = round($comision * 0.035, 2);  // 3.5% sobre comisión

        $cuentaArrendatarios = static::cuentaId('13050501');
        $cuentaComision      = static::cuentaId('41551001');
        $cuentaIva           = static::cuentaId('24080101');
        $cuentaXPagarProp    = static::cuentaId('23354001');
        // 136515 = Anticipo impuestos (activo deudor) — retención practicada A FAVOR
        // 236515 era incorrecto: es "retenciones por pagar" (pasivo, lo que nosotros practicamos a otros)
        $cuentaRete          = static::cuentaId('136515');
        $cuentaAutoRete      = static::cuentaId('13551502');  // Anticipo autorretención

        if (!$cuentaArrendatarios || !$cuentaComision || !$cuentaIva || !$cuentaXPagarProp) return null;

        $lineas = [
            ['account_id' => $cuentaArrendatarios, 'debito' => $total,    'credito' => 0,         'descripcion' => "Canon factura {$bill->numero}",           'third_id' => $bill->arrendatario_id],
            ['account_id' => $cuentaComision,       'debito' => 0,         'credito' => $comision, 'descripcion' => "Comisión adm. {$comisionPct}%",           'third_id' => null],
            ['account_id' => $cuentaIva,            'debito' => 0,         'credito' => $iva,      'descripcion' => "IVA {$ivaPct}% sobre comisión",           'third_id' => null],
            ['account_id' => $cuentaXPagarProp,     'debito' => 0,         'credito' => $netoProp, 'descripcion' => "Neto a girar propietario {$bill->numero}",'third_id' => $bill->rentalContract?->property?->propietario_id],
        ];

        // Retención practicada POR el arrendatario (persona jurídica) SOBRE la inmobiliaria.
        // El arrendatario paga menos (total - rete) y la diferencia queda como anticipo
        // de impuesto a favor de la inmobiliaria (Dr. 136515, naturaleza débito).
        // Cuadre: Db(total-rete + rete) = Cr(comision + iva + netoProp = total) ✓
        if ($rete > 0 && $cuentaRete) {
            $lineas[0]['debito'] = round($total - $rete, 2);
            $lineas[] = ['account_id' => $cuentaRete, 'debito' => $rete, 'credito' => 0, 'descripcion' => "Anticipo retefuente {$retePct}% s/canon", 'third_id' => $bill->arrendatario_id];
        }

        // Autorretención que practica la inmobiliaria sobre su comisión
        if ($cuentaAutoRete && $autoRete > 0) {
            $lineas[] = ['account_id' => $cuentaAutoRete, 'debito' => $autoRete, 'credito' => 0,         'descripcion' => 'Autorretención renta 3.5% comisión', 'third_id' => null];
            $lineas[] = ['account_id' => $cuentaComision, 'debito' => 0,         'credito' => $autoRete, 'descripcion' => 'Autorretención renta — ajuste ingreso','third_id' => null];
        }

        return static::crearComprobante([
            'tipo'            => 'CI',
            'fecha'           => $bill->periodo_inicio?->toDateString() ?? now()->toDateString(),
            'descripcion'     => "Factura {$bill->numero} — {$bill->arrendatario?->nombre_completo}",
            'third_id'        => $bill->arrendatario_id,
            'referencia'      => $bill->numero,
            'referencia_tipo' => $tipo,
            'referencia_id'   => $bill->id,
        ], $lineas);
    }

    // ────────────────────────────────────────────────────────────────────────
    // FIX CRÍTICO 4 — PAGO DE FACTURA (por RentPayment individual, no por bill)
    // Cada pago genera su propio asiento — soporta pagos parciales
    // ────────────────────────────────────────────────────────────────────────

    public static function generarParaPagoFactura(RentBill $bill, ?RentPayment $payment = null): ?AccountingEntry
    {
        // Referenciar por pago individual para soportar pagos parciales
        $referenciaId   = $payment ? $payment->id : $bill->id;
        $referenciaTipo = $payment ? 'pago_individual' : 'pago_rent_bill';

        if (static::yaExiste($referenciaId, $referenciaTipo)) return null;

        $monto = $payment ? (float) $payment->total_pagado : (float) $bill->total_pagado;
        if ($monto <= 0) return null;

        // La cuenta de destino del dinero depende del banco/caja elegido al registrar
        // el pago — si no se seleccionó ninguno, se usa Bancolombia como respaldo.
        $cuentaBancos        = $payment?->bank?->accounting_account_id ?? static::cuentaId('11100502');
        $cuentaArrendatarios = static::cuentaId('13050501');

        if (!$cuentaBancos || !$cuentaArrendatarios) return null;

        $numero      = $payment?->numero ?? $bill->numero;
        $fechaPago   = $payment?->fecha_pago?->toDateString() ?? $bill->fecha_pago?->toDateString() ?? now()->toDateString();
        $nombreBanco = $payment?->bank?->nombre;

        // Si hay mora, separar el ingreso por mora
        $lineas = [
            ['account_id' => $cuentaBancos,        'debito' => $monto, 'credito' => 0,      'descripcion' => $nombreBanco ? "Pago recibido {$numero} — {$nombreBanco}" : "Pago recibido {$numero}", 'third_id' => $bill->arrendatario_id],
            ['account_id' => $cuentaArrendatarios, 'debito' => 0,      'credito' => $monto, 'descripcion' => "Cancelación cartera {$bill->numero}", 'third_id' => $bill->arrendatario_id],
        ];

        // Si el pago incluye mora, reconocer el ingreso por interés
        if ($payment && $payment->valor_mora > 0) {
            $cuentaMora = static::cuentaId('42100505');
            if ($cuentaMora) {
                $mora = (float) $payment->valor_mora;
                $lineas[0]['debito']  = round($monto, 2);
                $lineas[1]['credito'] = round($monto - $mora, 2);
                $lineas[] = ['account_id' => $cuentaMora, 'debito' => 0, 'credito' => $mora, 'descripcion' => "Intereses de mora {$bill->numero}", 'third_id' => $bill->arrendatario_id];
            }
        }

        return static::crearComprobante([
            'tipo'            => 'CR',
            'fecha'           => $fechaPago,
            'descripcion'     => "Recibo caja {$numero} — {$bill->arrendatario?->nombre_completo}",
            'third_id'        => $bill->arrendatario_id,
            'referencia'      => $numero,
            'referencia_tipo' => $referenciaTipo,
            'referencia_id'   => $referenciaId,
        ], $lineas);
    }

    // ────────────────────────────────────────────────────────────────────────
    // FIX CRÍTICO 2 — MORA ACUMULADA
    // Reconoce intereses de mora cuando el VerificarMoraJob los calcula
    // ────────────────────────────────────────────────────────────────────────

    public static function generarParaMora(RentBill $bill, float $mora): ?AccountingEntry
    {
        if ($mora <= 0) return null;

        $tipo = 'mora_rent_bill_' . now()->format('Ym');
        if (static::yaExiste($bill->id, $tipo)) return null;

        $cuentaArrendatarios = static::cuentaId('13050501');
        $cuentaMora          = static::cuentaId('42100505');

        if (!$cuentaArrendatarios || !$cuentaMora) return null;

        return static::crearComprobante([
            'tipo'            => 'ND',
            'fecha'           => now()->toDateString(),
            'descripcion'     => "Mora {$bill->numero} — {$bill->arrendatario?->nombre_completo} — " . now()->format('M Y'),
            'third_id'        => $bill->arrendatario_id,
            'referencia'      => $bill->numero,
            'referencia_tipo' => $tipo,
            'referencia_id'   => $bill->id,
        ], [
            ['account_id' => $cuentaArrendatarios, 'debito' => $mora, 'credito' => 0,     'descripcion' => "Intereses de mora acumulados", 'third_id' => $bill->arrendatario_id],
            ['account_id' => $cuentaMora,          'debito' => 0,     'credito' => $mora, 'descripcion' => "Ingreso intereses mora {$bill->numero}", 'third_id' => null],
        ]);
    }

    // ────────────────────────────────────────────────────────────────────────
    // FIX CRÍTICO 3 — DEPÓSITO DE ARRENDATARIO
    // ────────────────────────────────────────────────────────────────────────

    public static function generarParaDeposito(RentalContract $contrato, float $monto, string $tipo = 'recibido'): ?AccountingEntry
    {
        if ($monto <= 0) return null;

        $tipoRef = "deposito_{$tipo}_contrato";
        if (static::yaExiste($contrato->id, $tipoRef)) return null;

        $cuentaBancos   = static::cuentaId('11100502');
        $cuentaDeposito = static::cuentaId('28150503');

        if (!$cuentaBancos || !$cuentaDeposito) return null;

        if ($tipo === 'recibido') {
            $lineas = [
                ['account_id' => $cuentaBancos,   'debito' => $monto, 'credito' => 0,      'descripcion' => "Depósito recibido {$contrato->numero_contrato}", 'third_id' => $contrato->arrendatario_id],
                ['account_id' => $cuentaDeposito, 'debito' => 0,      'credito' => $monto, 'descripcion' => "Depósito arrendatario {$contrato->numero_contrato}", 'third_id' => $contrato->arrendatario_id],
            ];
            $desc = "Depósito recibido contrato {$contrato->numero_contrato}";
            $tipoComp = 'CI';
        } else {
            // Devolución del depósito
            $lineas = [
                ['account_id' => $cuentaDeposito, 'debito' => $monto, 'credito' => 0,      'descripcion' => "Devolución depósito {$contrato->numero_contrato}", 'third_id' => $contrato->arrendatario_id],
                ['account_id' => $cuentaBancos,   'debito' => 0,      'credito' => $monto, 'descripcion' => "Giro devolución depósito {$contrato->numero_contrato}", 'third_id' => $contrato->arrendatario_id],
            ];
            $desc = "Devolución depósito contrato {$contrato->numero_contrato}";
            $tipoComp = 'CE';
        }

        return static::crearComprobante([
            'tipo'            => $tipoComp,
            'fecha'           => now()->toDateString(),
            'descripcion'     => $desc,
            'third_id'        => $contrato->arrendatario_id,
            'referencia'      => $contrato->numero_contrato,
            'referencia_tipo' => $tipoRef,
            'referencia_id'   => $contrato->id,
        ], $lineas);
    }

    // ────────────────────────────────────────────────────────────────────────
    // LIQUIDACIÓN DE PROPIETARIO
    // FIX: ya no reconoce comisión aquí — ya se reconoció en la factura
    // Solo registra los descuentos adicionales si los hay (otros_descuentos)
    // ────────────────────────────────────────────────────────────────────────

    public static function generarParaLiquidacion(OwnerLiquidation $liq): ?AccountingEntry
    {
        $tipo = 'liquidacion_owner';
        if (static::yaExiste($liq->id, $tipo)) return null;

        // Si no hay descuentos adicionales, no hay asiento extra que generar
        // La comisión ya fue reconocida en generarParaFactura
        $otrosDesc = (float) $liq->otros_descuentos;
        if ($otrosDesc <= 0) return null;

        $cuentaXPagarProp = static::cuentaId('23354001');
        $cuentaIngresos   = static::cuentaId('42959501'); // Administración de inmuebles

        if (!$cuentaXPagarProp || !$cuentaIngresos) return null;

        return static::crearComprobante([
            'tipo'            => 'CC',
            'fecha'           => now()->toDateString(),
            'descripcion'     => "Otros descuentos liq. {$liq->numero} — {$liq->propietario?->nombre_completo}",
            'third_id'        => $liq->propietario_id,
            'referencia'      => $liq->numero,
            'referencia_tipo' => $tipo,
            'referencia_id'   => $liq->id,
        ], [
            ['account_id' => $cuentaXPagarProp, 'debito' => $otrosDesc, 'credito' => 0,          'descripcion' => "Otros descuentos {$liq->numero}", 'third_id' => $liq->propietario_id],
            ['account_id' => $cuentaIngresos,   'debito' => 0,          'credito' => $otrosDesc,  'descripcion' => $liq->descripcion_descuentos ?? 'Descuentos propietario', 'third_id' => null],
        ]);
    }

    // ────────────────────────────────────────────────────────────────────────
    // GIRO AL PROPIETARIO
    // ────────────────────────────────────────────────────────────────────────

    public static function generarParaGiro(OwnerLiquidation $liq): ?AccountingEntry
    {
        $tipo = 'giro_owner';
        if (static::yaExiste($liq->id, $tipo)) return null;

        $monto = (float) $liq->total_giro;
        if ($monto <= 0) return null;

        $cuentaXPagarProp = static::cuentaId('23354001');
        $cuentaBancos     = static::cuentaId('11100502');

        if (!$cuentaXPagarProp || !$cuentaBancos) return null;

        return static::crearComprobante([
            'tipo'            => 'CE',
            'fecha'           => $liq->fecha_giro?->toDateString() ?? now()->toDateString(),
            'descripcion'     => "Giro propietario {$liq->numero} — {$liq->propietario?->nombre_completo}",
            'third_id'        => $liq->propietario_id,
            'referencia'      => $liq->numero,
            'referencia_tipo' => $tipo,
            'referencia_id'   => $liq->id,
        ], [
            ['account_id' => $cuentaXPagarProp, 'debito' => $monto, 'credito' => 0,      'descripcion' => "Cancelación obligación {$liq->numero}", 'third_id' => $liq->propietario_id],
            ['account_id' => $cuentaBancos,     'debito' => 0,      'credito' => $monto, 'descripcion' => "Giro {$liq->propietario?->nombre_completo}", 'third_id' => $liq->propietario_id],
        ]);
    }

    // ────────────────────────────────────────────────────────────────────────
    // PROVISIÓN DE CARTERA POR ANTIGÜEDAD (NIIF Pymes + norma colombiana)
    // Llamar desde VerificarMoraJob mensualmente
    // ────────────────────────────────────────────────────────────────────────

    public static function generarProvisionCartera(RentBill $bill): ?AccountingEntry
    {
        if (!in_array($bill->estado, ['en_mora', 'vencida'])) return null;

        $diasMora  = (int) $bill->dias_mora;
        $saldo     = (float) $bill->saldo_pendiente;
        if ($saldo <= 0 || $diasMora <= 0) return null;

        // Tabla de provisión colombiana (Circular Externa 029 SFC adaptada para comerciales)
        $pct = match(true) {
            $diasMora <= 30  => 0,
            $diasMora <= 60  => 5,
            $diasMora <= 90  => 10,
            $diasMora <= 180 => 15,
            $diasMora <= 360 => 33,
            default          => 100,
        };

        if ($pct === 0) return null;

        $provision = round($saldo * ($pct / 100), 2);
        $tipo      = 'provision_cartera_' . now()->format('Ym');

        if (static::yaExiste($bill->id, $tipo)) return null;

        $cuentaGastoProv = static::cuentaId('519905'); // Provisión deudores (gasto)
        $cuentaAcumProv  = static::cuentaId('139905'); // Provisión acumulada (activo correctivo)

        if (!$cuentaGastoProv || !$cuentaAcumProv) return null;

        return static::crearComprobante([
            'tipo'            => 'CA',
            'fecha'           => now()->toDateString(),
            'descripcion'     => "Provisión cartera {$bill->numero} — {$diasMora} días mora ({$pct}%)",
            'third_id'        => $bill->arrendatario_id,
            'referencia'      => $bill->numero,
            'referencia_tipo' => $tipo,
            'referencia_id'   => $bill->id,
        ], [
            ['account_id' => $cuentaGastoProv, 'debito' => $provision, 'credito' => 0,          'descripcion' => "Gasto provisión {$pct}% — {$diasMora} días", 'third_id' => $bill->arrendatario_id],
            ['account_id' => $cuentaAcumProv,  'debito' => 0,          'credito' => $provision,  'descripcion' => "Provisión acumulada {$bill->numero}",         'third_id' => $bill->arrendatario_id],
        ]);
    }

    // ────────────────────────────────────────────────────────────────────────
    // CUENTAS DE ORDEN — inmueble recibido en administración
    // Llamar al activar el contrato de administración
    // ────────────────────────────────────────────────────────────────────────

    public static function generarCuentaOrdenInmueble(\App\Models\Property $property, float $valorCatastral, string $operacion = 'entrada'): ?AccountingEntry
    {
        $tipo = "cuenta_orden_inmueble_{$operacion}";
        if (static::yaExiste($property->id, $tipo)) return null;
        if ($valorCatastral <= 0) return null;

        $cuentaOrdenDeb = static::cuentaId('810505'); // Inmuebles recibidos en administración
        $cuentaOrdenCre = static::cuentaId('910505'); // Propietarios responsables

        if (!$cuentaOrdenDeb || !$cuentaOrdenCre) return null;

        if ($operacion === 'entrada') {
            $lineas = [
                ['account_id' => $cuentaOrdenDeb, 'debito' => $valorCatastral, 'credito' => 0,                'descripcion' => "Inmueble {$property->codigo} recibido en administración", 'third_id' => $property->propietario_id],
                ['account_id' => $cuentaOrdenCre, 'debito' => 0,               'credito' => $valorCatastral,  'descripcion' => "Propietario responsable inmueble {$property->codigo}",     'third_id' => $property->propietario_id],
            ];
        } else {
            // Salida: reverso
            $lineas = [
                ['account_id' => $cuentaOrdenCre, 'debito' => $valorCatastral, 'credito' => 0,                'descripcion' => "Retiro inmueble {$property->codigo} de administración",   'third_id' => $property->propietario_id],
                ['account_id' => $cuentaOrdenDeb, 'debito' => 0,               'credito' => $valorCatastral,  'descripcion' => "Reverso cuenta orden inmueble {$property->codigo}",        'third_id' => $property->propietario_id],
            ];
        }

        return static::crearComprobante([
            'tipo'            => 'CC',
            'fecha'           => now()->toDateString(),
            'descripcion'     => "Cuenta de orden — {$operacion} inmueble {$property->codigo}",
            'third_id'        => $property->propietario_id,
            'referencia'      => $property->codigo,
            'referencia_tipo' => $tipo,
            'referencia_id'   => $property->id,
        ], $lineas);
    }
}

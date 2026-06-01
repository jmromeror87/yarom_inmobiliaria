<?php

namespace App\Services;

use App\Models\AccountingAccount;
use App\Models\AccountingEntry;
use App\Models\AccountingPeriod;
use App\Models\OwnerLiquidation;
use App\Models\RentBill;

class ContabilidadService
{
    // ── Helpers ────────────────────────────────────────────────────────

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

    private static function yaExiste(string $referenciaId, string $referenciaTipo): bool
    {
        return AccountingEntry::where('referencia_id', $referenciaId)
            ->where('referencia_tipo', $referenciaTipo)
            ->exists();
    }

    private static function crearComprobante(array $header, array $lineas): ?AccountingEntry
    {
        $periodo = static::periodo();
        if (!$periodo) return null;

        // Validar que todas las cuentas existen
        foreach ($lineas as $l) {
            if (empty($l['account_id'])) return null;
        }

        $totalDeb = collect($lineas)->sum('debito');
        $totalCre = collect($lineas)->sum('credito');

        $entry = AccountingEntry::create(array_merge($header, [
            'period_id'      => $periodo->id,
            'estado'         => 'borrador',
            'total_debitos'  => $totalDeb,
            'total_creditos' => $totalCre,
        ]));

        foreach ($lineas as $orden => $linea) {
            $entry->lines()->create(array_merge($linea, ['orden' => $orden + 1]));
        }

        return $entry;
    }

    // ── Factura de arrendamiento ────────────────────────────────────────

    /**
     * Al crear la factura: DR Arrendatarios / CR Arrendamientos a propietarios
     */
    public static function generarParaFactura(RentBill $bill): ?AccountingEntry
    {
        $tipo = 'factura_rent_bill';
        if (static::yaExiste($bill->id, $tipo)) return null;

        $monto = (float) $bill->total_factura;
        if ($monto <= 0) return null;

        $cuentaArrendatarios = static::cuentaId('130505'); // Arrendatarios
        $cuentaXPagarProp    = static::cuentaId('233510'); // Arrendamientos a propietarios

        if (!$cuentaArrendatarios || !$cuentaXPagarProp) return null;

        return static::crearComprobante([
            'tipo'            => 'CI',
            'fecha'           => $bill->periodo_inicio ?? now()->toDateString(),
            'descripcion'     => "Factura {$bill->numero} — " . ($bill->arrendatario?->nombre_completo ?? ''),
            'third_id'        => $bill->arrendatario_id,
            'referencia'      => $bill->numero,
            'referencia_tipo' => $tipo,
            'referencia_id'   => $bill->id,
        ], [
            ['account_id' => $cuentaArrendatarios, 'debito' => $monto,  'credito' => 0,      'descripcion' => 'Canon a cobrar al arrendatario'],
            ['account_id' => $cuentaXPagarProp,    'debito' => 0,       'credito' => $monto, 'descripcion' => 'Canon pendiente de liquidar a propietario'],
        ]);
    }

    /**
     * Al pagar la factura: DR Bancos / CR Arrendatarios
     */
    public static function generarParaPagoFactura(RentBill $bill): ?AccountingEntry
    {
        $tipo = 'pago_rent_bill';
        if (static::yaExiste($bill->id, $tipo)) return null;

        $monto = (float) $bill->total_pagado;
        if ($monto <= 0) return null;

        $cuentaBancos       = static::cuentaId('111005'); // Bancolombia
        $cuentaArrendatarios = static::cuentaId('130505'); // Arrendatarios

        if (!$cuentaBancos || !$cuentaArrendatarios) return null;

        return static::crearComprobante([
            'tipo'            => 'CI',
            'fecha'           => $bill->fecha_pago?->toDateString() ?? now()->toDateString(),
            'descripcion'     => "Pago factura {$bill->numero} — " . ($bill->arrendatario?->nombre_completo ?? ''),
            'third_id'        => $bill->arrendatario_id,
            'referencia'      => $bill->numero,
            'referencia_tipo' => $tipo,
            'referencia_id'   => $bill->id,
        ], [
            ['account_id' => $cuentaBancos,        'debito' => $monto, 'credito' => 0,      'descripcion' => 'Pago recibido del arrendatario'],
            ['account_id' => $cuentaArrendatarios, 'debito' => 0,      'credito' => $monto, 'descripcion' => "Cancelación cartera {$bill->numero}"],
        ]);
    }

    // ── Liquidación de propietario ──────────────────────────────────────

    /**
     * Al crear la liquidación: reconocimiento de comisión e IVA
     * DR Arrendamientos a propietarios / CR Comisión + IVA + Retefuente
     */
    public static function generarParaLiquidacion(OwnerLiquidation $liq): ?AccountingEntry
    {
        $tipo = 'liquidacion_owner';
        if (static::yaExiste($liq->id, $tipo)) return null;

        $comision  = (float) $liq->comision_valor;
        $iva       = (float) $liq->iva_comision;
        $rete      = $liq->aplica_retefuente ? (float) $liq->retefuente_valor : 0;
        $totalDeb  = $comision + $iva + $rete;

        if ($totalDeb <= 0) return null;

        $cuentaXPagarProp = static::cuentaId('233510'); // Arrendamientos a propietarios
        $cuentaComision   = static::cuentaId('413505'); // Comisión de arrendamiento
        $cuentaIva        = static::cuentaId('240805'); // IVA generado 19%
        $cuentaRete       = static::cuentaId('236515'); // Retefuente arrendamientos 3.5%

        if (!$cuentaXPagarProp || !$cuentaComision || !$cuentaIva) return null;

        $lineas = [
            ['account_id' => $cuentaXPagarProp, 'debito' => $totalDeb, 'credito' => 0,       'descripcion' => "Deducción liquidación {$liq->numero}"],
            ['account_id' => $cuentaComision,   'debito' => 0,         'credito' => $comision,'descripcion' => "Comisión {$liq->comision_porcentaje}% administración"],
            ['account_id' => $cuentaIva,        'debito' => 0,         'credito' => $iva,     'descripcion' => 'IVA 19% sobre comisión'],
        ];

        if ($rete > 0 && $cuentaRete) {
            $lineas[] = ['account_id' => $cuentaRete, 'debito' => 0, 'credito' => $rete, 'descripcion' => 'Retefuente 3.5% arrendamiento'];
        }

        return static::crearComprobante([
            'tipo'            => 'CC',
            'fecha'           => now()->toDateString(),
            'descripcion'     => "Liquidación {$liq->numero} — " . ($liq->propietario?->nombre_completo ?? ''),
            'third_id'        => $liq->propietario_id,
            'referencia'      => $liq->numero,
            'referencia_tipo' => $tipo,
            'referencia_id'   => $liq->id,
        ], $lineas);
    }

    /**
     * Al girar al propietario: DR Arrendamientos a propietarios / CR Bancos
     */
    public static function generarParaGiro(OwnerLiquidation $liq): ?AccountingEntry
    {
        $tipo = 'giro_owner';
        if (static::yaExiste($liq->id, $tipo)) return null;

        $monto = (float) $liq->total_giro;
        if ($monto <= 0) return null;

        $cuentaXPagarProp = static::cuentaId('233510'); // Arrendamientos a propietarios
        $cuentaBancos     = static::cuentaId('111005'); // Bancolombia

        if (!$cuentaXPagarProp || !$cuentaBancos) return null;

        return static::crearComprobante([
            'tipo'            => 'CE',
            'fecha'           => $liq->fecha_giro?->toDateString() ?? now()->toDateString(),
            'descripcion'     => "Giro propietario {$liq->numero} — " . ($liq->propietario?->nombre_completo ?? ''),
            'third_id'        => $liq->propietario_id,
            'referencia'      => $liq->numero,
            'referencia_tipo' => $tipo,
            'referencia_id'   => $liq->id,
        ], [
            ['account_id' => $cuentaXPagarProp, 'debito' => $monto, 'credito' => 0,      'descripcion' => "Cancelación obligación {$liq->numero}"],
            ['account_id' => $cuentaBancos,     'debito' => 0,       'credito' => $monto, 'descripcion' => "Pago al propietario {$liq->propietario?->nombre_completo}"],
        ]);
    }
}

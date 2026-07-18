<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CuentaPorPagarPropietario extends Model
{
    protected $table = 'cuentas_por_pagar_propietarios';

    protected $fillable = [
        'numero', 'tipo', 'concepto', 'third_id', 'property_id',
        'valor_original', 'valor_pagado', 'saldo',
        'estado', 'fecha_origen', 'fecha_pago_total', 'notas',
    ];

    protected $casts = [
        'valor_original' => 'decimal:2',
        'valor_pagado' => 'decimal:2',
        'saldo' => 'decimal:2',
        'fecha_origen' => 'date',
        'fecha_pago_total' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $c) {
            if (empty($c->numero)) {
                $ultimo = static::max('id') ?? 0;
                $c->numero = 'CPP-' . str_pad($ultimo + 1, 6, '0', STR_PAD_LEFT);
            }
            if (!isset($c->saldo)) {
                $c->saldo = $c->valor_original - $c->valor_pagado;
            }
        });
    }

    public function third(): BelongsTo { return $this->belongsTo(Third::class); }
    public function property(): BelongsTo { return $this->belongsTo(Property::class); }

    public function registrarPago(float $valor, string $referencia = '', string $notas = ''): void
    {
        $nuevoPagado = $this->valor_pagado + $valor;
        $nuevoSaldo = max(0, $this->valor_original - $nuevoPagado);

        $this->update([
            'valor_pagado' => $nuevoPagado,
            'saldo' => $nuevoSaldo,
            'estado' => $nuevoSaldo <= 0 ? 'pagado' : 'parcial',
            'fecha_pago_total' => $nuevoSaldo <= 0 ? today() : null,
            'notas' => trim(($this->notas ?? '') . "\n" . now()->format('Y-m-d') . " - Pago \${$valor} {$referencia} {$notas}"),
        ]);
    }
}

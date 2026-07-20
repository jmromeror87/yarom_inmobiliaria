<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CuentaPorCobrar extends Model
{
    protected $table = 'cuentas_por_cobrar';

    protected $fillable = [
        'numero','tipo','concepto',
        'rental_contract_id','third_id','property_id',
        'valor_original','valor_pagado','saldo',
        'estado','fecha_origen','fecha_vencimiento','fecha_pago_total','notas',
    ];

    protected $casts = [
        'valor_original'    => 'decimal:2',
        'valor_pagado'      => 'decimal:2',
        'saldo'             => 'decimal:2',
        'fecha_origen'      => 'date',
        'fecha_vencimiento' => 'date',
        'fecha_pago_total'  => 'date',
    ];

    public function rentalContract(): BelongsTo { return $this->belongsTo(RentalContract::class); }
    public function third(): BelongsTo          { return $this->belongsTo(Third::class); }
    public function property(): BelongsTo       { return $this->belongsTo(Property::class); }
    public function abonos(): HasMany           { return $this->hasMany(AbonoCartera::class); }

    protected static function booted(): void
    {
        static::creating(function (self $cpc) {
            if (empty($cpc->numero)) {
                $ultimo = static::max('id') ?? 0;
                $cpc->numero = 'CPC-' . str_pad($ultimo + 1, 6, '0', STR_PAD_LEFT);
            }
            $cpc->saldo = $cpc->valor_original - $cpc->valor_pagado;
        });
    }

    public function registrarAbono(float $valor, string $formaPago, string $referencia = '', ?int $userId = null, string $notas = ''): AbonoCartera
    {
        $abono = $this->abonos()->create([
            'valor'          => $valor,
            'fecha_abono'    => today(),
            'forma_pago'     => $formaPago,
            'referencia'     => $referencia,
            'registrado_por' => $userId,
            'notas'          => $notas,
        ]);

        $nuevoPagado = $this->valor_pagado + $valor;
        $nuevoSaldo  = $this->valor_original - $nuevoPagado;

        $this->update([
            'valor_pagado'   => $nuevoPagado,
            'saldo'          => max(0, $nuevoSaldo),
            'estado'         => $nuevoSaldo <= 0 ? 'pagado' : 'parcial',
            'fecha_pago_total' => $nuevoSaldo <= 0 ? today() : null,
        ]);

        // Si es un depósito, actualizar el contrato
        if ($this->tipo === 'deposito_arriendo' && $this->rentalContract) {
            $this->rentalContract->update([
                'deposito_pagado'     => $nuevoPagado,
                'estado_deposito'     => $nuevoSaldo <= 0 ? 'pagado' : 'en_cartera',
                'fecha_pago_deposito' => $nuevoSaldo <= 0 ? today() : null,
            ]);
        }

        return $abono;
    }

    public function porcentajePagado(): float
    {
        if ($this->valor_original <= 0) return 0;
        return round(($this->valor_pagado / $this->valor_original) * 100, 1);
    }

    public static function crearDepositoContrato(RentalContract $contrato): self
    {
        return static::create([
            'tipo'                => 'deposito_arriendo',
            'concepto'            => "Depósito en garantía - Contrato {$contrato->numero_contrato}",
            'rental_contract_id'  => $contrato->id,
            'third_id'            => $contrato->arrendatario_id,
            'property_id'         => $contrato->property_id,
            'valor_original'      => $contrato->deposito,
            'valor_pagado'        => 0,
            'saldo'               => $contrato->deposito,
            'estado'              => 'pendiente',
            'fecha_origen'        => $contrato->fecha_inicio ?? today(),
            'fecha_vencimiento'   => $contrato->fecha_inicio?->addDays(30),
        ]);
    }
}

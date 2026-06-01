<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleContract extends Model
{
    use SoftDeletes;

    protected $table = 'sale_contracts';

    const ESTADOS = [
        'promesa'       => 'Promesa de compraventa',
        'escrituracion' => 'En escrituración',
        'registrado'    => 'Registrado en notaría',
        'entregado'     => 'Entregado al comprador',
        'cancelado'     => 'Cancelado',
    ];

    const FORMAS_PAGO = [
        'contado'            => 'Contado',
        'credito_hipotecario' => 'Crédito hipotecario',
        'leasing'            => 'Leasing habitacional',
        'mixto'              => 'Mixto (cuota inicial + crédito)',
    ];

    protected $fillable = [
        'numero_contrato','property_id','vendedor_id','comprador_id',
        'asesor_id','request_id',
        'precio_venta','precio_avaluo',
        'forma_pago','entidad_financiera','valor_credito','valor_cuota_inicial',
        'quien_paga_comision','porcentaje_comision','valor_comision',
        'comision_pagada','estado_comision',
        'estado','fecha_promesa','fecha_escritura','fecha_registro','fecha_entrega',
        'notaria','notaria_ciudad','numero_escritura','fecha_escrituracion',
        'path_promesa','path_escritura','notas',
    ];

    protected $casts = [
        'precio_venta'       => 'decimal:2',
        'precio_avaluo'      => 'decimal:2',
        'valor_credito'      => 'decimal:2',
        'valor_cuota_inicial' => 'decimal:2',
        'porcentaje_comision' => 'decimal:2',
        'valor_comision'     => 'decimal:2',
        'comision_pagada'    => 'decimal:2',
        'fecha_promesa'      => 'date',
        'fecha_escritura'    => 'date',
        'fecha_registro'     => 'date',
        'fecha_entrega'      => 'date',
        'fecha_escrituracion' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $c) {
            if (empty($c->numero_contrato)) {
                $year  = now()->year;
                $count = static::whereYear('created_at', $year)->count() + 1;
                $c->numero_contrato = 'COR-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
            }
            // Calcular comisión automáticamente si no se llenó
            if (empty($c->valor_comision) && $c->precio_venta && $c->porcentaje_comision) {
                $c->valor_comision = round($c->precio_venta * ($c->porcentaje_comision / 100), 2);
            }
        });

        static::updating(function (self $c) {
            if ($c->isDirty('precio_venta') || $c->isDirty('porcentaje_comision')) {
                $c->valor_comision = round($c->precio_venta * ($c->porcentaje_comision / 100), 2);
            }
        });
    }

    public function property(): BelongsTo  { return $this->belongsTo(Property::class); }
    public function vendedor(): BelongsTo  { return $this->belongsTo(Third::class, 'vendedor_id'); }
    public function comprador(): BelongsTo { return $this->belongsTo(Third::class, 'comprador_id'); }
    public function asesor(): BelongsTo    { return $this->belongsTo(User::class, 'asesor_id'); }
    public function request(): BelongsTo   { return $this->belongsTo(Request::class); }

    public function getSaldoComisionAttribute(): float
    {
        return max(0, ($this->valor_comision ?? 0) - $this->comision_pagada);
    }

    public function getPorcentajeCobradoAttribute(): float
    {
        if (! $this->valor_comision) return 0;
        return round(($this->comision_pagada / $this->valor_comision) * 100, 1);
    }
}

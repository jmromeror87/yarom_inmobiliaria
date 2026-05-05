<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RentBill extends Model
{
    use SoftDeletes;

    protected $table = 'rent_bills';

    protected $fillable = [
        'numero','rental_contract_id','property_id','arrendatario_id',
        'periodo_inicio','periodo_fin','mes','anio',
        'canon_base','cuota_administracion','descuentos','otros_cobros',
        'descripcion_otros_cobros','total_factura',
        'fecha_limite_pago','dias_gracia','tasa_mora_diaria',
        'mora_acumulada','fecha_inicio_mora','dias_mora',
        'estado','total_pagado','saldo_pendiente','fecha_pago',
        'tipo_documento','cufe','numero_dian',
        'wap_enviado','wap_enviado_at','wap_mora_enviado','wap_mora_enviado_at',
        'owner_liquidation_id','notas',
    ];

    protected $casts = [
        'periodo_inicio'     => 'date',
        'periodo_fin'        => 'date',
        'fecha_limite_pago'  => 'date',
        'fecha_inicio_mora'  => 'date',
        'fecha_pago'         => 'date',
        'wap_enviado'        => 'boolean',
        'wap_enviado_at'     => 'datetime',
        'wap_mora_enviado'   => 'boolean',
        'wap_mora_enviado_at'=> 'datetime',
        'canon_base'         => 'decimal:2',
        'total_factura'      => 'decimal:2',
        'mora_acumulada'     => 'decimal:2',
        'total_pagado'       => 'decimal:2',
        'saldo_pendiente'    => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function ($b) {
            if (empty($b->numero)) {
                $year   = now()->year;
                $ultimo = static::whereYear('created_at', $year)->max('numero');
                $count  = $ultimo ? ((int)substr($ultimo, -4)) + 1 : 1;
                $b->numero = 'FAC-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    // ── Helpers ──────────────────────────────────────────
    public function calcularMora(): float
    {
        if (!$this->fecha_inicio_mora) return 0;
        $dias = now()->diffInDays($this->fecha_inicio_mora);
        return round($this->saldo_pendiente * ($this->tasa_mora_diaria / 100) * $dias, 2);
    }

    public function estaEnMora(): bool
    {
        return $this->estado === 'en_mora' ||
               ($this->estado !== 'pagada' && now()->gt($this->fecha_limite_pago->addDays($this->dias_gracia)));
    }

    // ── Relaciones ───────────────────────────────────────
    public function rentalContract(): BelongsTo  { return $this->belongsTo(RentalContract::class); }
    public function property(): BelongsTo        { return $this->belongsTo(Property::class); }
    public function arrendatario(): BelongsTo    { return $this->belongsTo(Third::class, 'arrendatario_id'); }
    public function payments(): HasMany          { return $this->hasMany(RentPayment::class); }
    public function liquidation(): BelongsTo     { return $this->belongsTo(OwnerLiquidation::class, 'owner_liquidation_id'); }
}

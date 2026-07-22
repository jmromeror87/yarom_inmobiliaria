<?php
namespace App\Models;

use App\Services\ContabilidadService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Log;

class RentBill extends Model
{
    use SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['numero', 'estado', 'total_factura', 'total_pagado', 'saldo_pendiente', 'mora_acumulada'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->setDescriptionForEvent(fn (string $e) => match($e) {
                'created' => 'Factura generada',
                'updated' => 'Factura actualizada',
                'deleted' => 'Factura eliminada',
                default   => $e,
            });
    }

    protected $table = 'rent_bills';

    protected $fillable = [
        'numero','rental_contract_id','property_id','arrendatario_id',
        'periodo_inicio','periodo_fin','mes','anio',
        'canon_base','cuota_administracion','descuentos','otros_cobros',
        'descripcion_otros_cobros',
        'saldo_anterior_arrastrado','nota_saldo_arrastrado',
        'valor_seguro_sura','iva_seguro_sura','redondeo_seguro',
        'total_factura',
        'fecha_limite_pago','dias_gracia','tasa_mora_diaria','aplicar_mora',
        'mora_acumulada','fecha_inicio_mora','dias_mora',
        'estado','total_pagado','saldo_pendiente','fecha_pago',
        'tipo_documento','cufe','numero_dian',
        'wap_enviado','wap_enviado_at','wap_mora_enviado','wap_mora_enviado_at',
        'owner_liquidation_id','notas',
        'payment_token','payment_token_expires_at','wompi_transaction_id','wompi_reference',
    ];

    protected $casts = [
        'periodo_inicio'     => 'date',
        'periodo_fin'        => 'date',
        'fecha_limite_pago'  => 'date',
        'fecha_inicio_mora'  => 'date',
        'fecha_pago'         => 'date',
        'wap_enviado'        => 'boolean',
        'wap_enviado_at'     => 'datetime',
        'wap_mora_enviado'         => 'boolean',
        'wap_mora_enviado_at'      => 'datetime',
        'payment_token_expires_at' => 'datetime',
        'aplicar_mora'       => 'boolean',
        'saldo_anterior_arrastrado' => 'decimal:2',
        'canon_base'         => 'decimal:2',
        'valor_seguro_sura'  => 'decimal:2',
        'iva_seguro_sura'    => 'decimal:2',
        'redondeo_seguro'    => 'decimal:2',
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

        // Contabilización manejada exclusivamente por RentBillObserver — no duplicar aquí

        // Blindaje: generar la liquidación al propietario en CUALQUIER
        // punto donde la factura pase a estado "pagada" — no solo cuando
        // se registra un pago (ver RentPayment::created). Comandos de
        // corrección/reversión de mora u otros procesos que actualizan
        // el estado directamente también deben disparar la liquidación.
        static::updated(function (RentBill $b) {
            if ($b->wasChanged('estado') && $b->estado === 'pagada') {
                OwnerLiquidation::generarDesdeFact($b);
            }
        });
    }

    // ── Payment token ────────────────────────────────────
    public function generatePaymentToken(): string
    {
        $token = bin2hex(random_bytes(32));
        $this->update([
            'payment_token'            => $token,
            'payment_token_expires_at' => $this->fecha_limite_pago->endOfDay(),
        ]);
        return $token;
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
        if (!$this->aplicar_mora) return false;

        return $this->estado === 'en_mora' ||
               ($this->estado !== 'pagada' && now()->gt($this->fecha_limite_pago->addDays($this->dias_gracia)));
    }

    // ── Relaciones ───────────────────────────────────────
    public function rentalContract(): BelongsTo  { return $this->belongsTo(RentalContract::class); }
    public function property(): BelongsTo        { return $this->belongsTo(Property::class); }
    public function arrendatario(): BelongsTo    { return $this->belongsTo(Third::class, 'arrendatario_id'); }
    public function payments(): HasMany          { return $this->hasMany(RentPayment::class); }
    public function liquidation(): BelongsTo     { return $this->belongsTo(OwnerLiquidation::class, 'owner_liquidation_id'); }
    public function electronicInvoices(): HasMany { return $this->hasMany(ElectronicInvoice::class, 'rent_bill_id'); }
    public function electronicInvoice(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ElectronicInvoice::class, 'rent_bill_id')
            ->latestOfMany();
    }
}

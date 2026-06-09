<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ElectronicInvoice extends Model
{
    protected $table = 'electronic_invoices';

    protected $fillable = [
        'rent_bill_id','cufe','numero_factura_dian','consecutivo','prefijo','qr_data',
        'operador','ambiente','estado',
        'respuesta_operador','mensaje_dian','codigo_dian',
        'xml_url','pdf_url','attached_document_url',
        'cufe_nota_credito','razon_anulacion','anulado_por','anulado_en',
        'intentos','proximo_reintento','ultimo_error',
        'emitido_por','emitido_en','aceptada_en',
    ];

    protected $casts = [
        'respuesta_operador' => 'array',
        'anulado_en'         => 'datetime',
        'emitido_en'         => 'datetime',
        'aceptada_en'        => 'datetime',
        'proximo_reintento'  => 'datetime',
        'intentos'           => 'integer',
        'consecutivo'        => 'integer',
    ];

    public function rentBill(): BelongsTo   { return $this->belongsTo(RentBill::class, 'rent_bill_id'); }
    public function anuladoPor(): BelongsTo { return $this->belongsTo(User::class, 'anulado_por'); }
    public function emitidoPor(): BelongsTo { return $this->belongsTo(User::class, 'emitido_por'); }

    // ── Helpers de estado ────────────────────────────────────────────────────

    public function getEsAceptadaAttribute(): bool
    {
        return in_array($this->estado, ['aceptada', 'aceptada_con_notificacion']);
    }

    public function getEsAnuladaAttribute(): bool
    {
        return $this->estado === 'anulada';
    }

    public function getPuedeReintentarAttribute(): bool
    {
        return in_array($this->estado, ['error', 'rechazada', 'pendiente'])
            && $this->intentos < config('fe.reintentos.max', 3);
    }

    public function getPuedeAnularAttribute(): bool
    {
        return $this->es_aceptada && !empty($this->cufe);
    }

    public function getEstadoLabelAttribute(): string
    {
        return match($this->estado) {
            'pendiente'                 => 'Pendiente',
            'enviada'                   => 'Enviada',
            'aceptada'                  => 'Aceptada DIAN',
            'aceptada_con_notificacion' => 'Aceptada c/nota',
            'rechazada'                 => 'Rechazada DIAN',
            'anulada'                   => 'Anulada',
            'error'                     => 'Error técnico',
            default                     => $this->estado,
        };
    }

    public function getOperadorLabelAttribute(): string
    {
        return match($this->operador) {
            'factus'       => 'Factus',
            'dataico'      => 'Dataico',
            'facturatech'  => 'Facturatech',
            default        => ucfirst($this->operador),
        };
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopePendientesReintento($query)
    {
        return $query->whereIn('estado', ['error', 'rechazada', 'pendiente'])
            ->where('intentos', '<', config('fe.reintentos.max', 3))
            ->where(fn($q) => $q->whereNull('proximo_reintento')
                ->orWhere('proximo_reintento', '<=', now())
            );
    }
}

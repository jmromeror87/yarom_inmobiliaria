<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DianDeclaration extends Model
{
    protected $table = 'dian_declarations';

    protected $fillable = [
        'obligation_type_id','anio','periodo','periodo_label',
        'fecha_inicio_periodo','fecha_fin_periodo','fecha_vencimiento',
        'estado','calculo','valor_a_pagar','sanciones','intereses','total_declarado',
        'numero_formulario','fecha_presentacion','valor_pagado','fecha_pago',
        'banco_pago','referencia_pago','notas','adjunto_path',
        'calculado_por','calculado_en','presentado_por','pagado_por',
    ];

    protected $casts = [
        'calculo'              => 'array',
        'fecha_inicio_periodo' => 'date',
        'fecha_fin_periodo'    => 'date',
        'fecha_vencimiento'    => 'date',
        'fecha_presentacion'   => 'date',
        'fecha_pago'           => 'date',
        'calculado_en'         => 'datetime',
        'valor_a_pagar'        => 'decimal:2',
        'sanciones'            => 'decimal:2',
        'intereses'            => 'decimal:2',
        'total_declarado'      => 'decimal:2',
        'valor_pagado'         => 'decimal:2',
        'anio'                 => 'integer',
        'periodo'              => 'integer',
    ];

    public function obligationType(): BelongsTo  { return $this->belongsTo(DianObligationType::class, 'obligation_type_id'); }
    public function calculadoPor(): BelongsTo     { return $this->belongsTo(User::class, 'calculado_por'); }
    public function presentadoPor(): BelongsTo    { return $this->belongsTo(User::class, 'presentado_por'); }
    public function pagadoPor(): BelongsTo        { return $this->belongsTo(User::class, 'pagado_por'); }

    // ── Helpers ──────────────────────────────────────────────────────────────

    public function getEstadoLabelAttribute(): string
    {
        return match($this->estado) {
            'pendiente'   => 'Pendiente',
            'en_proceso'  => 'En proceso',
            'presentada'  => 'Presentada',
            'pagada'      => 'Pagada',
            'no_aplica'   => 'No aplica',
            default       => $this->estado,
        };
    }

    public function getEstaVencidaAttribute(): bool
    {
        return $this->fecha_vencimiento < now()->toDateString()
            && !in_array($this->estado, ['presentada', 'pagada', 'no_aplica']);
    }

    public function getDiasParaVencerAttribute(): int
    {
        return (int) now()->diffInDays($this->fecha_vencimiento, false);
    }

    public function getEsUrgentaAttribute(): bool
    {
        return $this->dias_para_vencer <= 5 && $this->dias_para_vencer >= 0
            && !in_array($this->estado, ['presentada', 'pagada', 'no_aplica']);
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopePendientes($q)
    {
        return $q->whereIn('estado', ['pendiente', 'en_proceso']);
    }

    public function scopeVencidas($q)
    {
        return $q->pendientes()->where('fecha_vencimiento', '<', now()->toDateString());
    }

    public function scopeProximas($q, int $dias = 30)
    {
        return $q->pendientes()
            ->whereBetween('fecha_vencimiento', [now()->toDateString(), now()->addDays($dias)->toDateString()]);
    }

    public function scopeDelAnio($q, int $anio)
    {
        return $q->where('anio', $anio);
    }
}

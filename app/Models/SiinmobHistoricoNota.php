<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SiinmobHistoricoNota extends Model
{
    protected $table = 'siinmob_historico_notas';

    protected $fillable = [
        'ver_ref', 'fecha', 'tipo', 'nota_numero', 'transaccion',
        'detalle', 'creada_por', 'concepto', 'total_debito', 'total_credito',
    ];

    protected $casts = [
        'fecha' => 'date',
        'total_debito' => 'decimal:2',
        'total_credito' => 'decimal:2',
    ];

    public function lineas(): HasMany
    {
        return $this->hasMany(SiinmobHistoricoLinea::class, 'nota_id');
    }

    public function getCuadraAttribute(): bool
    {
        return round((float) $this->total_debito - (float) $this->total_credito, 2) === 0.0;
    }
}

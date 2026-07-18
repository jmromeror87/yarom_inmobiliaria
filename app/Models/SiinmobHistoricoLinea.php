<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiinmobHistoricoLinea extends Model
{
    protected $table = 'siinmob_historico_lineas';

    protected $fillable = [
        'nota_id', 'cuenta_codigo', 'cuenta_nombre', 'descripcion_linea', 'debito', 'credito',
    ];

    protected $casts = [
        'debito' => 'decimal:2',
        'credito' => 'decimal:2',
    ];

    public function nota(): BelongsTo
    {
        return $this->belongsTo(SiinmobHistoricoNota::class, 'nota_id');
    }
}

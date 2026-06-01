<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AbonoCartera extends Model
{
    protected $table = 'abonos_cartera';

    protected $fillable = [
        'cuenta_por_cobrar_id','valor','fecha_abono',
        'forma_pago','referencia','registrado_por','notas',
    ];

    protected $casts = [
        'valor'       => 'decimal:2',
        'fecha_abono' => 'date',
    ];

    public function cuentaPorCobrar(): BelongsTo { return $this->belongsTo(CuentaPorCobrar::class); }
    public function registradoPor(): BelongsTo   { return $this->belongsTo(User::class, 'registrado_por'); }
}

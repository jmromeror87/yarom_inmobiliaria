<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiinmobCarteraMovimiento extends Model
{
    protected $table = 'siinmob_cartera_movimientos';

    protected $fillable = [
        'fecha', 'tipo_cartera', 'comprobante', 'tercero',
        'descripcion', 'referencia', 'debito', 'credito',
    ];

    protected $casts = [
        'fecha' => 'date',
        'debito' => 'decimal:2',
        'credito' => 'decimal:2',
    ];
}

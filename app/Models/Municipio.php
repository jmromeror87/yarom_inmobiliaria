<?php
/*
|--------------------------------------------------------------------------
| YarOM ERP - Soluciones de Gestión
|--------------------------------------------------------------------------
| Proyecto privado desarrollado por:
| Ingeniero Jhoan Romero Rivera
| LinkedIn: https://linkedin.com/in/jmromeror87
|
| Módulo: \1
| Archivo: Municipio.php
| Fecha: CURRENT_DAY/05/2026
| Versión: v1.0
|--------------------------------------------------------------------------
*/
        

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Municipio extends Model
{
    protected $table = 'municipios';

    protected $fillable = [
        'departamento_id', 'codigo_dane', 'nombre',
        'categoria', 'tarifa_ica', 'is_active'
    ];

    protected $casts = ['is_active' => 'boolean', 'tarifa_ica' => 'decimal:2'];

    public function departamento(): BelongsTo
    {
        return $this->belongsTo(Departamento::class);
    }
}

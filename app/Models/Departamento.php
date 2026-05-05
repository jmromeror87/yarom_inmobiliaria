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
| Archivo: Departamento.php
| Fecha: CURRENT_DAY/05/2026
| Versión: v1.0
|--------------------------------------------------------------------------
*/
        
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Departamento extends Model
{
    protected $table = 'departamentos';

    protected $fillable = ['pais_id', 'codigo_dane', 'nombre', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function pais(): BelongsTo
    {
        return $this->belongsTo(Pais::class);
    }

    public function municipios()
    {
        return $this->hasMany(Municipio::class);
    }
}

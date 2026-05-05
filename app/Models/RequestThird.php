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
| Archivo: RequestThird.php
| Fecha: CURRENT_DAY/05/2026
| Versión: v1.0
|--------------------------------------------------------------------------
*/

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RequestThird extends Model
{
    protected $table = 'request_thirds';
    protected $fillable = [
        'request_id','third_id','rol',
        'ingresos_declarados','ingresos_verificados',
        'score_datacredito','reporte_negativo',
        'resultado_individual','notas_evaluacion','relacion_ingreso_canon',
    ];
    protected $casts = [
        'reporte_negativo'       => 'boolean',
        'ingresos_declarados'    => 'decimal:2',
        'ingresos_verificados'   => 'decimal:2',
        'relacion_ingreso_canon' => 'decimal:2',
    ];

    public function request(): BelongsTo  { return $this->belongsTo(Request::class); }
    public function third(): BelongsTo    { return $this->belongsTo(Third::class); }
    public function documents(): HasMany  { return $this->hasMany(RequestDocument::class, 'request_third_id'); }
}

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
| Archivo: ContractClause.php
| Fecha: CURRENT_DAY/05/2026
| Versión: v1.0
|--------------------------------------------------------------------------
*/

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContractClause extends Model
{
    protected $table = 'contract_clauses';
    protected $fillable = ['contract_template_id','numero','titulo','tipo','contenido','es_editable','es_obligatoria','orden','is_active'];
    protected $casts = ['es_editable' => 'boolean', 'es_obligatoria' => 'boolean', 'is_active' => 'boolean'];

    public function template() { return $this->belongsTo(ContractTemplate::class, 'contract_template_id'); }
}

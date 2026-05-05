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
| Archivo: ContractStatusHistory.php
| Fecha: CURRENT_DAY/05/2026
| Versión: v1.0
|--------------------------------------------------------------------------
*/
        
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractStatusHistory extends Model
{
    protected $table = 'contract_status_history';
    protected $fillable = [
        'administration_contract_id','changed_by','estado_anterior',
        'estado_nuevo','canal','razon_cambio','ip_address','cambiado_en',
    ];
    protected $casts = ['cambiado_en' => 'datetime'];

    public function contract(): BelongsTo { return $this->belongsTo(AdministrationContract::class, 'administration_contract_id'); }
    public function changedBy(): BelongsTo { return $this->belongsTo(User::class, 'changed_by'); }
}

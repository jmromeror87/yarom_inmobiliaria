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
| Archivo: ContractClauseHistory.php
| Fecha: CURRENT_DAY/05/2026
| Versión: v1.0
|--------------------------------------------------------------------------
*/
        
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ContractClauseHistory extends Model
{
    protected $table = 'contract_clause_history';
    protected $fillable = ['acc_clause_id','editado_por','contenido_anterior','contenido_nuevo','razon_cambio','ip_address','editado_en'];
    protected $casts = ['editado_en' => 'datetime'];

    public function clause()  { return $this->belongsTo(AdministrationContractClause::class, 'acc_clause_id'); }
    public function editor()  { return $this->belongsTo(User::class, 'editado_por'); }
}

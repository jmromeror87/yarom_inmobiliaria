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
| Archivo: AdministrationContractClause.php
| Fecha: CURRENT_DAY/05/2026
| Versión: v1.0
|--------------------------------------------------------------------------
*/
        
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AdministrationContractClause extends Model
{
    protected $table = 'administration_contract_clauses';
    protected $fillable = [
        'administration_contract_id','contract_clause_id','numero','titulo','tipo',
        'contenido_original','contenido_actual','fue_editada','es_editable','es_obligatoria','orden',
    ];
    protected $casts = ['fue_editada' => 'boolean', 'es_editable' => 'boolean', 'es_obligatoria' => 'boolean'];

    public function contract() { return $this->belongsTo(AdministrationContract::class, 'administration_contract_id'); }
    public function history()  { return $this->hasMany(ContractClauseHistory::class, 'acc_clause_id')->latest(); }

    // Al guardar registra el histórico automáticamente
    protected static function booted(): void
    {
        static::updating(function (AdministrationContractClause $clause) {
            if ($clause->isDirty('contenido_actual')) {
                ContractClauseHistory::create([
                    'acc_clause_id'      => $clause->id,
                    'editado_por'        => Auth::id(),
                    'contenido_anterior' => $clause->getOriginal('contenido_actual'),
                    'contenido_nuevo'    => $clause->contenido_actual,
                    'ip_address'         => request()?->ip(),
                    'editado_en'         => now(),
                ]);
                $clause->fue_editada = true;
            }
        });
    }
}

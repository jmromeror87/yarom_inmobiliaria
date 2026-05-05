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
| Archivo: ContractNotaryTracking.php
| Fecha: CURRENT_DAY/05/2026
| Versión: v1.0
|--------------------------------------------------------------------------
*/
        
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractNotaryTracking extends Model
{
    protected $table = 'contract_notary_tracking';
    protected $fillable = [
        'administration_contract_id','gestionado_por',
        'notaria_nombre','notaria_ciudad','notaria_direccion','notaria_telefono',
        'fecha_envio_notaria','enviado_por_nombre','numero_radicado_notaria',
        'fecha_autenticacion','numero_escritura','valor_autenticacion',
        'fecha_regreso','recibido_por','path_contrato_firmado','observaciones',
    ];
    protected $casts = [
        'fecha_envio_notaria'  => 'date',
        'fecha_autenticacion'  => 'date',
        'fecha_regreso'        => 'date',
        'valor_autenticacion'  => 'decimal:2',
    ];

    public function contract(): BelongsTo { return $this->belongsTo(AdministrationContract::class, 'administration_contract_id'); }
    public function gestionadoPor(): BelongsTo { return $this->belongsTo(User::class, 'gestionado_por'); }
}

<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RentalContractStatusHistory extends Model
{
    protected $table = 'rental_contract_status_history';
    protected $fillable = [
        'rental_contract_id','changed_by','estado_anterior',
        'estado_nuevo','canal','razon_cambio','cambiado_en',
    ];
    protected $casts = ['cambiado_en' => 'datetime'];

    public function contract(): BelongsTo { return $this->belongsTo(RentalContract::class, 'rental_contract_id'); }
    public function changedBy(): BelongsTo { return $this->belongsTo(User::class, 'changed_by'); }
}

<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RentalContractThird extends Model
{
    protected $table = 'rental_contract_thirds';
    protected $fillable = [
        'rental_contract_id','third_id','rol','ciudad_expedicion_doc',
        'direccion_notificacion','email_notificacion','celular_notificacion','orden',
    ];

    public function contract(): BelongsTo { return $this->belongsTo(RentalContract::class, 'rental_contract_id'); }
    public function third(): BelongsTo    { return $this->belongsTo(Third::class); }
}

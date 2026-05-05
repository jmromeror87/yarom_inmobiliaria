<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RentalContractClause extends Model
{
    protected $table = 'rental_contract_clauses';
    protected $fillable = [
        'rental_contract_id','contract_clause_id','numero','titulo','tipo',
        'contenido_original','contenido_actual','fue_editada','es_editable','es_obligatoria','orden',
    ];
    protected $casts = ['fue_editada' => 'boolean', 'es_editable' => 'boolean', 'es_obligatoria' => 'boolean'];

    protected static function booted(): void
    {
        static::updating(function ($clause) {
            if ($clause->isDirty('contenido_actual')) {
                $clause->fue_editada = true;
            }
        });
    }

    public function contract(): BelongsTo { return $this->belongsTo(RentalContract::class, 'rental_contract_id'); }
}

<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyHandoverHistory extends Model
{
    protected $table = 'property_handover_history';
    protected $fillable = [
        'property_handover_id','changed_by','estado_anterior',
        'estado_nuevo','canal','razon_cambio','ip_address','cambiado_en',
    ];
    protected $casts = ['cambiado_en' => 'datetime'];

    public function handover(): BelongsTo { return $this->belongsTo(PropertyHandover::class, 'property_handover_id'); }
    public function changedBy(): BelongsTo { return $this->belongsTo(User::class, 'changed_by'); }
}

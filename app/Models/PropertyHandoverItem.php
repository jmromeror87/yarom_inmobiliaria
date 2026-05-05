<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyHandoverItem extends Model
{
    protected $table = 'property_handover_items';
    protected $fillable = [
        'property_handover_id','ambiente','ambiente_detalle',
        'elemento','estado','descripcion','foto_path','orden',
    ];

    public function handover(): BelongsTo { return $this->belongsTo(PropertyHandover::class, 'property_handover_id'); }

    public function getEstadoColorAttribute(): string
    {
        return match($this->estado) {
            'excelente' => '#15803d',
            'bueno'     => '#2563eb',
            'regular'   => '#d97706',
            'malo'      => '#dc2626',
            default     => '#64748b',
        };
    }
}

<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OwnerLiquidationStatusHistory extends Model
{
    protected $table = 'owner_liquidation_status_histories';

    protected $fillable = [
        'owner_liquidation_id', 'estado_anterior', 'estado_nuevo',
        'usuario_id', 'ip', 'notas', 'cambiado_en',
    ];

    protected $casts = ['cambiado_en' => 'datetime'];

    public static array $labels = [
        'pendiente' => 'Pendiente',
        'aprobada'  => 'Aprobada',
        'pagada'    => 'Pagada',
        'anulada'   => 'Anulada',
    ];

    public function liquidacion(): BelongsTo
    {
        return $this->belongsTo(OwnerLiquidation::class, 'owner_liquidation_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function getEstadoAnteriorLabelAttribute(): string
    {
        return self::$labels[$this->estado_anterior] ?? $this->estado_anterior ?? '—';
    }

    public function getEstadoNuevoLabelAttribute(): string
    {
        return self::$labels[$this->estado_nuevo] ?? $this->estado_nuevo;
    }
}

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
| Archivo: Request.php
| Fecha: CURRENT_DAY/05/2026
| Versión: v1.0
|--------------------------------------------------------------------------
*/
        
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Request extends Model
{
    use SoftDeletes;

    protected $table = 'requests';

    protected $fillable = [
        'numero','property_id','asesor_id','tipo','estado',
        'canon_evaluar','precio_venta_evaluar',
        'fecha_radicacion','fecha_decision','decidido_por',
        'concepto_evaluacion','condiciones_especiales',
        'estado_inmueble_anterior','estado_inmueble_nuevo',
        'cambio_estado_aplicado','notas',
    ];

    protected $casts = [
        'fecha_radicacion'        => 'date',
        'fecha_decision'          => 'date',
        'cambio_estado_aplicado'  => 'boolean',
        'canon_evaluar'           => 'decimal:2',
        'precio_venta_evaluar'    => 'decimal:2',
    ];

    // ── Auto-generar número ──────────────────────────────────
    protected static function booted(): void
    {
        static::creating(function (Request $r) {
            if (empty($r->numero)) {
                $year  = now()->year;
                $count = static::whereYear('created_at', $year)->count() + 1;
                $r->numero = 'SOL-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
            }
            if (empty($r->fecha_radicacion)) {
                $r->fecha_radicacion = now()->toDateString();
            }
        });

        // ── Automatizar estado del inmueble al aprobar ───────
        static::updating(function (Request $r) {
            if ($r->isDirty('estado') && $r->estado === 'aprobada') {
                $property = Property::find($r->property_id);
                if ($property) {
                    $r->estado_inmueble_anterior = $property->estado;

                    $nuevoEstado = match($r->tipo) {
                        'estudio_propietario'  => 'disponible',
                        'estudio_arrendatario' => 'arrendado',
                        'estudio_comprador'    => 'en_venta',
                        default                => $property->estado,
                    };

                    $r->estado_inmueble_nuevo   = $nuevoEstado;
                    $r->cambio_estado_aplicado  = true;
                    $r->fecha_decision          = now()->toDateString();

                    $property->update(['estado' => $nuevoEstado]);
                }
            }

            if ($r->isDirty('estado') && in_array($r->estado, ['rechazada', 'desistida'])) {
                $r->fecha_decision = now()->toDateString();
            }
        });
    }

    public function property(): BelongsTo  { return $this->belongsTo(Property::class); }
    public function asesor(): BelongsTo    { return $this->belongsTo(User::class, 'asesor_id'); }
    public function thirds(): HasMany      { return $this->hasMany(RequestThird::class); }
    public function suraStudies(): HasMany { return $this->hasMany(RequestSuraStudy::class); } public function documents(): HasMany   { return $this->hasMany(RequestDocument::class); }

    public function getTipoLabelAttribute(): string
    {
        return match($this->tipo) {
            'estudio_propietario'  => 'Estudio propietario',
            'estudio_arrendatario' => 'Estudio arrendatario',
            'estudio_comprador'    => 'Estudio comprador',
            default                => $this->tipo,
        };
    }
}

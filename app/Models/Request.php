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

    const ESTADOS = [
        'radicada'          => 'Radicada',
        'en_estudio'        => 'En estudio',
        'aprobada'          => 'Aprobada (SURA)',
        'aprobada_gerente'  => 'Aprobada por gerente',
        'condicional'       => 'Condicional',
        'rechazada'         => 'Rechazada',
        'desistida'         => 'Desistida',
    ];

    protected $fillable = [
        'numero','property_id','asesor_id','tipo','estado',
        'tipo_aprobacion',
        'canon_evaluar','precio_venta_evaluar',
        'tarifa_estudio_cobrada',
        'fecha_radicacion','fecha_decision','decidido_por',
        'concepto_evaluacion','condiciones_especiales',
        'justificacion_gerente',
        'estado_inmueble_anterior','estado_inmueble_nuevo',
        'cambio_estado_aplicado','notas',
        'aprobado_por_id',
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

        // ── Registrar fecha de decisión y actualizar terceros ─
        static::updating(function (Request $r) {
            if (!$r->isDirty('estado')) return;

            $estadosDecision = ['aprobada', 'aprobada_gerente', 'rechazada', 'desistida', 'condicional'];

            if (in_array($r->estado, $estadosDecision)) {
                $r->fecha_decision = now()->toDateString();
            }

            // Actualizar estado crediticio del tercero titular
            $resultadoCrediticio = match($r->estado) {
                'aprobada', 'aprobada_gerente' => 'aprobado',
                'condicional'                  => 'condicional',
                'rechazada'                    => 'rechazado',
                default                        => null,
            };

            if ($resultadoCrediticio) {
                $titular = $r->thirds()->where('rol', 'titular')->with('third')->first();
                if ($titular?->third) {
                    $titular->third->update([
                        'estado_crediticio'           => $resultadoCrediticio,
                        'fecha_evaluacion_crediticia' => now()->toDateString(),
                        'notas_evaluacion'            => $r->concepto_evaluacion ?? $r->justificacion_gerente,
                        'tipo_garantia'               => $r->tipo_aprobacion === 'sura' ? 'poliza' : 'directa',
                    ]);
                }
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
            'estudio_arrendatario' => 'Estudio arrendatario',
            'estudio_comprador'    => 'Estudio comprador',
            default                => $this->tipo,
        };
    }
}

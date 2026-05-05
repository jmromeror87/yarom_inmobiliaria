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
| Archivo: AdministrationContract.php
| Fecha: CURRENT_DAY/05/2026
| Versión: v1.0
|--------------------------------------------------------------------------
*/
    
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Auth;

class AdministrationContract extends Model
{
    use SoftDeletes;

    protected $table = 'administration_contracts';

    protected $fillable = [
        'numero_contrato','contract_template_id','property_id','propietario_id','asesor_id',
        'tipo_contrato','fecha_inicio','fecha_fin','renovacion','dias_aviso_terminacion',
        'canon_pactado','comision_porcentaje','comision_venta_porcentaje',
        'estado','fecha_firma','firmado_por','notas',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin'    => 'date',
        'fecha_firma'  => 'date',
        'canon_pactado'         => 'decimal:2',
        'comision_porcentaje'   => 'decimal:2',
    ];

    // ── Estados del flujo legal colombiano ──────────────────
    const ESTADOS = [
        'borrador'             => 'Borrador',
        'enviado_propietario'  => 'Enviado al propietario',
        'en_revision'          => 'En revisión propietario',
        'aprobado_gerencia'    => 'Aprobado por gerencia',
        'enviado_notaria'      => 'Enviado a notaría',
        'autenticado_notaria'  => 'Autenticado en notaría',
        'firmado'              => 'Firmado y autenticado',
        'activo'               => 'Activo',
        'terminado'            => 'Terminado',
        'cancelado'            => 'Cancelado',
    ];

    // ── Estados que bloquean edición ─────────────────────────
    const ESTADOS_CERRADOS = ['activo', 'terminado', 'cancelado'];
    const ESTADOS_READONLY = ['firmado', 'activo', 'terminado', 'cancelado'];

    protected static function booted(): void
    {
        static::creating(function ($c) {
            if (empty($c->numero_contrato)) {
                $year  = now()->year;
                $ultimo = static::whereYear('created_at', $year)->max('numero_contrato'); $count = $ultimo ? ((int)substr($ultimo, -4)) + 1 : 1;
                $c->numero_contrato = 'CAD-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
            }
        });

        // ── Auto-registrar histórico de estados ──────────────
        static::updating(function ($c) {
            if ($c->isDirty('estado')) {
                ContractStatusHistory::create([
                    'administration_contract_id' => $c->id,
                    'changed_by'    => Auth::id(),
                    'estado_anterior' => $c->getOriginal('estado'),
                    'estado_nuevo'    => $c->estado,
                    'canal'           => 'sistema',
                    'ip_address'      => request()?->ip(),
                    'cambiado_en'     => now(),
                ]);

                // Si pasa a activo → actualizar inmueble a arrendado
                if ($c->estado === 'activo') {
                    Property::find($c->property_id)?->update(['estado' => 'disponible']);
                }
                // Si se cancela → inmueble vuelve a disponible
                if ($c->estado === 'cancelado') {
                    Property::find($c->property_id)?->update(['estado' => 'disponible']);
                }
            }
        });
    }

    // ── Helpers ──────────────────────────────────────────────
    public function isReadOnly(): bool
    {
        return in_array($this->estado, self::ESTADOS_READONLY);
    }

    public function diasParaVencer(): int
    {
        return (int) now()->diffInDays($this->fecha_fin, false);
    }

    public function estaProximoAVencer(): bool
    {
        $dias = $this->diasParaVencer();
        return $dias >= 0 && $dias <= 30;
    }

    public function estaVencido(): bool
    {
        return $this->diasParaVencer() < 0;
    }

    // ── Relaciones ───────────────────────────────────────────
    public function template(): BelongsTo    { return $this->belongsTo(ContractTemplate::class, 'contract_template_id'); }
    public function property(): BelongsTo    { return $this->belongsTo(Property::class); }
    public function propietario(): BelongsTo { return $this->belongsTo(Third::class, 'propietario_id'); }
    public function asesor(): BelongsTo      { return $this->belongsTo(User::class, 'asesor_id'); }
    public function clauses(): HasMany       { return $this->hasMany(AdministrationContractClause::class)->orderBy('orden'); }
    public function statusHistory(): HasMany { return $this->hasMany(ContractStatusHistory::class)->orderByDesc('cambiado_en'); }
    public function notaryTracking(): HasOne { return $this->hasOne(ContractNotaryTracking::class)->latest(); }
}

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
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Auth;

class AdministrationContract extends Model
{
    use SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['numero_contrato', 'estado', 'canon_administracion', 'porcentaje_administracion', 'fecha_inicio', 'fecha_fin'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->setDescriptionForEvent(fn (string $e) => match($e) {
                'created' => 'Contrato administración creado',
                'updated' => 'Contrato administración actualizado',
                'deleted' => 'Contrato administración eliminado',
                default   => $e,
            });
    }

    protected $table = 'administration_contracts';

    protected $fillable = [
        'numero_contrato','contract_template_id','property_id','propietario_id','asesor_id',
        'tipo_contrato','fecha_inicio','fecha_fin','renovacion','dias_aviso_terminacion',
        'canon_pactado','comision_porcentaje','incluye_administracion','cuota_administracion_valor',
        'autoriza_venta','comision_venta_porcentaje','precio_venta_pactado',
        'estado','fecha_firma','firmado_por','notas',
    ];

    protected $casts = [
        'fecha_inicio'              => 'date',
        'fecha_fin'                 => 'date',
        'fecha_firma'               => 'date',
        'canon_pactado'             => 'decimal:2',
        'comision_porcentaje'       => 'decimal:2',
        'comision_venta_porcentaje' => 'decimal:2',
        'cuota_administracion_valor'=> 'decimal:2',
        'precio_venta_pactado'      => 'decimal:2',
        'incluye_administracion'    => 'boolean',
        'autoriza_venta'            => 'boolean',
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

    // ── Estados que bloquean edición de contenido ────────────
    const ESTADOS_CERRADOS = ['terminado', 'cancelado'];
    const ESTADOS_READONLY = ['activo', 'terminado', 'cancelado'];

    protected $attributes = [
        'estado'                    => 'borrador',
        'renovacion'                => 'automatica',
        'dias_aviso_terminacion'    => 30,
        'comision_porcentaje'       => 10,
        'comision_venta_porcentaje' => 3,
        'cuota_administracion_valor'=> 0,
        'incluye_administracion'    => false,
        'autoriza_venta'            => false,
    ];

    protected static function booted(): void
    {
        static::creating(function ($c) {
            // Garantizar defaults aunque el form no los envíe
            $c->cuota_administracion_valor = $c->cuota_administracion_valor ?? 0;
            $c->comision_porcentaje        = $c->comision_porcentaje ?? 10;
            $c->comision_venta_porcentaje  = $c->comision_venta_porcentaje ?? 3;
            $c->dias_aviso_terminacion     = $c->dias_aviso_terminacion ?? 30;
            $c->incluye_administracion     = $c->incluye_administracion ?? false;
            $c->autoriza_venta             = $c->autoriza_venta ?? false;

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

                // Contrato activo → inmueble disponible para recibir solicitudes de arriendo
                if ($c->estado === 'activo') {
                    Property::find($c->property_id)?->update(['estado' => 'disponible']);
                }

                // Contrato terminado o cancelado → inmueble vuelve a captación
                if (in_array($c->estado, ['terminado', 'cancelado'])) {
                    Property::find($c->property_id)?->update(['estado' => 'en_captacion']);
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

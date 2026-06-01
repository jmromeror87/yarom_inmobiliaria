<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Support\LogOptions;
use App\Models\CuentaPorCobrar;
use Spatie\Activitylog\Models\Concerns\LogsActivity;

class RentalContract extends Model
{
    use SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['numero_contrato', 'estado', 'canon_mensual', 'fecha_inicio', 'fecha_fin', 'arrendatario_id', 'property_id'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->setDescriptionForEvent(fn (string $e) => match($e) {
                'created' => 'Contrato arriendo creado',
                'updated' => 'Contrato arriendo actualizado',
                'deleted' => 'Contrato arriendo eliminado',
                default   => $e,
            });
    }

    protected $table = 'rental_contracts';

    protected $fillable = [
        'numero_contrato','property_id','administration_contract_id','request_id',
        'contract_template_id','asesor_id','tipo','lugar_contrato','fecha_contrato',
        'destinacion','actividad_comercial','folio_inmobiliario','arrendatario_id',
        'canon_mensual','deposito','cuota_administracion','fecha_inicio','fecha_fin',
        'duracion_meses','tipo_incremento','porcentaje_incremento','meses_preaviso',
        'servicios_cargo_arrendatario','tipo_garantia','estado','fecha_firma',
        'firmado_por','path_contrato_firmado','fecha_terminacion','causal_terminacion','notas',
        'admin_cobrada_por','mora_solo_sobre_canon',
        'estado_deposito','fecha_pago_deposito','deposito_pagado','notas_deposito',
    ];

    protected $casts = [
        'fecha_contrato'    => 'date',
        'fecha_inicio'      => 'date',
        'fecha_fin'         => 'date',
        'fecha_firma'       => 'date',
        'fecha_terminacion' => 'date',
        'canon_mensual'     => 'decimal:2',
        'deposito'          => 'decimal:2',
        'cuota_administracion'  => 'decimal:2',
        'mora_solo_sobre_canon' => 'boolean',
        'deposito_pagado'       => 'decimal:2',
        'fecha_pago_deposito'   => 'date',
    ];

    const ESTADOS_READONLY = ['activo', 'terminado', 'cancelado'];

 protected static function booted(): void
{
    static::creating(function ($c) {
        if (empty($c->numero_contrato)) {
            $year   = now()->year;
            $tipo   = $c->tipo === 'comercial' ? 'COM' : 'VIV';
            $ultimo = static::whereYear('created_at', $year)->max('numero_contrato');
            $count  = $ultimo ? ((int)substr($ultimo, -4)) + 1 : 1;
            $c->numero_contrato = $tipo . '-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
        }
    });

    static::updating(function ($c) {
        if ($c->isDirty('estado')) {
            if ($c->estado === 'activo') {
                Property::find($c->property_id)?->update(['estado' => 'arrendado']);
            }
            if (in_array($c->estado, ['terminado', 'cancelado'])) {
                Property::find($c->property_id)?->update(['estado' => 'disponible']);
            }
        }
    });

    static::saved(function (self $c) {
        // Crear cuenta por cobrar por depósito si corresponde
        if (
            $c->wasChanged('estado_deposito') &&
            $c->estado_deposito === 'en_cartera' &&
            $c->deposito > 0
        ) {
            $yaExiste = CuentaPorCobrar::where('rental_contract_id', $c->id)
                ->where('tipo', 'deposito_arriendo')
                ->exists();

            if (! $yaExiste) {
                $saldo = $c->deposito - ($c->deposito_pagado ?? 0);
                CuentaPorCobrar::create([
                    'tipo'               => 'deposito_arriendo',
                    'concepto'           => "Depósito en garantía - Contrato {$c->numero_contrato}",
                    'rental_contract_id' => $c->id,
                    'third_id'           => $c->arrendatario_id,
                    'property_id'        => $c->property_id,
                    'valor_original'     => $c->deposito,
                    'valor_pagado'       => $c->deposito_pagado ?? 0,
                    'saldo'              => max(0, $saldo),
                    'estado'             => $saldo <= 0 ? 'pagado' : (($c->deposito_pagado ?? 0) > 0 ? 'parcial' : 'pendiente'),
                    'fecha_origen'       => $c->fecha_inicio ?? today(),
                    'fecha_vencimiento'  => ($c->fecha_inicio ?? today())->addDays(30),
                ]);
            }
        }
    });
}

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
        $aviso = $this->meses_preaviso * 30;
        return $dias >= 0 && $dias <= $aviso;
    }

    public function estaVencido(): bool { return $this->diasParaVencer() < 0; }

    // ── Relaciones ───────────────────────────────────────────
    public function statusHistory(): HasMany { return $this->hasMany(RentalContractStatusHistory::class)->orderByDesc('cambiado_en'); }

    public function property(): BelongsTo              { return $this->belongsTo(Property::class); }
    public function administrationContract(): BelongsTo { return $this->belongsTo(AdministrationContract::class); }
    public function request(): BelongsTo               { return $this->belongsTo(Request::class); }
    public function template(): BelongsTo              { return $this->belongsTo(ContractTemplate::class, 'contract_template_id'); }
    public function asesor(): BelongsTo                { return $this->belongsTo(User::class, 'asesor_id'); }
    public function arrendatario(): BelongsTo          { return $this->belongsTo(Third::class, 'arrendatario_id'); }
    public function clauses(): HasMany                 { return $this->hasMany(RentalContractClause::class)->orderBy('orden'); }
    public function thirds(): HasMany                  { return $this->hasMany(RentalContractThird::class)->orderBy('orden'); }
    public function cuentasPorCobrar(): HasMany        { return $this->hasMany(CuentaPorCobrar::class); }
    public function depositoCartera(): ?CuentaPorCobrar
    {
        return $this->cuentasPorCobrar()->where('tipo', 'deposito_arriendo')->latest()->first();
    }
}

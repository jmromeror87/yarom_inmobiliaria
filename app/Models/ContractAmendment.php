<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Activitylog\Models\Concerns\LogsActivity;

class ContractAmendment extends Model
{
    use SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['numero', 'tipo', 'titulo', 'estado', 'fecha_firma', 'fecha_vigencia', 'valor_nuevo', 'fecha_fin_nueva'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->setDescriptionForEvent(fn(string $e) => match($e) {
                'created' => 'Otrosí creado',
                'updated' => 'Otrosí actualizado',
                'deleted' => 'Otrosí eliminado',
                default   => $e,
            });
    }

    protected $table = 'contract_amendments';

    protected $fillable = [
        'numero', 'rental_contract_id', 'administration_contract_id',
        'tipo', 'titulo', 'descripcion', 'clausula_modificada',
        'valor_anterior', 'valor_nuevo',
        'fecha_fin_anterior', 'fecha_fin_nueva',
        'texto_anterior', 'texto_nuevo',
        'fecha_firma', 'fecha_vigencia',
        'estado', 'aplica_cambio_automatico', 'cambio_aplicado', 'cambio_aplicado_en',
        'firmado_por_arrendador', 'firmado_por_arrendatario', 'firmado_por_garante',
        'path_documento', 'notas', 'created_by',
    ];

    protected $casts = [
        'fecha_firma'            => 'date',
        'fecha_vigencia'         => 'date',
        'fecha_fin_anterior'     => 'date',
        'fecha_fin_nueva'        => 'date',
        'cambio_aplicado_en'     => 'datetime',
        'valor_anterior'         => 'decimal:2',
        'valor_nuevo'            => 'decimal:2',
        'aplica_cambio_automatico' => 'boolean',
        'cambio_aplicado'        => 'boolean',
    ];

    const TIPOS = [
        'incremento_canon'      => 'Incremento de canon',
        'prorroga'              => 'Prórroga de plazo',
        'cesion_arrendatario'   => 'Cesión del arrendatario',
        'cambio_codeudor'       => 'Cambio de codeudor/garante',
        'adicion_areas'         => 'Adición de áreas',
        'modificacion_clausula' => 'Modificación de cláusula',
        'cambio_comision'       => 'Cambio de comisión',
        'otro'                  => 'Otro',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $a) {
            $a->created_by = $a->created_by ?? Auth::id();

            if (empty($a->numero)) {
                $a->numero = static::generarNumero($a);
            }
        });

        static::created(function (self $a) {
            $a->registrarEnHistorial('borrador', 'borrador', 'Otrosí creado: ' . $a->titulo);
        });

        static::updated(function (self $a) {
            if ($a->wasChanged('estado')) {
                $anterior = $a->getOriginal('estado');
                $nuevo    = $a->estado;

                $a->registrarEnHistorial(
                    'otrosi_' . $anterior,
                    'otrosi_' . $nuevo,
                    match($nuevo) {
                        'firmado' => "Otrosí firmado: {$a->titulo} ({$a->numero})",
                        'anulado' => "Otrosí anulado: {$a->titulo} ({$a->numero})",
                        default   => "Otrosí actualizado: {$a->titulo} ({$a->numero})",
                    }
                );

                if ($nuevo === 'firmado' && $a->aplica_cambio_automatico && !$a->cambio_aplicado) {
                    $a->aplicarCambioAlContrato();
                }
            }
        });
    }

    protected function registrarEnHistorial(string $estadoAnterior, string $estadoNuevo, string $razon): void
    {
        $userId = Auth::id();

        if ($this->rental_contract_id) {
            RentalContractStatusHistory::create([
                'rental_contract_id' => $this->rental_contract_id,
                'changed_by'         => $userId,
                'estado_anterior'    => $estadoAnterior,
                'estado_nuevo'       => $estadoNuevo,
                'canal'              => 'otrosi',
                'razon_cambio'       => $razon,
                'cambiado_en'        => now(),
            ]);
        }

        if ($this->administration_contract_id) {
            ContractStatusHistory::create([
                'administration_contract_id' => $this->administration_contract_id,
                'changed_by'                 => $userId,
                'estado_anterior'            => $estadoAnterior,
                'estado_nuevo'               => $estadoNuevo,
                'canal'                      => 'otrosi',
                'razon_cambio'               => $razon,
                'ip_address'                 => request()?->ip(),
                'cambiado_en'                => now(),
            ]);
        }
    }

    public static function generarNumero(self $a): string
    {
        $year = now()->year;

        if ($a->rental_contract_id) {
            $contrato = RentalContract::find($a->rental_contract_id);
            $prefijo  = 'OTROSI-' . ($contrato?->numero_contrato ?? 'VIV');
        } else {
            $contrato = AdministrationContract::find($a->administration_contract_id);
            $prefijo  = 'OTROSI-' . ($contrato?->numero_contrato ?? 'CAD');
        }

        $count = static::where(function ($q) use ($a) {
            $q->where('rental_contract_id', $a->rental_contract_id)
              ->where('administration_contract_id', $a->administration_contract_id);
        })->withTrashed()->count() + 1;

        return $prefijo . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
    }

    public function aplicarCambioAlContrato(): void
    {
        if ($this->rental_contract_id) {
            $contrato = RentalContract::find($this->rental_contract_id);
            if ($contrato) {
                match ($this->tipo) {
                    'incremento_canon' => $this->valor_nuevo ? $contrato->update(['canon_mensual' => $this->valor_nuevo]) : null,
                    'prorroga'         => $this->fecha_fin_nueva ? $contrato->update(['fecha_fin' => $this->fecha_fin_nueva]) : null,
                    default            => null,
                };
            }
        }

        if ($this->administration_contract_id) {
            $contrato = AdministrationContract::find($this->administration_contract_id);
            if ($contrato) {
                match ($this->tipo) {
                    'prorroga'       => $this->fecha_fin_nueva ? $contrato->update(['fecha_fin' => $this->fecha_fin_nueva]) : null,
                    'cambio_comision'=> $this->valor_nuevo ? $contrato->update(['comision_porcentaje' => $this->valor_nuevo]) : null,
                    default          => null,
                };
            }
        }

        $this->updateQuietly([
            'cambio_aplicado'    => true,
            'cambio_aplicado_en' => now(),
        ]);
    }

    public function getTipoLabelAttribute(): string
    {
        return self::TIPOS[$this->tipo] ?? $this->tipo;
    }

    // ── Relaciones ───────────────────────────────────────────
    public function rentalContract(): BelongsTo       { return $this->belongsTo(RentalContract::class); }
    public function administrationContract(): BelongsTo { return $this->belongsTo(AdministrationContract::class); }
    public function createdBy(): BelongsTo            { return $this->belongsTo(User::class, 'created_by'); }
}

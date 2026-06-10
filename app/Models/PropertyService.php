<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class PropertyService extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'property_id', 'third_id', 'rental_contract_id', 'owner_liquidation_id',
        'accounting_entry_id', 'created_by',
        'numero', 'tipo', 'descripcion', 'fecha_servicio', 'fecha_pago_proveedor',
        'valor', 'iva', 'retencion',
        'quien_paga', 'estado', 'estado_pago_proveedor',
        'cuenta_gasto_puc', 'cuenta_pagar_puc', 'notas',
    ];

    protected $casts = [
        'fecha_servicio'      => 'date',
        'fecha_pago_proveedor' => 'date',
        'valor'     => 'decimal:2',
        'iva'       => 'decimal:2',
        'retencion' => 'decimal:2',
    ];

    // ── Relaciones ───────────────────────────────────────────

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function proveedor()
    {
        return $this->belongsTo(Third::class, 'third_id');
    }

    public function rentalContract()
    {
        return $this->belongsTo(RentalContract::class);
    }

    public function ownerLiquidation()
    {
        return $this->belongsTo(OwnerLiquidation::class);
    }

    public function accountingEntry()
    {
        return $this->belongsTo(AccountingEntry::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    // ── Accessors ────────────────────────────────────────────

    public function getValorNetoAttribute(): float
    {
        return (float)$this->valor + (float)$this->iva - (float)$this->retencion;
    }

    public function getTipoLabelAttribute(): string
    {
        return match($this->tipo) {
            'mantenimiento' => 'Mantenimiento',
            'reparacion'    => 'Reparación',
            'remodelacion'  => 'Remodelación',
            'limpieza'      => 'Limpieza',
            'inspeccion'    => 'Inspección',
            'otro'          => 'Otro',
            default         => ucfirst($this->tipo),
        };
    }

    public function getQuienPagaLabelAttribute(): string
    {
        return match($this->quien_paga) {
            'propietario'     => 'Propietario',
            'inquilino'       => 'Inquilino',
            'deduccion_canon' => 'Deducción del canon',
            default           => $this->quien_paga,
        };
    }

    // ── Métodos de negocio ───────────────────────────────────

    public static function generarNumero(): string
    {
        $anio = now()->year;
        $ultimo = static::where('numero', 'like', "SRV-{$anio}-%")
            ->orderByDesc('numero')->first();
        $seq = $ultimo ? ((int) substr($ultimo->numero, -4)) + 1 : 1;
        return "SRV-{$anio}-" . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Genera comprobante contable (cuenta por pagar al proveedor)
     * y marca el servicio como pagado.
     */
    public function contabilizar(): void
    {
        if ($this->accounting_entry_id) {
            return;
        }

        $period = AccountingPeriod::actual();
        if (! $period) {
            throw new \RuntimeException('No hay un período contable abierto.');
        }

        $cuentaGasto  = $this->cuenta_gasto_puc  ?: '513595'; // Mantenimiento y reparaciones
        $cuentaPagar  = $this->cuenta_pagar_puc   ?: '220501'; // Proveedores nacionales

        $accountGasto = AccountingAccount::where('codigo', $cuentaGasto)->first();
        $accountPagar = AccountingAccount::where('codigo', $cuentaPagar)->first();

        if (! $accountGasto || ! $accountPagar) {
            throw new \RuntimeException("Cuentas PUC no encontradas: {$cuentaGasto} / {$cuentaPagar}. Configúrelas en el servicio.");
        }

        $entry = AccountingEntry::create([
            'tipo'        => 'CC',
            'numero'      => AccountingEntry::generarNumero('CC'),
            'fecha'       => now()->toDateString(),
            'period_id'   => $period->id,
            'descripcion' => "Servicio {$this->numero} — {$this->descripcion} — Proveedor: {$this->proveedor->nombre_completo}",
            'third_id'    => $this->third_id,
            'referencia'  => $this->numero,
            'estado'      => 'borrador',
        ]);

        // Línea gasto/activo (débito)
        $entry->lines()->create([
            'account_id' => $accountGasto->id,
            'descripcion'=> $this->descripcion,
            'debito'     => $this->valor,
            'credito'    => 0,
            'third_id'   => $this->third_id,
            'orden'      => 1,
        ]);

        // IVA si aplica
        if ($this->iva > 0) {
            $cuentaIva = AccountingAccount::where('codigo', '240801')->first();
            if ($cuentaIva) {
                $entry->lines()->create([
                    'account_id' => $cuentaIva->id,
                    'descripcion'=> 'IVA servicio',
                    'debito'     => $this->iva,
                    'credito'    => 0,
                    'orden'      => 2,
                ]);
            }
        }

        // Retención si aplica (crédito a retefuente)
        if ($this->retencion > 0) {
            $cuentaRete = AccountingAccount::where('codigo', '236540')->first();
            if ($cuentaRete) {
                $entry->lines()->create([
                    'account_id' => $cuentaRete->id,
                    'descripcion'=> 'Retención en la fuente',
                    'debito'     => 0,
                    'credito'    => $this->retencion,
                    'orden'      => 3,
                ]);
            }
        }

        // Línea cuenta por pagar (crédito)
        $entry->lines()->create([
            'account_id' => $accountPagar->id,
            'descripcion'=> "Por pagar proveedor {$this->proveedor->nombre_completo}",
            'debito'     => 0,
            'credito'    => $this->valor_neto,
            'third_id'   => $this->third_id,
            'orden'      => 4,
        ]);

        $entry->recalcularTotales();
        $entry->contabilizar();

        $this->update([
            'accounting_entry_id' => $entry->id,
            'estado'              => 'ejecutado',
        ]);
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->numero)) {
                $model->numero = static::generarNumero();
            }
            if (Auth::check() && empty($model->created_by)) {
                $model->created_by = Auth::id();
            }
        });
    }
}

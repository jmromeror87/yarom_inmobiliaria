<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class OwnerLiquidation extends Model
{
    use SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['numero', 'estado', 'total_giro', 'fecha_giro', 'forma_giro'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->setDescriptionForEvent(fn (string $e) => match($e) {
                'created' => 'Liquidación creada',
                'updated' => 'Liquidación actualizada',
                'deleted' => 'Liquidación eliminada',
                default   => $e,
            });
    }

    protected $table = 'owner_liquidations';

    protected $fillable = [
        'numero', 'rental_contract_id', 'property_id', 'propietario_id',
        'mes', 'anio', 'periodo_inicio', 'periodo_fin',
        'canon_cobrado', 'comision_porcentaje', 'comision_valor', 'iva_comision',
        'aplica_retefuente', 'retefuente_valor',
        'seguro_sura_deducido',
        'otros_descuentos', 'descripcion_descuentos', 'total_giro',
        'estado', 'fecha_giro', 'forma_giro', 'banco_giro_id', 'referencia_giro',
        'comprobante_giro_path', 'wap_enviado', 'wap_enviado_at', 'notas',
        'motivo_anulacion', 'anulado_por_id', 'anulado_en',
    ];

    protected $casts = [
        'periodo_inicio'    => 'date',
        'periodo_fin'       => 'date',
        'fecha_giro'        => 'date',
        'wap_enviado'       => 'boolean',
        'wap_enviado_at'    => 'datetime',
        'aplica_retefuente' => 'boolean',
        'canon_cobrado'     => 'decimal:2',
        'comision_valor'    => 'decimal:2',
        'iva_comision'      => 'decimal:2',
        'retefuente_valor'      => 'decimal:2',
        'seguro_sura_deducido'  => 'decimal:2',
        'otros_descuentos'      => 'decimal:2',
        'total_giro'        => 'decimal:2',
        'anulado_en'        => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function ($l) {
            if (empty($l->numero)) {
                $year   = now()->year;
                $ultimo = static::whereYear('created_at', $year)->max('numero');
                $count  = $ultimo ? ((int)substr($ultimo, -4)) + 1 : 1;
                $l->numero = 'LIQ-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
            }
        });

        static::updating(function ($l) {
            if ($l->isDirty('estado')) {
                $l->statusHistories()->create([
                    'estado_anterior' => $l->getOriginal('estado'),
                    'estado_nuevo'    => $l->estado,
                    'usuario_id'      => Auth::id(),
                    'ip'              => request()?->ip(),
                    'cambiado_en'     => now(),
                ]);
            }

            // Si se editó manualmente algún componente de la liquidación
            // económica, el total a girar se recalcula siempre a partir de
            // ellos — nunca queda desincronizado. El seguro SURA NO se resta
            // aquí: "canon_cobrado" ya viene neto de seguro (ver
            // generarDesdeFact) — el campo seguro_sura_deducido es solo
            // informativo, para mostrar cuánto se le transfiere a ASURA.
            if ($l->isDirty(['canon_cobrado', 'comision_porcentaje', 'comision_valor', 'iva_comision', 'retefuente_valor', 'otros_descuentos'])) {
                $l->total_giro = max(0,
                    (float) $l->canon_cobrado
                    - (float) $l->comision_valor
                    - (float) $l->iva_comision
                    - (float) $l->retefuente_valor
                    - (float) $l->otros_descuentos
                );
            }
        });

        // Contabilización manejada exclusivamente por OwnerLiquidationObserver — no duplicar aquí
    }

    public static function generarDesdeFact(RentBill $bill): static|null
    {
        // Permitir re-liquidar si la anterior fue anulada
        $existeActiva = static::where('rental_contract_id', $bill->rental_contract_id)
            ->where('mes', $bill->mes)->where('anio', $bill->anio)
            ->whereNotIn('estado', ['anulada'])->exists();
        if ($existeActiva) return null;

        $contrato = $bill->rentalContract()->with(['property.propietario', 'arrendatario', 'administrationContract'])->first();
        if (!$contrato || !$contrato->property) return null;

        $company  = Company::first();

        $comisionPct = $contrato->administrationContract?->comision_porcentaje
            ?? $company?->comision_administracion ?? 10;

        $propietario = $contrato->property->propietario;

        // Comportamiento fiscal DIAN del propietario para ESTE inmueble en particular
        // (un propietario puede declarar IVA/retefuente en unos inmuebles y en otros no —
        // Property::requiereIva()/requiereRetefuente() resuelven la excepción por inmueble
        // antes de caer al comportamiento general del tercero).
        $aplicaIva  = $contrato->property->requiereIva();
        $aplicaRete = $contrato->property->requiereRetefuente();

        $ivaPct  = (float)($propietario?->tarifa_iva_pactada ?: $company?->tarifa_iva ?? 19);
        $retePct = (float)($propietario?->tarifa_retefuente_pactada ?: $company?->tarifa_retefuente_arrendamiento ?? 3.5);

        // Canon base del propietario: el canon pactado (nunca incluye el seguro
        // SURA en sí — eso es de paso hacia ASURA). La comisión se calcula
        // SOLO sobre este canon puro, nunca sobre el seguro ni su redondeo.
        $canon = (float) $bill->canon_base;

        // Si la cuota de administración la cobra la inmobiliaria para el propietario, incluirla
        if ($contrato->admin_cobrada_por === 'inmobiliaria') {
            $canon += (float)$bill->cuota_administracion;
        }

        $comisionValor = round($canon * ($comisionPct / 100), 2);
        $ivaComision   = $aplicaIva ? round($comisionValor * ($ivaPct / 100), 2) : 0;
        $retefuente    = $aplicaRete ? round($canon * ($retePct / 100), 2) : 0;

        // Seguro SURA: se cobró al inquilino pero la inmobiliaria lo paga a ASURA — no va al propietario.
        $seguroSura = (float)($bill->valor_seguro_sura ?? 0) + (float)($bill->iva_seguro_sura ?? 0);
        // El redondeo del seguro (diferencia entre lo cobrado al inquilino y el
        // total exacto) SÍ es del propietario — se suma al canon mostrado, pero
        // no lleva comisión (ya se calculó arriba sobre el canon puro).
        $redondeo = (float)($bill->redondeo_seguro ?? 0);
        $canonMostrado = $canon + $redondeo;

        // Administración que la inmobiliaria le paga al edificio por cuenta
        // del propietario (distinto de quién la COBRA al inquilino) — se
        // descuenta automáticamente como "otros descuentos" en cada
        // liquidación nueva, sin que nadie tenga que escribirlo a mano.
        $adminPagadaInmobiliaria = (float) ($contrato->admin_pagada_inmobiliaria_valor ?? 0);
        $descripcionDescuentos  = $adminPagadaInmobiliaria > 0
            ? 'Administración pagada por la inmobiliaria al edificio'
            : null;

        $liq = static::create([
            'rental_contract_id'  => $bill->rental_contract_id,
            'property_id'         => $bill->property_id,
            'propietario_id'      => $contrato->property->propietario_id,
            'mes'                 => $bill->mes,
            'anio'                => $bill->anio,
            'periodo_inicio'      => $bill->periodo_inicio,
            'periodo_fin'         => $bill->periodo_fin,
            'canon_cobrado'       => $canonMostrado,
            'comision_porcentaje' => $comisionPct,
            'comision_valor'      => $comisionValor,
            'iva_comision'        => $ivaComision,
            'aplica_retefuente'   => $aplicaRete,
            'retefuente_valor'    => $retefuente,
            'seguro_sura_deducido'=> $seguroSura,
            'otros_descuentos'    => $adminPagadaInmobiliaria,
            'descripcion_descuentos' => $descripcionDescuentos,
            'total_giro'          => max(0, $canonMostrado - $comisionValor - $ivaComision - $retefuente - $adminPagadaInmobiliaria),
            'estado'              => 'pendiente',
        ]);

        $bill->update(['owner_liquidation_id' => $liq->id]);
        return $liq;
    }

    /**
     * Anula la liquidación reversando cualquier asiento contable ya causado
     * (la causación de "otros descuentos" y, si ya se giró, el comprobante
     * de egreso del giro) y liberando las facturas asociadas para que
     * puedan volver a liquidarse — igual de blindado que
     * RentBill::anularConReversion().
     */
    public function anularConReversion(string $motivo, ?int $usuarioId = null): void
    {
        \Illuminate\Support\Facades\DB::transaction(function () use ($motivo, $usuarioId) {
            $entries = \App\Models\AccountingEntry::where('referencia_id', $this->id)
                ->whereIn('referencia_tipo', ['giro_owner', 'liquidacion_owner'])
                ->where('estado', '!=', 'anulado')
                ->get();

            foreach ($entries as $entry) {
                $entry->anular("Liquidación {$this->numero} anulada: {$motivo}");
            }

            // Libera las facturas del inquilino ligadas a esta liquidación
            // para que puedan volver a liquidarse (el generador exige
            // owner_liquidation_id nulo, y la guarda de generarDesdeFact ya
            // ignora liquidaciones anuladas al buscar una activa del mismo
            // contrato/período).
            $this->bills()->update(['owner_liquidation_id' => null]);

            $this->update([
                'estado'           => 'anulada',
                'motivo_anulacion' => $motivo,
                'anulado_por_id'   => $usuarioId,
                'anulado_en'       => now(),
            ]);
        });
    }

    public function getPeriodoLabelAttribute(): string
    {
        $meses = [
            1=>'Enero', 2=>'Febrero', 3=>'Marzo', 4=>'Abril',
            5=>'Mayo', 6=>'Junio', 7=>'Julio', 8=>'Agosto',
            9=>'Septiembre', 10=>'Octubre', 11=>'Noviembre', 12=>'Diciembre',
        ];
        return ($meses[$this->mes] ?? $this->mes) . ' ' . $this->anio;
    }

    public function rentalContract(): BelongsTo { return $this->belongsTo(RentalContract::class); }
    public function property(): BelongsTo       { return $this->belongsTo(Property::class); }
    public function propietario(): BelongsTo    { return $this->belongsTo(Third::class, 'propietario_id'); }
    public function bancoGiro(): BelongsTo      { return $this->belongsTo(Bank::class, 'banco_giro_id'); }
    public function bills(): HasMany            { return $this->hasMany(RentBill::class); }
    public function statusHistories(): HasMany  { return $this->hasMany(OwnerLiquidationStatusHistory::class)->orderByDesc('cambiado_en'); }
    public function anuladoPor(): BelongsTo     { return $this->belongsTo(\App\Models\User::class, 'anulado_por_id'); }
}

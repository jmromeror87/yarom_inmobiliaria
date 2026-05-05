<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class OwnerLiquidation extends Model
{
    use SoftDeletes;

    protected $table = 'owner_liquidations';

    protected $fillable = [
        'numero', 'rental_contract_id', 'property_id', 'propietario_id',
        'mes', 'anio', 'periodo_inicio', 'periodo_fin',
        'canon_cobrado', 'comision_porcentaje', 'comision_valor', 'iva_comision',
        'aplica_retefuente', 'retefuente_valor',
        'otros_descuentos', 'descripcion_descuentos', 'total_giro',
        'estado', 'fecha_giro', 'forma_giro', 'referencia_giro',
        'comprobante_giro_path', 'wap_enviado', 'wap_enviado_at', 'notas',
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
        'retefuente_valor'  => 'decimal:2',
        'otros_descuentos'  => 'decimal:2',
        'total_giro'        => 'decimal:2',
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
        });
    }

    public static function generarDesdeFact(RentBill $bill): static|null
    {
        $existe = static::where('rental_contract_id', $bill->rental_contract_id)
            ->where('mes', $bill->mes)->where('anio', $bill->anio)->exists();
        if ($existe) return null;

        $contrato = $bill->rentalContract;
        $company  = Company::first();

        $comisionPct = $contrato->administrationContract?->comision_porcentaje
            ?? $company?->comision_administracion ?? 10;

        $ivaPct  = (float)($company?->tarifa_iva ?? 19);
        $retePct = (float)($company?->tarifa_retefuente_arrendamiento ?? 3.5);
        $aplicaRete = false; // TODO: $contrato->arrendatario->es_agente_retenedor

        $canon         = (float)$bill->total_pagado;
        $comisionValor = round($canon * ($comisionPct / 100), 2);
        $ivaComision   = round($comisionValor * ($ivaPct  / 100), 2);
        $retefuente    = $aplicaRete ? round($canon * ($retePct / 100), 2) : 0;

        $liq = static::create([
            'rental_contract_id'  => $bill->rental_contract_id,
            'property_id'         => $bill->property_id,
            'propietario_id'      => $contrato->property->propietario_id,
            'mes'                 => $bill->mes,
            'anio'                => $bill->anio,
            'periodo_inicio'      => $bill->periodo_inicio,
            'periodo_fin'         => $bill->periodo_fin,
            'canon_cobrado'       => $canon,
            'comision_porcentaje' => $comisionPct,
            'comision_valor'      => $comisionValor,
            'iva_comision'        => $ivaComision,
            'aplica_retefuente'   => $aplicaRete,
            'retefuente_valor'    => $retefuente,
            'otros_descuentos'    => 0,
            'total_giro'          => max(0, $canon - $comisionValor - $ivaComision - $retefuente),
            'estado'              => 'pendiente',
        ]);

        $bill->update(['owner_liquidation_id' => $liq->id]);
        return $liq;
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
    public function bills(): HasMany            { return $this->hasMany(RentBill::class); }
    public function statusHistories(): HasMany  { return $this->hasMany(OwnerLiquidationStatusHistory::class)->orderByDesc('cambiado_en'); }
}

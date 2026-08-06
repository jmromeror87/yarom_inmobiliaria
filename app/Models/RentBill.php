<?php
namespace App\Models;

use App\Services\ContabilidadService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Log;

class RentBill extends Model
{
    use SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['numero', 'estado', 'total_factura', 'total_pagado', 'saldo_pendiente', 'mora_acumulada'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->setDescriptionForEvent(fn (string $e) => match($e) {
                'created' => 'Factura generada',
                'updated' => 'Factura actualizada',
                'deleted' => 'Factura eliminada',
                default   => $e,
            });
    }

    protected $table = 'rent_bills';

    protected $fillable = [
        'numero','rental_contract_id','property_id','arrendatario_id',
        'periodo_inicio','periodo_fin','mes','anio',
        'canon_base','cuota_administracion','descuentos','otros_cobros',
        'descripcion_otros_cobros','retencion_practicada','iva_practicado','reteiva_practicada',
        'saldo_anterior_arrastrado','nota_saldo_arrastrado',
        'valor_seguro_sura','iva_seguro_sura','redondeo_seguro',
        'total_factura',
        'fecha_limite_pago','dias_gracia','tasa_mora_diaria','aplicar_mora',
        'mora_acumulada','fecha_inicio_mora','dias_mora',
        'estado','total_pagado','saldo_pendiente','fecha_pago',
        'motivo_anulacion','anulado_por_id','anulado_en',
        'tipo_documento','cufe','numero_dian',
        'wap_enviado','wap_enviado_at','wap_mora_enviado','wap_mora_enviado_at',
        'owner_liquidation_id','notas',
        'contabilizado_via_historico','referencia_historico',
        'payment_token','payment_token_expires_at','wompi_transaction_id','wompi_reference',
    ];

    protected $casts = [
        'periodo_inicio'     => 'date',
        'periodo_fin'        => 'date',
        'fecha_limite_pago'  => 'date',
        'fecha_inicio_mora'  => 'date',
        'fecha_pago'         => 'date',
        'wap_enviado'        => 'boolean',
        'wap_enviado_at'     => 'datetime',
        'wap_mora_enviado'         => 'boolean',
        'wap_mora_enviado_at'      => 'datetime',
        'payment_token_expires_at' => 'datetime',
        'anulado_en'         => 'datetime',
        'aplicar_mora'       => 'boolean',
        'contabilizado_via_historico' => 'boolean',
        'saldo_anterior_arrastrado' => 'decimal:2',
        'canon_base'         => 'decimal:2',
        'valor_seguro_sura'  => 'decimal:2',
        'iva_seguro_sura'    => 'decimal:2',
        'redondeo_seguro'    => 'decimal:2',
        'total_factura'      => 'decimal:2',
        'retencion_practicada' => 'decimal:2',
        'iva_practicado'     => 'decimal:2',
        'reteiva_practicada' => 'decimal:2',
        'mora_acumulada'     => 'decimal:2',
        'total_pagado'       => 'decimal:2',
        'saldo_pendiente'    => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function ($b) {
            if (empty($b->numero)) {
                $year   = now()->year;
                $ultimo = static::whereYear('created_at', $year)->max('numero');
                $count  = $ultimo ? ((int)substr($ultimo, -4)) + 1 : 1;
                $b->numero = 'FAC-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
            }
        });

        // Recálculo de mora con efecto inmediato — antes solo se disparaba
        // al mover el toggle "aplicar_mora", así que si una factura se
        // generó sin mora por fechas de periodo mal calculadas, corregir
        // esas fechas desde "Ajustes" no reliquidaba nada hasta la corrida
        // diaria de VerificarMoraJob. Ahora también se dispara al corregir
        // fecha_limite_pago/periodo_inicio/periodo_fin/saldo_anterior_arrastrado,
        // para que las chicas puedan chequear mora + corregir fechas y quede
        // recalculado y reliquidado de una vez.
        static::updating(function (RentBill $b) {
            if (!$b->isDirty(['aplicar_mora', 'fecha_limite_pago', 'periodo_inicio', 'periodo_fin', 'saldo_anterior_arrastrado'])) return;

            $capital = round(
                (float) $b->total_factura + (float) $b->saldo_anterior_arrastrado - (float) $b->total_pagado,
                2
            );

            if (!$b->aplicar_mora) {
                $b->mora_acumulada    = 0;
                $b->dias_mora         = 0;
                $b->fecha_inicio_mora = null;
                $b->saldo_pendiente   = max(0, $capital);
                if ($b->estado === 'en_mora') {
                    $b->estado = ((float) $b->total_pagado > 0) ? 'parcial' : 'pendiente';
                }
                return;
            }

            $finGracia = $b->fecha_limite_pago->copy()->addDays($b->dias_gracia)->endOfDay();
            if (now()->lte($finGracia)) return; // aún en gracia, nada que calcular todavía

            $diasMora = (int) $b->fecha_limite_pago->copy()->startOfDay()->diffInDays(now()->startOfDay());

            $baseParaMora = $capital;
            if ($b->rentalContract?->mora_solo_sobre_canon && $b->canon_base > 0) {
                $proporcionCanon = $b->canon_base / max($b->total_factura, 1);
                $baseParaMora    = round($capital * $proporcionCanon, 2);
            }

            $mora = round($baseParaMora * ($b->tasa_mora_diaria / 100) * $diasMora, 2);

            $b->dias_mora         = $diasMora;
            $b->mora_acumulada    = $mora;
            $b->saldo_pendiente   = max(0, round($capital + $mora, 2));
            $b->fecha_inicio_mora = $b->fecha_inicio_mora ?? $b->fecha_limite_pago->toDateString();
            if ($b->estado !== 'pagada') {
                $b->estado = 'en_mora';
            }
        });

        // Contabilización manejada exclusivamente por RentBillObserver — no duplicar aquí

        // Blindaje: generar la liquidación al propietario en CUALQUIER
        // punto donde la factura pase a estado "pagada" — no solo cuando
        // se registra un pago (ver RentPayment::created). Comandos de
        // corrección/reversión de mora u otros procesos que actualizan
        // el estado directamente también deben disparar la liquidación.
        static::updated(function (RentBill $b) {
            if ($b->wasChanged('estado') && $b->estado === 'pagada') {
                OwnerLiquidation::generarDesdeFact($b);
            }
        });
    }

    // ── Payment token ────────────────────────────────────
    public function generatePaymentToken(): string
    {
        $token = bin2hex(random_bytes(32));
        $this->update([
            'payment_token'            => $token,
            // El link debe seguir funcionando mientras la factura esté sin
            // pagar (con mora incluida) — antes vencía justo en la fecha
            // límite, dejando al inquilino sin forma de pagar en línea
            // apenas se atrasaba un día.
            'payment_token_expires_at' => $this->fecha_limite_pago->copy()->addMonths(6)->endOfDay(),
        ]);
        return $token;
    }

    // ── Helpers ──────────────────────────────────────────
    public function calcularMora(): float
    {
        if (!$this->fecha_inicio_mora) return 0;
        $dias = now()->diffInDays($this->fecha_inicio_mora);
        return round($this->saldo_pendiente * ($this->tasa_mora_diaria / 100) * $dias, 2);
    }

    public function estaEnMora(): bool
    {
        if (!$this->aplicar_mora) return false;

        return $this->estado === 'en_mora' ||
               ($this->estado !== 'pagada' && now()->gt($this->fecha_limite_pago->addDays($this->dias_gracia)));
    }

    /**
     * Anula la factura y reversa (marca como anulado, sin borrar, para
     * mantener el rastro de auditoría) todos los asientos contables
     * ligados a ella: la factura misma, la mora acumulada, la provisión
     * de cartera, los pagos que se le hayan registrado, y si ya estaba
     * liquidada al propietario, también esa liquidación y su giro — de lo
     * contrario quedaría un giro pagado sobre una factura que ya no existe.
     */
    public function anularConReversion(string $motivo, ?int $usuarioId = null): void
    {
        \Illuminate\Support\Facades\DB::transaction(function () use ($motivo, $usuarioId) {
            $entriesFactura = \App\Models\AccountingEntry::where('referencia_id', $this->id)
                ->where(function ($q) {
                    $q->where('referencia_tipo', 'factura_rent_bill')
                      ->orWhere('referencia_tipo', 'like', 'mora_rent_bill%')
                      ->orWhere('referencia_tipo', 'like', 'provision_cartera_%');
                })
                ->where('estado', '!=', 'anulado')
                ->get();

            foreach ($entriesFactura as $entry) {
                $entry->anular("Factura {$this->numero} anulada: {$motivo}");
            }

            $pagoIds = $this->payments()->pluck('id');
            if ($pagoIds->isNotEmpty()) {
                $entriesPago = \App\Models\AccountingEntry::whereIn('referencia_id', $pagoIds)
                    ->where('referencia_tipo', 'pago_individual')
                    ->where('estado', '!=', 'anulado')
                    ->get();
                foreach ($entriesPago as $entry) {
                    $entry->anular("Factura {$this->numero} anulada: {$motivo}");
                }
            }

            if ($this->owner_liquidation_id && $this->liquidation && $this->liquidation->estado !== 'anulada') {
                $entryGiro = \App\Models\AccountingEntry::where('referencia_id', $this->liquidation->id)
                    ->where('referencia_tipo', 'giro_owner')
                    ->where('estado', '!=', 'anulado')
                    ->first();
                $entryGiro?->anular("Factura {$this->numero} anulada: {$motivo}");

                $this->liquidation->update([
                    'estado' => 'anulada',
                    'notas'  => trim(($this->liquidation->notas ? $this->liquidation->notas . ' — ' : ''))
                        . "Anulada automáticamente al anular la factura {$this->numero}: {$motivo}",
                ]);
            }

            $this->update([
                'estado'            => 'anulada',
                'motivo_anulacion'  => $motivo,
                'anulado_por_id'    => $usuarioId,
                'anulado_en'        => now(),
                'saldo_pendiente'   => 0,
                'aplicar_mora'      => false,
            ]);
        });
    }

    // ── Relaciones ───────────────────────────────────────
    public function rentalContract(): BelongsTo  { return $this->belongsTo(RentalContract::class); }
    public function property(): BelongsTo        { return $this->belongsTo(Property::class); }
    public function arrendatario(): BelongsTo    { return $this->belongsTo(Third::class, 'arrendatario_id'); }
    public function payments(): HasMany          { return $this->hasMany(RentPayment::class); }
    public function liquidation(): BelongsTo     { return $this->belongsTo(OwnerLiquidation::class, 'owner_liquidation_id'); }
    public function anuladoPor(): BelongsTo      { return $this->belongsTo(\App\Models\User::class, 'anulado_por_id'); }
    public function electronicInvoices(): HasMany { return $this->hasMany(ElectronicInvoice::class, 'rent_bill_id'); }
    public function electronicInvoice(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ElectronicInvoice::class, 'rent_bill_id')
            ->latestOfMany();
    }
}

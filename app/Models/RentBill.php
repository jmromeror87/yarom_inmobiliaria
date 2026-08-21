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

            if (!$b->aplicar_mora) {
                $capital = round((float) $b->total_factura + (float) $b->saldo_anterior_arrastrado - (float) $b->total_pagado, 2);
                $b->mora_acumulada    = 0;
                $b->dias_mora         = 0;
                $b->fecha_inicio_mora = null;
                $b->saldo_pendiente   = max(0, $capital);
                if ($b->estado === 'en_mora') {
                    $b->estado = ((float) $b->total_pagado > 0) ? 'parcial' : 'pendiente';
                }
                return;
            }

            $r = static::recalcularMoraDesdeHistorial($b);

            if ($r['dias_mora'] <= 0 || $r['mora'] <= 0) {
                $b->mora_acumulada    = 0;
                $b->dias_mora         = 0;
                $b->fecha_inicio_mora = null;
                $b->saldo_pendiente   = max(0, $r['capital']);
                if ($b->estado === 'en_mora') {
                    $b->estado = ((float) $b->total_pagado > 0) ? 'parcial' : 'pendiente';
                }
                return;
            }

            $b->dias_mora         = $r['dias_mora'];
            $b->mora_acumulada    = $r['mora'];
            $b->saldo_pendiente   = max(0, round($r['capital'] + $r['mora'], 2));
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

    /**
     * Calcula los componentes de la factura (canon, administración, seguro
     * SURA, retención, IVA, total) a partir de los datos VIGENTES del
     * contrato — única fórmula fuente, usada tanto por
     * GenerarFacturasMensuales (factura nueva) como por
     * sincronizarPendientesDesdeContrato (facturas ya generadas que aún no
     * se han pagado, cuando se corrige el contrato).
     */
    public static function componentesDesdeContrato(RentalContract $contrato): ?array
    {
        $canonBase = (float) $contrato->canon_mensual;
        $admin     = (float) ($contrato->cuota_administracion ?? 0);

        // Mismo blindaje que GenerarFacturasMensuales: un canon en $0 casi
        // siempre es un dato mal cargado, nunca se factura/resincroniza en silencio.
        if ($canonBase <= 0) return null;

        $tieneSeguroSura = (bool) ($contrato->property?->tiene_seguro_sura);
        $valorSeguroSura = $tieneSeguroSura ? (float) ($contrato->property?->valor_seguro_sura ?? 0) : 0;
        $ivaSeguroSura   = $tieneSeguroSura ? (float) ($contrato->property?->iva_seguro_sura ?? 0) : 0;

        $totalExacto    = $canonBase + $admin + $valorSeguroSura + $ivaSeguroSura;
        $canonInquilino = (float) ($contrato->property?->canon_cobrado_inquilino ?? 0);
        if ($tieneSeguroSura && $canonInquilino > $totalExacto) {
            $total          = $canonInquilino;
            $redondeoSeguro = round($canonInquilino - $totalExacto, 2);
        } else {
            $total          = $totalExacto;
            $redondeoSeguro = 0;
        }

        $retencion       = ContabilidadService::calcularRetencionArrendamiento($contrato, $canonBase);
        $ivaArriendo     = ContabilidadService::calcularIvaArrendamiento($contrato, $canonBase);
        $reteIvaArriendo = ContabilidadService::calcularReteIvaArrendamiento($contrato, $ivaArriendo);

        return [
            'canon_base'           => $canonBase,
            'cuota_administracion' => $admin,
            'valor_seguro_sura'    => $valorSeguroSura,
            'iva_seguro_sura'      => $ivaSeguroSura,
            'redondeo_seguro'      => $redondeoSeguro,
            'retencion_practicada' => $retencion,
            'iva_practicado'       => $ivaArriendo,
            'reteiva_practicada'   => $reteIvaArriendo,
            'total_factura'        => round($total + $ivaArriendo - $retencion - $reteIvaArriendo, 2),
        ];
    }

    /**
     * Al editar un contrato de arrendamiento (canon, cuota de
     * administración, etc.) se resincronizan TODAS las facturas del
     * inquilino que sigan pendientes, parciales o en mora — nunca las ya
     * pagadas ni las anuladas, esas son historia y solo se corrigen con un
     * ajuste explícito. Preserva lo ya abonado y la mora ya causada; solo
     * recalcula el capital de la factura con los valores nuevos del contrato.
     */
    public static function sincronizarPendientesDesdeContrato(RentalContract $contrato): void
    {
        $c = static::componentesDesdeContrato($contrato);
        if (!$c) {
            Log::warning("Contrato {$contrato->numero_contrato}: canon_mensual en \$0 tras editar — no se resincronizaron sus facturas pendientes.");
            return;
        }

        static::where('rental_contract_id', $contrato->id)
            ->whereNotIn('estado', ['pagada', 'anulada'])
            ->get()
            ->each(function (self $bill) use ($c) {
                $totalPagado  = (float) $bill->total_pagado;
                $nuevoCapital = max(0, round($c['total_factura'] + (float) $bill->saldo_anterior_arrastrado - $totalPagado, 2));

                $bill->fill($c);
                $bill->saldo_pendiente = round($nuevoCapital + (float) $bill->mora_acumulada, 2);
                $bill->estado = $bill->saldo_pendiente <= 0
                    ? 'pagada'
                    : ($totalPagado > 0 ? 'parcial' : ($bill->dias_mora > 0 ? 'en_mora' : 'pendiente'));
                $bill->save();
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

    /**
     * Cálculo canónico de mora: reproduce el historial completo de pagos en
     * orden cronológico aplicando "interés primero" — cada abono primero
     * salda el interés ya causado a esa fecha y solo el remanente reduce el
     * capital. Cuando un abono salda el interés por completo, el ancla de
     * cómputo ("desde cuándo corre el interés") se mueve a la fecha de ese
     * abono, así que el interés vuelve a $0 y solo empieza a correr de
     * nuevo sobre el capital YA reducido — nunca sobre el capital viejo.
     *
     * Si un abono NO alcanza a cubrir todo el interés causado, el capital
     * NO se toca (no se capitaliza interés / anatocismo) y el ancla no se
     * mueve: el interés sigue corriendo desde el mismo punto sobre el
     * mismo capital hasta que un abono sí alcance a cubrirlo.
     *
     * Es la ÚNICA función que debe calcular mora en todo el sistema —
     * RentPayment::created(), VerificarMoraJob y RentBill::updating() la
     * usan en vez de reimplementar la fórmula (evita que se desincronicen).
     *
     * @return array{capital: float, mora: float, dias_mora: int, ancla: ?\Carbon\Carbon}
     */
    public static function recalcularMoraDesdeHistorial(self $bill, ?\Carbon\Carbon $hasta = null): array
    {
        $hasta = ($hasta ?? now())->copy();
        $capital = round((float) $bill->total_factura + (float) $bill->saldo_anterior_arrastrado, 2);
        $ancla = $bill->fecha_limite_pago?->copy();

        if (!$bill->aplicar_mora || !$ancla) {
            $totalPagado = (float) $bill->payments()->where('fecha_pago', '<=', $hasta)->sum('total_pagado');
            return ['capital' => max(0, round($capital - $totalPagado, 2)), 'mora' => 0.0, 'dias_mora' => 0, 'ancla' => $ancla];
        }

        $finGracia = $ancla->copy()->addDays($bill->dias_gracia)->endOfDay();

        $baseParaMora = function (float $capital) use ($bill): float {
            if ($bill->rentalContract?->mora_solo_sobre_canon && $bill->canon_base > 0) {
                $proporcion = $bill->canon_base / max((float) $bill->total_factura, 1);
                return round($capital * $proporcion, 2);
            }
            return $capital;
        };

        $pagos = $bill->payments()
            ->where('fecha_pago', '<=', $hasta)
            ->orderBy('fecha_pago')->orderBy('id')->get();

        // Interés ya cubierto por abonos parciales que no alcanzaron a saldar
        // por completo el interés causado desde el ancla vigente — se le da
        // crédito a ese dinero (nunca se pierde), pero sin tocar capital ni
        // mover el ancla hasta que el interés quede saldado por completo.
        $interesPagadoAcumulado = 0.0;

        foreach ($pagos as $pago) {
            $fechaPago = \Carbon\Carbon::parse($pago->fecha_pago);

            $interesCausado = 0.0;
            if ($capital > 0 && $fechaPago->gt($finGracia)) {
                $diasMora = $ancla->copy()->startOfDay()->diffInDays($fechaPago->copy()->startOfDay());
                $interesCausado = round($baseParaMora($capital) * ($bill->tasa_mora_diaria / 100) * $diasMora, 2);
            }

            $interesNetoAdeudado = max(0, round($interesCausado - $interesPagadoAcumulado, 2));
            $montoPago = (float) $pago->total_pagado;
            $interesPagadoEnEstePago = min($montoPago, $interesNetoAdeudado);
            $interesPagadoAcumulado = round($interesPagadoAcumulado + $interesPagadoEnEstePago, 2);
            $remanente = round($montoPago - $interesPagadoEnEstePago, 2);

            if ($fechaPago->lte($finGracia)) {
                // Pago hecho DENTRO del período de gracia: todavía no existía
                // mora que saldar, así que el ancla NUNCA se mueve por esto —
                // si la mora llega a activarse más adelante, se sigue contando
                // completa desde la fecha límite ORIGINAL (regla de negocio
                // confirmada: el período de gracia no recorre el ancla, solo
                // pospone cuándo empieza a cobrar). Solo se reduce el capital.
                $capital = max(0, round($capital - $remanente, 2));
            } elseif ($interesPagadoAcumulado >= $interesCausado - 0.01) {
                // Ya en mora, y este pago (solo o sumado a los anteriores)
                // saldó el interés causado por completo: ahí sí se mueve el
                // ancla a la fecha de este pago y se reduce el capital con el
                // remanente. Reinicia el acumulador porque el interés desde
                // el nuevo ancla arranca en $0.
                $capital = max(0, round($capital - $remanente, 2));
                $ancla = $fechaPago->copy();
                $interesPagadoAcumulado = 0.0;
            }
            // Si no alcanzó a cubrir el interés ya causado en mora, ni el
            // capital ni el ancla se tocan — el crédito parcial queda
            // guardado en $interesPagadoAcumulado para el próximo pago.
        }

        $diasMora = 0;
        $mora = 0.0;
        if ($capital > 0 && $hasta->gt($finGracia)) {
            $diasMora = $ancla->copy()->startOfDay()->diffInDays($hasta->copy()->startOfDay());
            $interesCausadoHoy = round($baseParaMora($capital) * ($bill->tasa_mora_diaria / 100) * $diasMora, 2);
            $mora = max(0, round($interesCausadoHoy - $interesPagadoAcumulado, 2));
        }

        return ['capital' => $capital, 'mora' => $mora, 'dias_mora' => $diasMora, 'ancla' => $ancla];
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

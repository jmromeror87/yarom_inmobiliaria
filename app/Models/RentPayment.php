<?php
namespace App\Models;

use App\Services\ContabilidadService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

class RentPayment extends Model
{
    protected $table = 'rent_payments';

    protected $fillable = [
        'numero','rent_bill_id','rental_contract_id','arrendatario_id','registrado_por',
        'valor_canon','valor_mora','valor_administracion','otros_valores','total_pagado',
        'forma_pago','fecha_pago','referencia_pago','banco_origen','bank_id','comprobante_path','notas',
    ];

    protected $casts = [
        'fecha_pago'   => 'date',
        'total_pagado' => 'decimal:2',
        'valor_canon'  => 'decimal:2',
        'valor_mora'   => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function ($p) {
            if (empty($p->numero)) {
                $year   = now()->year;
                $ultimo = static::whereYear('created_at', $year)->max('numero');
                $count  = $ultimo ? ((int)substr($ultimo, -4)) + 1 : 1;
                $p->numero = 'PAG-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
            }
        });

        // Al guardar pago → actualizar factura
        static::created(function (RentPayment $payment) {
            $bill = RentBill::find($payment->rent_bill_id);
            if (!$bill) return;

            $totalPagado = $bill->payments()->sum('total_pagado');

            // La mora se recalcula reproduciendo TODO el historial de pagos
            // con "interés primero" (RentBill::recalcularMoraDesdeHistorial):
            // cada abono salda el interés ya causado antes de tocar el
            // capital, así que después de un abono suficiente el interés
            // queda en $0 y solo vuelve a correr sobre el capital reducido
            // desde la fecha de ESE abono — nunca se re-cobra sobre el
            // capital nuevo el interés de días ya cubiertos.
            $r = RentBill::recalcularMoraDesdeHistorial($bill);
            $capital  = $r['capital'];
            $mora     = $r['mora'];
            $diasMora = $r['dias_mora'];

            $saldo  = max(0, round($capital + $mora, 2));
            $estado = $saldo <= 0 ? 'pagada' : ($totalPagado > 0 ? 'parcial' : $bill->estado);

            $bill->update([
                'total_pagado'    => $totalPagado,
                'mora_acumulada'  => $mora,
                'dias_mora'       => $diasMora,
                'saldo_pendiente' => $saldo,
                'estado'          => $estado,
                'fecha_pago'      => $estado === 'pagada' ? $payment->fecha_pago : null,
            ]);

            // Si pagada → generar liquidación al propietario
            if ($estado === 'pagada') {
                OwnerLiquidation::generarDesdeFact($bill);
            }

            // Blindaje contable: si la mora de este mes ya se había
            // contabilizado (por VerificarMoraJob) con el valor de ANTES de
            // este abono, el comprobante queda desalineado con la mora
            // recién recalculada — se anula y se vuelve a causar con el
            // valor correcto, misma guarda de idempotencia que usa el job.
            try {
                $tipoMoraMes = 'mora_rent_bill_' . now()->format('Ym');
                $entryMesActual = \App\Models\AccountingEntry::where('referencia_id', $bill->id)
                    ->where('referencia_tipo', $tipoMoraMes)
                    ->where('estado', '!=', 'anulado')
                    ->first();

                if ($entryMesActual) {
                    $moraYaCausada = (float) $entryMesActual->lines()->where('debito', '>', 0)->sum('debito');
                    if (round($moraYaCausada, 2) !== round($mora, 2)) {
                        $entryMesActual->anular("Recálculo automático de mora tras registrar pago {$payment->numero}");
                    }
                }

                if ($mora > 0) {
                    ContabilidadService::generarParaMora($bill, $mora);
                    ContabilidadService::generarProvisionCartera($bill);
                }
            } catch (\Throwable $e) {
                Log::warning("Contabilidad mora (recálculo tras pago) {$bill->numero}: " . $e->getMessage());
            }

            // Contabilización del pago individual (soporta pagos parciales)
            try {
                $bill->refresh();
                ContabilidadService::generarParaPagoFactura($bill, $payment);
            } catch (\Throwable $e) {
                Log::warning("Contabilidad pago {$payment->numero}: " . $e->getMessage());
            }
        });
    }

    public function bill(): BelongsTo            { return $this->belongsTo(RentBill::class, 'rent_bill_id'); }
    public function rentalContract(): BelongsTo  { return $this->belongsTo(RentalContract::class); }
    public function arrendatario(): BelongsTo    { return $this->belongsTo(Third::class, 'arrendatario_id'); }
    public function registradoPor(): BelongsTo   { return $this->belongsTo(User::class, 'registrado_por'); }
    public function bank(): BelongsTo            { return $this->belongsTo(Bank::class); }
}

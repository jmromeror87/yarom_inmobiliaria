<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RentPayment extends Model
{
    protected $table = 'rent_payments';

    protected $fillable = [
        'numero','rent_bill_id','rental_contract_id','arrendatario_id','registrado_por',
        'valor_canon','valor_mora','valor_administracion','otros_valores','total_pagado',
        'forma_pago','fecha_pago','referencia_pago','banco_origen','comprobante_path','notas',
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
            $saldo       = $bill->total_factura + $bill->mora_acumulada - $totalPagado;

            $estado = $saldo <= 0 ? 'pagada' : ($totalPagado > 0 ? 'parcial' : $bill->estado);

            $bill->update([
                'total_pagado'    => $totalPagado,
                'saldo_pendiente' => max(0, $saldo),
                'estado'          => $estado,
                'fecha_pago'      => $estado === 'pagada' ? $payment->fecha_pago : null,
            ]);

            // Si pagada → generar liquidación al propietario
            if ($estado === 'pagada') {
                OwnerLiquidation::generarDesdeFact($bill);
            }
        });
    }

    public function bill(): BelongsTo            { return $this->belongsTo(RentBill::class, 'rent_bill_id'); }
    public function rentalContract(): BelongsTo  { return $this->belongsTo(RentalContract::class); }
    public function arrendatario(): BelongsTo    { return $this->belongsTo(Third::class, 'arrendatario_id'); }
    public function registradoPor(): BelongsTo   { return $this->belongsTo(User::class, 'registrado_por'); }
}

<?php

namespace App\Observers;

use App\Models\RentBill;
use App\Services\ContabilidadService;
use App\Services\FacturacionElectronicaService;
use Illuminate\Support\Facades\Log;

class RentBillObserver
{
    public function created(RentBill $bill): void
    {
        // Contabilización automática
        ContabilidadService::generarParaFactura($bill);

        // Facturación electrónica automática (solo si FE activa y tipo_documento = factura_electronica)
        try {
            FacturacionElectronicaService::emitir($bill);
        } catch (\Throwable $e) {
            // No interrumpir el flujo si FE falla — queda en estado 'error' con reintento programado
            Log::error("FE Observer error bill#{$bill->id}: " . $e->getMessage());
        }
    }

    // El pago individual se contabiliza desde RentPayment::booted()
    // pasando el pago concreto — ver App\Models\RentPayment
}

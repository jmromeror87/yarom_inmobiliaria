<?php

namespace App\Observers;

use App\Models\RentBill;
use App\Services\ContabilidadService;

class RentBillObserver
{
    public function created(RentBill $bill): void
    {
        ContabilidadService::generarParaFactura($bill);
    }

    public function updated(RentBill $bill): void
    {
        if ($bill->isDirty('estado') && $bill->estado === 'pagada') {
            ContabilidadService::generarParaPagoFactura($bill);
        }
    }
}

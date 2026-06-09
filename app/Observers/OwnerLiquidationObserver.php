<?php

namespace App\Observers;

use App\Models\OwnerLiquidation;
use App\Services\ContabilidadService;

class OwnerLiquidationObserver
{
    public function created(OwnerLiquidation $liq): void
    {
        // Solo genera asiento si hay descuentos adicionales (comisión ya reconocida en factura)
        ContabilidadService::generarParaLiquidacion($liq);
    }

    public function updated(OwnerLiquidation $liq): void
    {
        if ($liq->wasChanged('estado') && $liq->estado === 'pagada') {
            ContabilidadService::generarParaGiro($liq);
        }
    }
}

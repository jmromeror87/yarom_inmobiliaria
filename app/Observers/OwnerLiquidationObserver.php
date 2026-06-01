<?php

namespace App\Observers;

use App\Models\OwnerLiquidation;
use App\Services\ContabilidadService;

class OwnerLiquidationObserver
{
    public function created(OwnerLiquidation $liq): void
    {
        ContabilidadService::generarParaLiquidacion($liq);
    }

    public function updated(OwnerLiquidation $liq): void
    {
        if ($liq->isDirty('estado') && $liq->estado === 'pagada') {
            ContabilidadService::generarParaGiro($liq);
        }
    }
}

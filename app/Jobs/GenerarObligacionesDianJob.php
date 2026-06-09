<?php

namespace App\Jobs;

use App\Services\DianObligationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerarObligacionesDianJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 1;
    public int $timeout = 120;

    public function handle(): void
    {
        $anioActual = now()->year;
        $anioAnterior = $anioActual - 1;

        // Genera períodos del año actual y del anterior (para las anuales que vencen en el siguiente)
        $creadosActual   = DianObligationService::generarPeriodosAnio($anioActual);
        $creadosAnterior = DianObligationService::generarPeriodosAnio($anioAnterior);

        $total = $creadosActual + $creadosAnterior;

        if ($total > 0) {
            Log::info("GenerarObligacionesDianJob: {$total} nuevos períodos DIAN creados ({$creadosActual} año actual, {$creadosAnterior} año anterior)");
        }
    }
}

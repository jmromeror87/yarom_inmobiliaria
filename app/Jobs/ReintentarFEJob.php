<?php

namespace App\Jobs;

use App\Models\ElectronicInvoice;
use App\Services\FacturacionElectronicaService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ReintentarFEJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 1;
    public int $timeout = 60;

    public function handle(): void
    {
        $pendientes = ElectronicInvoice::pendientesReintento()->with('rentBill')->get();

        if ($pendientes->isEmpty()) return;

        Log::info("ReintentarFEJob: procesando {$pendientes->count()} facturas electrónicas pendientes");

        foreach ($pendientes as $fe) {
            try {
                FacturacionElectronicaService::reintentar($fe);
            } catch (\Throwable $e) {
                Log::error("ReintentarFEJob fe#{$fe->id}: " . $e->getMessage());
            }
        }
    }
}

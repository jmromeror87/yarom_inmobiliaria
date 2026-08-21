<?php

namespace App\Jobs;

use App\Jobs\Concerns\LogsExecution;
use App\Services\SeguimientoDiarioService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Guarda la planilla del día (inquilinos en mora / propietarios pendientes
 * de girar) aunque nadie entre a la página "Seguimiento Diario" — así el
 * historial por fecha siempre existe para poder navegar hacia atrás.
 */
class SnapshotSeguimientoDiarioJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, LogsExecution;

    public function handle(): void
    {
        $this->iniciarLog('Snapshot Seguimiento Diario');

        $hoy = now()->toDateString();

        $inquilinos   = SeguimientoDiarioService::calcularInquilinos();
        $propietarios = SeguimientoDiarioService::calcularPropietarios();

        SeguimientoDiarioService::sincronizar('inquilino', $inquilinos, $hoy);
        SeguimientoDiarioService::sincronizar('propietario', $propietarios, $hoy);

        $this->finalizarLog(count($inquilinos) + count($propietarios), [
            'inquilinos'   => count($inquilinos),
            'propietarios' => count($propietarios),
            'fecha'        => $hoy,
        ]);
    }
}

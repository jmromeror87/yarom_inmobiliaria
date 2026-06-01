<?php

namespace App\Jobs;

use App\Jobs\Concerns\LogsExecution;
use App\Models\OwnerLiquidation;
use App\Models\RentBill;
use App\Models\RentalContract;
use App\Models\User;
use App\Notifications\ContratosVencimientoNotification;
use App\Notifications\FacturasVencidasNotification;
use App\Notifications\LiquidacionesPendientesNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class EnviarNotificacionesInternasJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, LogsExecution;

    public int $tries = 3;

    public function handle(): void
    {
        $this->iniciarLog('Notificaciones Internas');

        // Destinatarios: admins y super_admins
        $admins = User::role(['super_admin', 'admin'])->get();
        if ($admins->isEmpty()) {
            $this->finalizarLog(0, ['mensaje' => 'Sin usuarios admin']);
            return;
        }

        $enviadas = 0;

        // ── 1. Facturas en mora ─────────────────────────────────────
        $facturasMora = RentBill::whereIn('estado', ['en_mora', 'vencida'])
            ->selectRaw('COUNT(*) as total, SUM(saldo_pendiente + mora_acumulada) as cartera, MAX(dias_mora) as max_mora')
            ->first();

        if ($facturasMora && $facturasMora->total > 0) {
            // Borrar notificaciones del mismo tipo del día de hoy para no duplicar
            $this->limpiarNotificacionesHoy($admins, 'facturas_mora');

            $notif = new FacturasVencidasNotification(
                totalFacturas: (int) $facturasMora->total,
                totalCartera:  (float) $facturasMora->cartera,
                diasMaxMora:   (int) $facturasMora->max_mora,
            );

            foreach ($admins as $admin) {
                $admin->notify($notif);
                $enviadas++;
            }
            Log::info("Notificación mora: {$facturasMora->total} facturas → {$admins->count()} admins");
        }

        // ── 2. Contratos próximos a vencer ──────────────────────────
        $diasAlerta = [5, 15, 30, 60];
        $resumen    = [];
        $totalContratos = 0;

        foreach ($diasAlerta as $dias) {
            $fecha = now()->addDays($dias)->toDateString();
            $count = RentalContract::where('estado', 'activo')
                ->whereDate('fecha_fin', $fecha)
                ->count();
            if ($count > 0) {
                $resumen[(string) $dias] = $count;
                $totalContratos += $count;
            }
        }

        if ($totalContratos > 0) {
            $this->limpiarNotificacionesHoy($admins, 'contratos_vencimiento');

            $notif = new ContratosVencimientoNotification(
                resumen: $resumen,
                total:   $totalContratos,
            );

            foreach ($admins as $admin) {
                $admin->notify($notif);
                $enviadas++;
            }
            Log::info("Notificación vencimiento: {$totalContratos} contratos → {$admins->count()} admins");
        }

        // ── 3. Liquidaciones pendientes de aprobar ──────────────────
        $liquidaciones = OwnerLiquidation::whereIn('estado', ['pendiente', 'aprobada'])
            ->selectRaw('COUNT(*) as total, SUM(total_giro) as valor')
            ->first();

        if ($liquidaciones && $liquidaciones->total > 0) {
            $this->limpiarNotificacionesHoy($admins, 'liquidaciones_pendientes');

            $notif = new LiquidacionesPendientesNotification(
                totalPendientes: (int) $liquidaciones->total,
                totalValor:      (float) $liquidaciones->valor,
            );

            foreach ($admins as $admin) {
                $admin->notify($notif);
                $enviadas++;
            }
            Log::info("Notificación liquidaciones: {$liquidaciones->total} pendientes → {$admins->count()} admins");
        }

        $this->finalizarLog($enviadas, [
            'admins_notificados' => $admins->count(),
            'facturas_mora'      => $facturasMora?->total ?? 0,
            'contratos_vencer'   => $totalContratos,
            'liquidaciones'      => $liquidaciones?->total ?? 0,
        ]);
    }

    private function limpiarNotificacionesHoy($admins, string $tipo): void
    {
        foreach ($admins as $admin) {
            $admin->notifications()
                ->whereDate('created_at', now()->toDateString())
                ->whereJsonContains('data->tipo', $tipo)
                ->delete();
        }
    }
}

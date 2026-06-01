<?php

use App\Jobs\AlertasDocumentosJob;
use App\Jobs\AlertasVencimientoContratosJob;
use App\Jobs\EnviarNotificacionesInternasJob;
use App\Jobs\GenerarFacturasMensuales;
use App\Jobs\RenovarContratosJob;
use App\Jobs\VerificarMoraJob;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(\Illuminate\Foundation\Inspiring::quote());
})->purpose('Display an inspiring quote');

// ── 1. Generar facturas — día 1 de cada mes a las 7am ───────────────────
Schedule::job(new GenerarFacturasMensuales)
    ->monthlyOn(1, '07:00')
    ->name('generar-facturas-mensuales')
    ->withoutOverlapping()
    ->onFailure(fn () => \Illuminate\Support\Facades\Log::error('Job GenerarFacturasMensuales falló'));

// ── 2. Verificar mora — diario a las 8am ────────────────────────────────
Schedule::job(new VerificarMoraJob)
    ->dailyAt('08:00')
    ->name('verificar-mora')
    ->withoutOverlapping()
    ->onFailure(fn () => \Illuminate\Support\Facades\Log::error('Job VerificarMoraJob falló'));

// ── 3. Alertas de vencimiento de contratos — diario a las 9am ───────────
// Envía WhatsApp a arrendatarios y asesores 60/30/15/5 días antes de vencer
Schedule::job(new AlertasVencimientoContratosJob)
    ->dailyAt('09:00')
    ->name('alertas-vencimiento-contratos')
    ->withoutOverlapping()
    ->onFailure(fn () => \Illuminate\Support\Facades\Log::error('Job AlertasVencimientoContratosJob falló'));

// ── 4. Renovación automática — día 1 de cada mes a las 6am ──────────────
// Renueva contratos vencidos con incremento IPC o % fijo y notifica
Schedule::job(new RenovarContratosJob)
    ->monthlyOn(1, '06:00')
    ->name('renovar-contratos')
    ->withoutOverlapping()
    ->onFailure(fn () => \Illuminate\Support\Facades\Log::error('Job RenovarContratosJob falló'));

// ── 5. Notificaciones internas — diario a las 7:30am ────────────────────
// Genera alertas en el topbar de Filament para admins
Schedule::job(new EnviarNotificacionesInternasJob)
    ->dailyAt('07:30')
    ->name('notificaciones-internas')
    ->withoutOverlapping()
    ->onFailure(fn () => \Illuminate\Support\Facades\Log::error('Job EnviarNotificacionesInternasJob falló'));

// ── 6. Alertas de documentos — lunes a las 8am ──────────────────────────
// Alerta al equipo cuando documentos de propiedades están próximos a vencer
Schedule::job(new AlertasDocumentosJob)
    ->weeklyOn(1, '08:30')
    ->name('alertas-documentos')
    ->withoutOverlapping()
    ->onFailure(fn () => \Illuminate\Support\Facades\Log::error('Job AlertasDocumentosJob falló'));

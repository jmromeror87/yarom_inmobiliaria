<?php

namespace App\Filament\Pages;

use App\Jobs\AlertasDocumentosJob;
use App\Jobs\AlertasVencimientoContratosJob;
use App\Jobs\EnviarNotificacionesInternasJob;
use App\Jobs\GenerarFacturasMensuales;
use App\Jobs\RenovarContratosJob;
use App\Jobs\VerificarMoraJob;
use App\Models\JobExecution;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class MonitorJobs extends Page
{
    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-cpu-chip';
    protected static ?string                 $navigationLabel = 'Tareas Automáticas';
    protected static string|\UnitEnum|null   $navigationGroup = 'Sistema';
    protected static ?int                    $navigationSort  = 1;
    protected static ?string                 $title           = 'Monitor de Tareas Automáticas';
    protected string                         $view            = 'filament.pages.monitor-jobs';

    // Intervalo de auto-refresh en segundos (Livewire polling)
    public int $refreshInterval = 30;

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'admin']) ?? false;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('ejecutar_facturas')
                ->label('Generar Facturas')
                ->icon('heroicon-o-document-text')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Ejecutar: Generar Facturas Mensuales')
                ->modalDescription('Esto generará facturas para todos los contratos activos del mes actual. ¿Continuar?')
                ->action(fn () => $this->ejecutarJob(GenerarFacturasMensuales::class, 'Generar Facturas Mensuales'))
                ->visible(fn () => auth()->user()?->hasRole('super_admin')),

            Action::make('ejecutar_mora')
                ->label('Verificar Mora')
                ->icon('heroicon-o-exclamation-triangle')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Ejecutar: Verificar Mora')
                ->modalDescription('Calculará mora actualizada para todas las facturas vencidas. ¿Continuar?')
                ->action(fn () => $this->ejecutarJob(VerificarMoraJob::class, 'Verificar Mora'))
                ->visible(fn () => auth()->user()?->hasRole('super_admin')),

            Action::make('ejecutar_alertas')
                ->label('Alertas Vencimiento')
                ->icon('heroicon-o-bell-alert')
                ->color('info')
                ->requiresConfirmation()
                ->modalHeading('Ejecutar: Alertas de Vencimiento')
                ->modalDescription('Enviará WhatsApp a contratos próximos a vencer (60/30/15/5 días). ¿Continuar?')
                ->action(fn () => $this->ejecutarJob(AlertasVencimientoContratosJob::class, 'Alertas Vencimiento Contratos'))
                ->visible(fn () => auth()->user()?->hasRole('super_admin')),

            Action::make('ejecutar_documentos')
                ->label('Alertas Documentos')
                ->icon('heroicon-o-folder-open')
                ->color('gray')
                ->requiresConfirmation()
                ->modalHeading('Ejecutar: Alertas de Documentos')
                ->modalDescription('Alertará al equipo sobre documentos de propiedades próximos a vencer. ¿Continuar?')
                ->action(fn () => $this->ejecutarJob(AlertasDocumentosJob::class, 'Alertas Documentos por Vencer'))
                ->visible(fn () => auth()->user()?->hasRole('super_admin')),
        ];
    }

    private function ejecutarJob(string $jobClass, string $nombre): void
    {
        try {
            dispatch_sync(new $jobClass);
            Notification::make()
                ->title("✅ {$nombre} ejecutado correctamente")
                ->success()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title("❌ Error al ejecutar {$nombre}")
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function getJobsData(): Collection
    {
        $jobs = [
            [
                'nombre'      => 'Generar Facturas Mensuales',
                'clase'       => GenerarFacturasMensuales::class,
                'descripcion' => 'Genera facturas del mes para contratos activos y envía WhatsApp con link de pago.',
                'frecuencia'  => 'Día 1 de cada mes · 7:00 am',
                'icono'       => '📄',
            ],
            [
                'nombre'      => 'Verificar Mora',
                'clase'       => VerificarMoraJob::class,
                'descripcion' => 'Calcula mora diaria en facturas vencidas y notifica arrendatarios en mora.',
                'frecuencia'  => 'Diario · 8:00 am',
                'icono'       => '⚠️',
            ],
            [
                'nombre'      => 'Alertas Vencimiento Contratos',
                'clase'       => AlertasVencimientoContratosJob::class,
                'descripcion' => 'Envía WhatsApp a arrendatarios y asesores 60/30/15/5 días antes del vencimiento.',
                'frecuencia'  => 'Diario · 9:00 am',
                'icono'       => '🔔',
            ],
            [
                'nombre'      => 'Renovar Contratos Automáticos',
                'clase'       => RenovarContratosJob::class,
                'descripcion' => 'Renueva contratos con incremento IPC o % fijo y notifica al arrendatario.',
                'frecuencia'  => 'Día 1 de cada mes · 6:00 am',
                'icono'       => '🔄',
            ],
            [
                'nombre'      => 'Alertas Documentos por Vencer',
                'clase'       => AlertasDocumentosJob::class,
                'descripcion' => 'Alerta al equipo sobre documentos de propiedades que vencen en los próximos 30 días.',
                'frecuencia'  => 'Lunes · 8:30 am',
                'icono'       => '📁',
            ],
            [
                'nombre'      => 'Notificaciones Internas',
                'clase'       => EnviarNotificacionesInternasJob::class,
                'descripcion' => 'Genera alertas en el panel para admins: facturas en mora, contratos por vencer y liquidaciones pendientes.',
                'frecuencia'  => 'Diario · 7:30 am',
                'icono'       => '🔔',
            ],
        ];

        return collect($jobs)->map(function (array $job) {
            $ultima = JobExecution::ultimoPorJob($job['clase']);
            $job['ultima_ejecucion'] = $ultima;
            return $job;
        });
    }

    public function getHistorialReciente(): Collection
    {
        return JobExecution::latest('started_at')
            ->limit(50)
            ->get();
    }
}

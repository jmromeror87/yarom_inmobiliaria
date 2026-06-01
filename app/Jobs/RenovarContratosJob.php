<?php

namespace App\Jobs;

use App\Jobs\Concerns\LogsExecution;
use App\Models\RentalContract;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Ejecutar el día 1 de cada mes.
 * Busca contratos vencidos ayer o hoy que tengan renovación automática
 * y genera la renovación con el incremento configurado (IPC o % fijo).
 */
class RenovarContratosJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, LogsExecution;

    public int $tries = 3;

    // IPC vigente Colombia — actualizar manualmente cuando el DANE lo publique
    // o conectar con API del DANE si se requiere automatización total
    public const IPC_ANUAL = 5.49; // % — IPC 12 meses a dic 2024

    public function handle(): void
    {
        $this->iniciarLog('Renovar Contratos Automáticos');

        $wap     = app(WhatsAppService::class);
        $empresa = \App\Models\Company::first()?->razon_social ?? 'Serviarrendar S.A.S';

        $contratos = RentalContract::where('estado', 'activo')
            ->whereIn('tipo_incremento', ['ipc', 'porcentaje_fijo'])
            ->whereDate('fecha_fin', '<=', now()->toDateString())
            ->with(['arrendatario', 'asesor', 'property'])
            ->get();

        $renovados = 0;

        foreach ($contratos as $contrato) {
            try {
                $this->renovar($contrato, $wap, $empresa);
                $renovados++;
            } catch (\Throwable $e) {
                Log::error("Error renovando contrato {$contrato->numero_contrato}: " . $e->getMessage());
            }
        }

        $this->finalizarLog($renovados, [
            'contratos_revisados' => $contratos->count(),
            'contratos_renovados' => $renovados,
            'ipc_aplicado'        => self::IPC_ANUAL . '%',
        ]);
    }

    private function renovar(RentalContract $contrato, WhatsAppService $wap, string $empresa): void
    {
        // Calcular nuevo canon según tipo de incremento
        $canonActual = (float) $contrato->canon_mensual;
        $pct         = $contrato->tipo_incremento === 'ipc'
            ? self::IPC_ANUAL
            : (float) $contrato->porcentaje_incremento;

        $canonNuevo  = round($canonActual * (1 + $pct / 100), -2); // Redondear a centenas
        $duracion    = (int) ($contrato->duracion_meses ?? 12);

        // Nueva vigencia
        $nuevaInicio = $contrato->fecha_fin->addDay();
        $nuevaFin    = $nuevaInicio->copy()->addMonths($duracion)->subDay();

        // Actualizar el mismo contrato (renovación en sitio)
        $contrato->update([
            'canon_mensual' => $canonNuevo,
            'fecha_inicio'  => $nuevaInicio->toDateString(),
            'fecha_fin'     => $nuevaFin->toDateString(),
        ]);

        Log::info("Contrato renovado: {$contrato->numero_contrato} | Canon {$canonActual} → {$canonNuevo} | IPC/Pct: {$pct}%");

        // Notificar al arrendatario
        $this->notificarRenovacion($wap, $contrato, $canonActual, $canonNuevo, $pct, $nuevaFin, $empresa);
    }

    private function notificarRenovacion(
        WhatsAppService $wap,
        RentalContract $contrato,
        float $canonAnterior,
        float $canonNuevo,
        float $pct,
        $nuevaFin,
        string $empresa
    ): void {
        $arrendatario = $contrato->arrendatario;
        $celular      = $arrendatario?->celular ?? $arrendatario?->celular_alt;
        if (!$celular) return;

        $nombre   = $arrendatario->nombre_completo ?? $arrendatario->razon_social;
        $inmueble = $contrato->property?->direccion ?? $contrato->property?->codigo;
        $tipoPct  = $contrato->tipo_incremento === 'ipc' ? 'IPC' : 'incremento pactado';

        $msg = "🔄 *Renovación de contrato*\n\n"
            . "Estimado(a) {$nombre},\n\n"
            . "Su contrato *{$contrato->numero_contrato}* ha sido renovado automáticamente.\n\n"
            . "📍 Inmueble: {$inmueble}\n"
            . "💰 Canon anterior: \$" . number_format($canonAnterior, 0, ',', '.') . "\n"
            . "💰 Canon nuevo ({$tipoPct} {$pct}%): *\$" . number_format($canonNuevo, 0, ',', '.') . "*\n"
            . "📅 Nueva vigencia hasta: {$nuevaFin->format('d/m/Y')}\n\n"
            . "El incremento se aplica conforme a la ley y lo pactado en su contrato.\n"
            . "Cualquier inquietud, comuníquese con nosotros.\n\n"
            . "— {$empresa}";

        try {
            $wap->enviar($celular, $msg);
        } catch (\Throwable $e) {
            Log::warning("WhatsApp renovación {$contrato->numero_contrato}: " . $e->getMessage());
        }

        // También notificar al asesor
        $asesor  = $contrato->asesor;
        $celularAsesor = $asesor?->celular_personal ?? $asesor?->phone;
        if ($celularAsesor) {
            try {
                $wap->enviar($celularAsesor,
                    "✅ *Contrato renovado automáticamente*\n\n"
                    . "Contrato: *{$contrato->numero_contrato}*\n"
                    . "Arrendatario: {$nombre}\n"
                    . "Canon nuevo: *\$" . number_format($canonNuevo, 0, ',', '.') . "*/mes\n"
                    . "Nueva vigencia: hasta {$nuevaFin->format('d/m/Y')}\n\n"
                    . "— {$empresa}"
                );
            } catch (\Throwable $e) {
                Log::warning("WhatsApp asesor renovación: " . $e->getMessage());
            }
        }
    }
}

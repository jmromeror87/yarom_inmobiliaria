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

class AlertasVencimientoContratosJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, LogsExecution;

    public int $tries = 3;

    public function handle(): void
    {
        $this->iniciarLog('Alertas Vencimiento Contratos');

        $wap     = app(WhatsAppService::class);
        $empresa = \App\Models\Company::first()?->razon_social ?? 'Serviarrendar S.A.S';
        $hoy     = now()->toDateString();

        $diasAlerta = [60, 30, 15, 5];
        $totalAlertas = 0;
        $resumen = [];

        foreach ($diasAlerta as $dias) {
            $fechaObjetivo = now()->addDays($dias)->toDateString();

            $contratos = RentalContract::where('estado', 'activo')
                ->whereDate('fecha_fin', $fechaObjetivo)
                ->with(['arrendatario', 'asesor', 'property'])
                ->get();

            foreach ($contratos as $contrato) {
                $this->notificarArrendatario($wap, $contrato, $dias, $empresa);
                $this->notificarAsesor($wap, $contrato, $dias, $empresa);
                Log::info("Alerta vencimiento contrato {$contrato->numero_contrato}: {$dias} días");
                $totalAlertas++;
            }

            if ($contratos->count() > 0) {
                $resumen["{$dias}_dias"] = $contratos->count();
            }
        }

        $this->finalizarLog($totalAlertas, array_merge(['fecha' => $hoy], $resumen));
    }

    private function notificarArrendatario(WhatsAppService $wap, RentalContract $contrato, int $dias, string $empresa): void
    {
        $arrendatario = $contrato->arrendatario;
        $celular      = $arrendatario?->celular ?? $arrendatario?->celular_alt;
        if (!$celular) return;

        $icono = match (true) {
            $dias <= 5  => '🔴',
            $dias <= 15 => '🟠',
            $dias <= 30 => '🟡',
            default     => '📋',
        };

        $nombre  = $arrendatario->nombre_completo ?? $arrendatario->razon_social;
        $canon   = '$' . number_format($contrato->canon_mensual, 0, ',', '.');
        $vence   = $contrato->fecha_fin->format('d/m/Y');
        $inmueble = $contrato->property?->direccion ?? $contrato->property?->codigo;

        $msg = "{$icono} *Aviso de vencimiento de contrato*\n\n"
            . "Estimado(a) {$nombre},\n\n"
            . "Le informamos que su contrato de arrendamiento *{$contrato->numero_contrato}* vence en *{$dias} días* ({$vence}).\n\n"
            . "📍 Inmueble: {$inmueble}\n"
            . "💰 Canon: {$canon}/mes\n\n"
            . ($dias <= 30
                ? "Por favor comuníquese con nosotros para gestionar la *renovación* o informar su decisión.\n\n"
                : "Le recomendamos revisar las condiciones de renovación con anticipación.\n\n")
            . "— {$empresa}";

        try {
            $wap->enviar($celular, $msg);
        } catch (\Throwable $e) {
            Log::warning("WhatsApp arrendatario {$contrato->numero_contrato}: " . $e->getMessage());
        }
    }

    private function notificarAsesor(WhatsAppService $wap, RentalContract $contrato, int $dias, string $empresa): void
    {
        $asesor  = $contrato->asesor;
        $celular = $asesor?->celular_personal ?? $asesor?->phone;
        if (!$celular) return;

        $nombre    = $contrato->arrendatario?->nombre_completo ?? $contrato->arrendatario?->razon_social;
        $inmueble  = $contrato->property?->codigo . ' — ' . $contrato->property?->direccion;
        $vence     = $contrato->fecha_fin->format('d/m/Y');

        $msg = "⏰ *Contrato próximo a vencer — {$dias} días*\n\n"
            . "Contrato: *{$contrato->numero_contrato}*\n"
            . "Arrendatario: {$nombre}\n"
            . "Inmueble: {$inmueble}\n"
            . "Vence: *{$vence}*\n\n"
            . "Gestiona la renovación o terminación desde el sistema.\n"
            . "— {$empresa}";

        try {
            $wap->enviar($celular, $msg);
        } catch (\Throwable $e) {
            Log::warning("WhatsApp asesor alerta {$contrato->numero_contrato}: " . $e->getMessage());
        }
    }
}

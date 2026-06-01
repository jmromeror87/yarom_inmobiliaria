<?php

namespace App\Jobs;

use App\Jobs\Concerns\LogsExecution;
use App\Models\Property;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Ejecutar los lunes a las 8am.
 * Alerta al equipo cuando documentos clave de inmuebles están próximos a vencer.
 * Documentos con vencimiento: predial (anual), paz y salvo, certificado de libertad.
 */
class AlertasDocumentosJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, LogsExecution;

    public int $tries = 3;

    // Tipos de documentos que tienen vencimiento anual
    private const DOCS_CON_VENCIMIENTO = [
        'predial'              => 'Impuesto predial',
        'paz_salvo_admin'      => 'Paz y salvo administración',
        'certificado_libertad' => 'Certificado de libertad y tradición',
    ];

    public function handle(): void
    {
        $this->iniciarLog('Alertas Documentos por Vencer');

        $wap     = app(WhatsAppService::class);
        $empresa = \App\Models\Company::first();
        $admins  = \App\Models\User::role(['super_admin', 'admin'])->get();

        // Alerta a propiedades con documentos próximos a vencer (30 días)
        $propiedades = Property::with(['documents' => fn ($q) =>
                $q->whereIn('tipo', array_keys(self::DOCS_CON_VENCIMIENTO))
                  ->whereNotNull('fecha_vencimiento')
                  ->whereDate('fecha_vencimiento', '<=', now()->addDays(30)->toDateString())
                  ->whereDate('fecha_vencimiento', '>=', now()->toDateString()),
            'propietario'
        ])
        ->get()
        ->filter(fn ($p) => $p->documents->isNotEmpty());

        if ($propiedades->isEmpty()) {
            $this->finalizarLog(0, ['propiedades_alertadas' => 0, 'mensaje' => 'Sin documentos próximos a vencer']);
            return;
        }

        foreach ($admins as $admin) {
            $celular = $admin->celular_personal ?? null;
            if (!$celular) continue;

            $lista = '';
            foreach ($propiedades as $prop) {
                foreach ($prop->documents as $doc) {
                    $diasRestantes = now()->diffInDays($doc->fecha_vencimiento, false);
                    $tipoLabel     = self::DOCS_CON_VENCIMIENTO[$doc->tipo] ?? $doc->tipo;
                    $lista .= "• {$prop->codigo} — {$tipoLabel}: vence en {$diasRestantes} días ({$doc->fecha_vencimiento->format('d/m/Y')})\n";
                }
            }

            $msg = "📁 *Documentos próximos a vencer*\n\n"
                . "Los siguientes documentos requieren atención:\n\n"
                . $lista
                . "\nRevisa y actualiza en el sistema.\n"
                . "— " . ($empresa?->razon_social ?? 'YarOM ERP');

            try {
                $wap->enviar($celular, $msg);
            } catch (\Throwable $e) {
                Log::warning("WhatsApp alertas docs admin: " . $e->getMessage());
            }
        }

        $total = $propiedades->sum(fn ($p) => $p->documents->count());
        Log::info("AlertasDocumentosJob: " . $propiedades->count() . " propiedades con documentos por vencer.");

        $this->finalizarLog($total, [
            'propiedades_alertadas' => $propiedades->count(),
            'documentos_por_vencer' => $total,
            'admins_notificados'    => $admins->count(),
        ]);
    }
}

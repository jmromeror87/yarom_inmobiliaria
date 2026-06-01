<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\RequestSuraStudy;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EstudioController extends Controller
{
    public function show(string $token)
    {
        $study = RequestSuraStudy::where('estudio_token', $token)
            ->with(['request.property', 'request.thirds.third', 'request.thirds.documents'])
            ->firstOrFail();

        $company = Company::first();

        if ($study->tokenUsed()) {
            return view('estudio.show', compact('study', 'company', 'token'))
                ->with('status', 'respondido');
        }

        return view('estudio.show', compact('study', 'company', 'token'))
            ->with('status', 'pendiente');
    }

    public function store(Request $http, string $token)
    {
        $study = RequestSuraStudy::where('estudio_token', $token)
            ->with(['request.asesor', 'request.property'])
            ->firstOrFail();

        if ($study->tokenUsed()) {
            return redirect()->route('estudio.show', $token)
                ->with('error', 'Esta solicitud ya fue respondida.');
        }

        $company = Company::first();

        $http->validate([
            'resultado_sura'       => 'required|in:aprobada,condicional,rechazada',
            'numero_solicitud_sura'=> 'nullable|string|max:30',
            'analista_sura'        => 'nullable|string|max:150',
            'observaciones_sura'   => 'nullable|string|max:2000',
            'documento'            => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $pathDoc = null;
        if ($http->hasFile('documento') && $http->file('documento')->isValid()) {
            $pathDoc = $http->file('documento')->store(
                'estudios/' . $study->request_id,
                'public'
            );
        }

        $study->update([
            'resultado_sura'        => $http->resultado_sura,
            'numero_solicitud_sura' => $http->numero_solicitud_sura,
            'analista_sura'         => $http->analista_sura,
            'observaciones_sura'    => $http->observaciones_sura,
            'fecha_respuesta'       => now(),
            'path_respuesta'        => $pathDoc,
            'estudio_token_used_at' => now(),
        ]);

        // Actualizar estado de la solicitud
        $estadoMap = [
            'aprobada'    => 'aprobada',
            'condicional' => 'condicional',
            'rechazada'   => 'rechazada',
        ];
        $study->request->update([
            'estado'           => $estadoMap[$http->resultado_sura],
            'fecha_decision'   => now()->toDateString(),
            'decidido_por'     => 'Sudamericana / ' . ($http->analista_sura ?? 'Asesor externo'),
            'concepto_evaluacion' => $http->observaciones_sura,
        ]);

        // Notificar al asesor interno por WhatsApp
        $this->notificarAsesor($study, $http->resultado_sura, $company);

        Log::info("Estudio Sudamericana respondido", [
            'study_id'  => $study->id,
            'request'   => $study->request->numero,
            'resultado' => $http->resultado_sura,
        ]);

        return redirect()->route('estudio.show', $token)
            ->with('success', 'Respuesta registrada correctamente. ¡Gracias!');
    }

    private function notificarAsesor(RequestSuraStudy $study, string $resultado, ?Company $company): void
    {
        $asesor = $study->request->asesor;
        if (!$asesor?->celular_personal && !$asesor?->phone) return;

        $telefono = $asesor->celular_personal ?? $asesor->phone;
        $iconos   = ['aprobada' => '✅', 'condicional' => '⚠️', 'rechazada' => '❌'];
        $icono    = $iconos[$resultado] ?? '📋';
        $empresa  = $company?->razon_social ?? 'Serviarrendar S.A.S';

        $msg = "{$icono} *Respuesta de Sudamericana*\n\n"
            . "Solicitud: *{$study->request->numero}*\n"
            . "Inmueble: {$study->request->property?->direccion}\n\n"
            . "Resultado: *" . strtoupper($resultado) . "*\n"
            . ($study->numero_solicitud_sura ? "Ref. Sura: {$study->numero_solicitud_sura}\n" : '')
            . ($study->analista_sura ? "Analista: {$study->analista_sura}\n" : '')
            . ($study->observaciones_sura ? "\nObservaciones:\n{$study->observaciones_sura}\n" : '')
            . "\n— {$empresa}";

        try {
            app(WhatsAppService::class)->enviar($telefono, $msg);
        } catch (\Throwable $e) {
            Log::warning("WhatsApp asesor falló: " . $e->getMessage());
        }
    }
}

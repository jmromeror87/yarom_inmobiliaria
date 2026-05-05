<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.whatsapp.url', 'http://localhost:3001');
    }

    public function isConnected(): bool
    {
        try {
            $res = Http::timeout(3)->get($this->baseUrl . '/status');
            return $res->json('ready', false);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getStatus(): array
    {
        try {
            $res = Http::timeout(3)->get($this->baseUrl . '/status');
            return $res->json();
        } catch (\Exception $e) {
            return ['ready' => false, 'qr' => null, 'error' => $e->getMessage()];
        }
    }

    public function enviar(string $telefono, string $mensaje): array
    {
        try {
            $res = Http::timeout(15)->post($this->baseUrl . '/send', [
                'telefono' => $telefono,
                'mensaje'  => $mensaje,
            ]);
            Log::info('WhatsApp enviado', ['tel' => $telefono, 'ok' => $res->json('ok')]);
            return $res->json();
        } catch (\Exception $e) {
            Log::error('WhatsApp error', ['error' => $e->getMessage()]);
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    public function enviarConArchivo(string $telefono, string $mensaje, string $rutaArchivo, string $nombreArchivo = ''): array
    {
        try {
            $res = Http::timeout(30)->post($this->baseUrl . '/send-doc', [
                'telefono'       => $telefono,
                'mensaje'        => $mensaje,
                'archivo_path'   => $rutaArchivo,
                'nombre_archivo' => $nombreArchivo,
            ]);
            return $res->json();
        } catch (\Exception $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }
}

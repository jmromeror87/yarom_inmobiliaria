<?php
namespace App\Helpers;

use App\Services\WhatsAppService;
use Filament\Notifications\Notification;

class WhatsApp
{
    public static function enviar(string $telefono, string $mensaje): bool
    {
        $service = app(WhatsAppService::class);

        if (!$service->isConnected()) {
            Notification::make()
                ->title('⚠️ WhatsApp no conectado')
                ->body('El servicio WhatsApp no está disponible. Use el enlace manual.')
                ->warning()->send();
            return false;
        }

        $resultado = $service->enviar($telefono, $mensaje);

        if ($resultado['ok'] ?? false) {
            Notification::make()
                ->title('📱 Mensaje enviado por WhatsApp')
                ->success()->send();
            return true;
        }

        Notification::make()
            ->title('❌ Error enviando WhatsApp')
            ->body($resultado['error'] ?? 'Error desconocido')
            ->danger()->send();
        return false;
    }

    public static function urlFallback(string $telefono, string $mensaje): string
    {
        $numero = preg_replace('/[^0-9]/', '', $telefono);
        if (!str_starts_with($numero, '57')) $numero = '57' . $numero;
        return 'https://wa.me/' . $numero . '?text=' . urlencode($mensaje);
    }
}

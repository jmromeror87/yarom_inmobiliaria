<?php

namespace App\Filament\Pages;

use App\Services\WhatsAppService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class WhatsAppStatus extends Page
{
    protected string $view = 'filament.pages.whatsapp-status';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-left-ellipsis';
    protected static ?int $navigationSort = 4;

    public static function getNavigationLabel(): string { return 'WhatsApp'; }
    public static function getNavigationGroup(): ?string { return 'Configuración'; }
    public function getTitle(): string { return 'Estado WhatsApp — Serviarrendar'; }

    public array $wapStatus = [];

    public function mount(): void
    {
        $this->wapStatus = app(WhatsAppService::class)->getStatus();
    }

    public function poll(): void
    {
        $this->wapStatus = app(WhatsAppService::class)->getStatus();
    }

    public function refresh(): void
    {
        $this->wapStatus = app(WhatsAppService::class)->getStatus();
        Notification::make()->title('Estado actualizado')->success()->duration(2000)->send();
    }

    public function reiniciar(): void
    {
        $res = app(WhatsAppService::class)->reiniciar();
        $this->wapStatus = app(WhatsAppService::class)->getStatus();

        if ($res['ok'] ?? false) {
            Notification::make()->title('Sesión reiniciada — escanea el nuevo QR')->success()->send();
        } else {
            Notification::make()->title('No se pudo reiniciar la sesión')->body($res['error'] ?? '')->danger()->send();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refresh')
                ->label('Actualizar ahora')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action('refresh'),
        ];
    }
}

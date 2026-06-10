<?php

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Mail;

class EmailConfig extends Page
{
    protected string $view = 'filament.pages.email-config';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-envelope';
    protected static ?int $navigationSort = 5;

    public static function getNavigationLabel(): string { return 'Correo Electrónico'; }
    public static function getNavigationGroup(): ?string { return 'Configuración'; }
    public function getTitle(): string { return 'Configuración de Correo — Serviarrendar'; }

    public string $estado = 'verificando';
    public string $from_address = '';
    public string $from_name = '';
    public string $mailer = '';
    public string $resend_key_masked = '';

    public function mount(): void
    {
        $this->from_address     = config('mail.from.address', '');
        $this->from_name        = config('mail.from.name', '');
        $this->mailer           = config('mail.default', '');
        $key                    = config('resend.api_key', env('RESEND_API_KEY', ''));
        $this->resend_key_masked = $key ? substr($key, 0, 8) . '••••••••••••••••' . substr($key, -4) : '— no configurado —';
        $this->estado           = 'listo';
    }

    public function testEmail(array $data): void
    {
        $destino = $data['email_prueba'];

        try {
            Mail::raw(
                "✅ Correo de prueba desde YarOM Inmobiliaria\n\n" .
                "Si recibes este mensaje, el servicio de correo está funcionando correctamente.\n\n" .
                "Enviado: " . now()->format('d/m/Y H:i:s') . "\n" .
                "Servidor: Resend API\n" .
                "Desde: {$this->from_address}",
                function ($message) use ($destino) {
                    $message->to($destino)
                        ->subject('✅ Prueba de correo — YarOM Inmobiliaria');
                }
            );

            Notification::make()
                ->title('Correo enviado correctamente')
                ->body("Se envió un correo de prueba a {$destino}")
                ->success()
                ->send();

        } catch (\Throwable $e) {
            Notification::make()
                ->title('Error al enviar correo')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('test_email')
                ->label('Enviar correo de prueba')
                ->icon('heroicon-o-paper-airplane')
                ->extraAttributes([
                    'style' => 'background:linear-gradient(135deg,#1e3a8a,#E11D48)!important;color:#fff!important;border:none!important;box-shadow:0 4px 14px rgba(30,58,138,.3)!important;font-weight:700!important;',
                ])
                ->form([
                    TextInput::make('email_prueba')
                        ->label('Correo destino para prueba')
                        ->email()
                        ->default(auth()->user()->email ?? '')
                        ->required(),
                ])
                ->action(fn(array $data) => $this->testEmail($data)),
        ];
    }
}

<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Mail\InvitacionUsuario;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    // Contraseña temporal aleatoria (el usuario la cambiará al activar)
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['password']         = bcrypt(Str::random(32));
        $data['invitation_token'] = Str::random(64);
        $data['invitation_sent_at'] = now();
        return $data;
    }

    protected function afterCreate(): void
    {
        $user  = $this->record;
        $token = $user->invitation_token;

        try {
            Mail::to($user->email)->send(new InvitacionUsuario($user, $token));

            Notification::make()
                ->title('Usuario creado')
                ->body("Se envió la invitación a {$user->email}")
                ->success()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Usuario creado, pero el correo falló')
                ->body('Verifica la configuración de mail en .env. Puedes reenviar la invitación desde la tabla.')
                ->warning()
                ->send();
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

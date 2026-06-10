<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Illuminate\Validation\Rules\Password;

class EditProfile extends BaseEditProfile
{
    public static function isSimple(): bool
    {
        return false;
    }

    public function getView(): string
    {
        return 'filament.pages.auth.edit-profile';
    }

    public function form(Schema $form): Schema
    {
        return $form->components([

            Section::make('info_personal')
                ->heading('Información Personal')
                ->icon('heroicon-m-user')
                ->columns(2)
                ->schema([
                    TextInput::make('name')
                        ->label('Nombre completo')
                        ->required()
                        ->maxLength(255),

                    TextInput::make('email')
                        ->label('Correo electrónico')
                        ->email()
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255),
                ]),

            Section::make('cambiar_password')
                ->heading('Cambiar Contraseña')
                ->icon('heroicon-m-lock-closed')
                ->description('Deja en blanco si no deseas cambiarla.')
                ->columns(2)
                ->schema([
                    TextInput::make('current_password')
                        ->label('Contraseña actual')
                        ->password()
                        ->currentPassword()
                        ->dehydrated(false),

                    TextInput::make('password')
                        ->label('Nueva contraseña')
                        ->password()
                        ->rule(Password::default())
                        ->autocomplete('new-password')
                        ->dehydrated(fn ($state): bool => filled($state))
                        ->live(debounce: 500),

                    TextInput::make('password_confirmation')
                        ->label('Confirmar contraseña')
                        ->password()
                        ->same('password')
                        ->dehydrated(false),
                ]),
        ]);
    }
}

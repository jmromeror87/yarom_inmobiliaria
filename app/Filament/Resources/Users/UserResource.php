<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Mail\InvitacionUsuario;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class UserResource extends Resource
{
    protected static ?string              $model           = User::class;
    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-users';
    protected static ?string              $navigationLabel = 'Usuarios';
    protected static string|\UnitEnum|null $navigationGroup = 'Configuración';
    protected static ?int                 $navigationSort  = 1;

    public static function getNavigationGroup(): ?string { return 'Configuración'; }

    public static function canAccess(): bool
    {
        return auth()->user()?->can('ver_usuarios') ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Datos del usuario')->schema([
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
            ])->columns(2),

            Section::make('Rol y permisos')->schema([
                Select::make('roles')
                    ->label('Rol del usuario')
                    ->relationship('roles', 'name')
                    ->preload()
                    ->multiple()
                    ->options(Role::pluck('name', 'id')->map(fn ($name) => match ($name) {
                        'super_admin'  => '🛡️ Super Administrador',
                        'admin'        => '⚙️ Administrador',
                        'asesor'       => '🏠 Asesor',
                        'contador'     => '📊 Contador',
                        'solo_lectura' => '👁️ Solo lectura',
                        default        => '🔒 ' . $name,
                    }))
                    ->required()
                    ->helperText('Al crear el usuario se le enviará un correo para que active su cuenta y cree su propia contraseña.'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn ($record) => $record->email),

                TextColumn::make('roles.name')
                    ->label('Rol')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'super_admin'  => 'danger',
                        'admin'        => 'warning',
                        'asesor'       => 'success',
                        'contador'     => 'info',
                        'solo_lectura' => 'gray',
                        default        => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'super_admin'  => '🛡️ Super Admin',
                        'admin'        => '⚙️ Admin',
                        'asesor'       => '🏠 Asesor',
                        'contador'     => '📊 Contador',
                        'solo_lectura' => '👁️ Solo lectura',
                        default        => $state,
                    }),

                TextColumn::make('invitation_accepted_at')
                    ->label('Estado cuenta')
                    ->badge()
                    ->color(fn ($record) => $record->invitation_accepted_at
                        ? 'success'
                        : ($record->invitation_sent_at ? 'warning' : 'gray'))
                    ->formatStateUsing(fn ($state, $record) => match (true) {
                        $record->invitation_accepted_at !== null => '✅ Activo',
                        $record->invitation_sent_at !== null     => '📧 Invitación pendiente',
                        default                                   => '⚪ Sin invitar',
                    }),

                TextColumn::make('invitation_sent_at')
                    ->label('Invitación enviada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->recordActions([
                Action::make('reenviar_invitacion')
                    ->label('Reenviar invitación')
                    ->icon('heroicon-o-envelope')
                    ->color('info')
                    ->visible(fn ($record) => $record->invitation_accepted_at === null)
                    ->requiresConfirmation()
                    ->modalHeading('Reenviar invitación')
                    ->modalDescription(fn ($record) => "Se enviará un nuevo correo de invitación a {$record->email}.")
                    ->action(function ($record) {
                        $token = Str::random(64);
                        $record->update([
                            'invitation_token'   => $token,
                            'invitation_sent_at' => now(),
                        ]);
                        Mail::to($record->email)->send(new InvitacionUsuario($record, $token));
                        Notification::make()->title('Invitación reenviada')->success()->send();
                    }),

                EditAction::make()->label('Editar'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Eliminar'),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit'   => EditUser::route('/{record}/edit'),
        ];
    }
}

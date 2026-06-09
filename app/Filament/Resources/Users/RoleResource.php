<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateRole;
use App\Filament\Resources\Users\Pages\EditRole;
use App\Filament\Resources\Users\Pages\ListRoles;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleResource extends Resource
{
    protected static ?string              $model           = Role::class;
    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-shield-check';
    protected static ?string              $navigationLabel = 'Roles y Permisos';
    protected static string|\UnitEnum|null $navigationGroup = 'Configuración';
    protected static ?int                 $navigationSort  = 2;

    public static function getNavigationGroup(): ?string { return 'Configuración'; }
    protected static ?string              $modelLabel      = 'Rol';
    protected static ?string              $pluralModelLabel = 'Roles y Permisos';

    public static function canAccess(): bool
    {
        return auth()->user()?->can('asignar_roles') ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        $grupos = [
            'Portafolio'   => ['ver_inmuebles', 'crear_inmuebles', 'editar_inmuebles', 'eliminar_inmuebles'],
            'Contratos Arriendo' => ['ver_contratos_arriendo', 'crear_contratos_arriendo', 'editar_contratos_arriendo', 'eliminar_contratos_arriendo', 'firmar_contratos_arriendo'],
            'Contratos Administración' => ['ver_contratos_administracion', 'crear_contratos_administracion', 'editar_contratos_administracion', 'eliminar_contratos_administracion'],
            'Cartera / Facturas' => ['ver_facturas', 'crear_facturas', 'editar_facturas', 'eliminar_facturas', 'enviar_link_pago', 'ver_pagos'],
            'Liquidaciones' => ['ver_liquidaciones', 'crear_liquidaciones', 'aprobar_liquidaciones', 'registrar_giro_liquidaciones', 'eliminar_liquidaciones'],
            'Solicitudes / Estudios' => ['ver_solicitudes', 'crear_solicitudes', 'editar_solicitudes', 'eliminar_solicitudes', 'enviar_estudio_sudamericana'],
            'Actas de Entrega' => ['ver_actas', 'crear_actas', 'editar_actas', 'eliminar_actas', 'firmar_actas'],
            'Terceros' => ['ver_terceros', 'crear_terceros', 'editar_terceros', 'eliminar_terceros'],
            'Configuración Empresa' => ['ver_empresas', 'editar_empresas', 'ver_plantillas', 'crear_plantillas', 'editar_plantillas', 'eliminar_plantillas'],
            'Contabilidad' => ['ver_contabilidad', 'crear_asientos', 'contabilizar_asientos', 'anular_asientos'],
            'Reportes' => ['ver_reportes', 'descargar_reportes'],
            'Dashboard' => ['ver_dashboard'],
            'Usuarios y Roles' => ['ver_usuarios', 'crear_usuarios', 'editar_usuarios', 'eliminar_usuarios', 'asignar_roles'],
        ];

        $secciones = [];
        foreach ($grupos as $grupo => $permisos) {
            $opciones = Permission::whereIn('name', $permisos)
                ->pluck('name', 'id')
                ->map(fn ($p) => str_replace('_', ' ', ucfirst($p)))
                ->toArray();

            $secciones[] = Section::make($grupo)
                ->schema([
                    CheckboxList::make('permissions')
                        ->label('')
                        ->relationship('permissions', 'name')
                        ->options($opciones)
                        ->columns(2)
                        ->gridDirection('row'),
                ])
                ->collapsed(fn ($record) => $record !== null)
                ->collapsible();
        }

        return $schema->components([
            Section::make('Nombre del rol')->schema([
                TextInput::make('name')
                    ->label('Nombre del rol')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->helperText('Usa snake_case: ej. asesor_senior, contador_externo'),
            ]),
            ...$secciones,
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Rol')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'super_admin'  => '🛡️ Super Administrador',
                        'admin'        => '⚙️ Administrador',
                        'asesor'       => '🏠 Asesor',
                        'contador'     => '📊 Contador',
                        'solo_lectura' => '👁️ Solo lectura',
                        default        => '🔒 ' . $state,
                    })
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('permissions_count')
                    ->label('Permisos asignados')
                    ->counts('permissions')
                    ->badge()
                    ->color('info'),

                TextColumn::make('users_count')
                    ->label('Usuarios con este rol')
                    ->counts('users')
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'success' : 'gray'),

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->striped()
            ->recordActions([
                EditAction::make()->label('Editar permisos'),
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
            'index'  => ListRoles::route('/'),
            'create' => CreateRole::route('/create'),
            'edit'   => EditRole::route('/{record}/edit'),
        ];
    }
}

<?php

namespace App\Filament\Resources\PropertyServices;

use App\Filament\Resources\PropertyServices\Pages\CreatePropertyService;
use App\Filament\Resources\PropertyServices\Pages\EditPropertyService;
use App\Filament\Resources\PropertyServices\Pages\ListPropertyServices;
use App\Models\AccountingAccount;
use App\Models\Property;
use App\Models\PropertyService;
use App\Models\RentalContract;
use App\Models\Third;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use App\Services\WhatsAppService;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Mail;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PropertyServiceResource extends Resource
{
    protected static ?string $model = PropertyService::class;
    protected static ?string $modelLabel = 'Servicio';
    protected static ?string $pluralModelLabel = 'Servicios / Mantenimientos';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-wrench-screwdriver';
    protected static ?string $navigationLabel = 'Servicios';
    protected static string|\UnitEnum|null  $navigationGroup = 'Contratación';
    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Inmueble y proveedor')
                ->description('Seleccione el inmueble y el tercero que ejecuta el servicio')
                ->icon('heroicon-o-home')
                ->columns(2)
                ->schema([
                    Select::make('property_id')
                        ->label('Inmueble')
                        ->options(fn() => Property::orderBy('direccion')
                            ->get()
                            ->mapWithKeys(fn($p) => [$p->id => ($p->codigo ?? $p->id) . ' — ' . $p->direccion . ' ' . ($p->apto_casa_oficina ?? '')])
                        )
                        ->searchable()->required()
                        ->live()
                        ->afterStateUpdated(function ($state, $set) {
                            if ($state) {
                                $contrato = RentalContract::where('property_id', $state)
                                    ->whereIn('estado', ['activo', 'vigente'])
                                    ->latest()->first();
                                $set('rental_contract_id', $contrato?->id);
                            }
                        }),

                    Select::make('third_id')
                        ->label('Proveedor / Contratista')
                        ->options(fn() => Third::orderBy('nombre_completo')
                            ->get()
                            ->mapWithKeys(fn($t) => [$t->id => $t->nombre_completo . ' — ' . $t->numero_documento])
                        )
                        ->searchable()->required()
                        ->createOptionForm([
                            Section::make()
                                ->schema([
                                    \Filament\Schemas\Components\Html::make(new \Illuminate\Support\HtmlString(
                                        '<div style="background:linear-gradient(135deg,#0F172A,#0369a1);border-radius:14px;padding:16px 20px;
                                                     display:flex;align-items:center;gap:14px;margin-bottom:4px;">
                                            <div style="width:42px;height:42px;background:rgba(255,255,255,.12);border-radius:11px;
                                                        display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="1.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
                                                </svg>
                                            </div>
                                            <div>
                                                <p style="font-size:15px;font-weight:800;color:#fff;margin:0;">Nuevo Proveedor / Contratista</p>
                                                <p style="font-size:11px;color:rgba(255,255,255,.5);margin:3px 0 0;">Datos básicos — se marca automáticamente como proveedor</p>
                                            </div>
                                        </div>'
                                    ))->columnSpanFull(),

                                    Select::make('tipo_persona')
                                        ->label('Tipo de persona')
                                        ->options(['natural'=>'Natural','juridica'=>'Jurídica'])
                                        ->default('natural')->required()->live(),

                                    Select::make('tipo_documento')
                                        ->label('Tipo de documento')
                                        ->options([
                                            'CC'=>'Cédula de ciudadanía',
                                            'NIT'=>'NIT',
                                            'CE'=>'Cédula de extranjería',
                                            'PA'=>'Pasaporte',
                                        ])
                                        ->default('CC')->required(),

                                    TextInput::make('numero_documento')
                                        ->label('Número de documento')->required(),

                                    TextInput::make('primer_nombre')
                                        ->label('Primer nombre')
                                        ->visible(fn(Get $get) => $get('tipo_persona') !== 'juridica'),

                                    TextInput::make('primer_apellido')
                                        ->label('Primer apellido')
                                        ->visible(fn(Get $get) => $get('tipo_persona') !== 'juridica'),

                                    TextInput::make('razon_social')
                                        ->label('Razón social / Nombre empresa')
                                        ->columnSpanFull()
                                        ->visible(fn(Get $get) => $get('tipo_persona') === 'juridica'),

                                    TextInput::make('celular')
                                        ->label('Celular / Teléfono')
                                        ->tel(),

                                    TextInput::make('email')
                                        ->label('Correo electrónico')
                                        ->email(),
                                ])
                                ->columns(2),
                        ])
                        ->createOptionUsing(function (array $data) {
                            $data['es_proveedor'] = true;
                            $data['nombre_completo'] = trim(
                                ($data['razon_social'] ?? '') ?:
                                (($data['primer_nombre'] ?? '') . ' ' . ($data['primer_apellido'] ?? ''))
                            );
                            return Third::create($data)->id;
                        })
                        ->createOptionModalHeading('Nuevo proveedor / contratista'),

                    Select::make('rental_contract_id')
                        ->label('Contrato de arriendo activo')
                        ->options(fn(Get $get) => RentalContract::whereIn('estado', ['activo', 'vigente'])
                            ->when($get('property_id'), fn($q, $pid) => $q->where('property_id', $pid))
                            ->with('property')
                            ->get()
                            ->mapWithKeys(fn($c) => [
                                $c->id => $c->numero . ' — ' . ($c->property->direccion ?? '—') . ' ' . ($c->property->apto_casa_oficina ?? '')
                            ])
                        )
                        ->nullable()
                        ->searchable()
                        ->live()
                        ->disabled(fn(Get $get) => $get('property_id') &&
                            RentalContract::whereIn('estado', ['activo', 'vigente'])
                                ->where('property_id', $get('property_id'))
                                ->count() === 1
                        )
                        ->dehydrated()
                        ->helperText(fn(Get $get) => match(true) {
                            !$get('property_id') => 'Seleccione primero el inmueble',
                            RentalContract::whereIn('estado', ['activo', 'vigente'])->where('property_id', $get('property_id'))->count() === 0
                                => 'Este inmueble no tiene contrato activo',
                            RentalContract::whereIn('estado', ['activo', 'vigente'])->where('property_id', $get('property_id'))->count() === 1
                                => 'Se asignó automáticamente el contrato activo (solo lectura)',
                            default => 'El inmueble tiene varios contratos activos — seleccione uno',
                        }),

                    Select::make('tipo')
                        ->label('Tipo de servicio')
                        ->options([
                            'mantenimiento' => 'Mantenimiento preventivo',
                            'reparacion'    => 'Reparación',
                            'remodelacion'  => 'Remodelación',
                            'limpieza'      => 'Limpieza / Aseo',
                            'inspeccion'    => 'Inspección técnica',
                            'otro'          => 'Otro',
                        ])
                        ->default('mantenimiento')->required(),
                ]),

            Section::make('Detalle del servicio')
                ->description('Descripción, fechas y estado')
                ->icon('heroicon-o-clipboard-document-list')
                ->columns(3)
                ->schema([
                    Textarea::make('descripcion')
                        ->label('Descripción del trabajo')
                        ->required()->rows(3)->columnSpanFull(),

                    DatePicker::make('fecha_servicio')
                        ->label('Fecha del servicio')
                        ->default(now())->required(),

                    Select::make('estado')
                        ->label('Estado')
                        ->options([
                            'pendiente'  => 'Pendiente',
                            'aprobado'   => 'Aprobado',
                            'ejecutado'  => 'Ejecutado',
                            'pagado'     => 'Pagado',
                            'cancelado'  => 'Cancelado',
                        ])
                        ->default('pendiente')->required(),

                    Select::make('quien_paga')
                        ->label('¿Quién asume el costo?')
                        ->options([
                            'propietario'     => 'Propietario (descuento en liquidación)',
                            'inquilino'       => 'Inquilino (pago directo)',
                            'deduccion_canon' => 'Deducción del canon de arrendamiento',
                        ])
                        ->default('propietario')->required()
                        ->helperText('Define cómo se recupera el costo del servicio'),

                    Textarea::make('notas')
                        ->label('Notas / Observaciones')
                        ->rows(2)->nullable()->columnSpanFull(),
                ]),

            Section::make('Valor y contabilización')
                ->description('Costos del servicio y cuentas PUC para el comprobante')
                ->icon('heroicon-o-banknotes')
                ->columns(3)
                ->schema([
                    TextInput::make('valor')
                        ->label('Valor del servicio')
                        ->numeric()->prefix('$')->default(0)->required()
                        ->minValue(0),

                    TextInput::make('iva')
                        ->label('IVA (19%)')
                        ->numeric()->prefix('$')->default(0)
                        ->helperText('Dejar en 0 si no aplica'),

                    TextInput::make('retencion')
                        ->label('Retención en la fuente')
                        ->numeric()->prefix('$')->default(0)
                        ->helperText('Dejar en 0 si no aplica'),

                    Select::make('cuenta_gasto_puc')
                        ->label('Cuenta de gasto/activo PUC')
                        ->options(fn() => AccountingAccount::where('acepta_movimiento', true)
                            ->where('estado', 'activo')
                            ->whereIn('clase', ['5', '6', '1'])
                            ->orderBy('codigo')
                            ->get()
                            ->mapWithKeys(fn($a) => [$a->codigo => $a->codigo . ' — ' . $a->nombre])
                        )
                        ->searchable()->nullable()
                        ->helperText('Por defecto: 513595 Mantenimientos'),

                    Select::make('cuenta_pagar_puc')
                        ->label('Cuenta por pagar PUC')
                        ->options(fn() => AccountingAccount::where('acepta_movimiento', true)
                            ->where('estado', 'activo')
                            ->where('clase', '2')
                            ->orderBy('codigo')
                            ->get()
                            ->mapWithKeys(fn($a) => [$a->codigo => $a->codigo . ' — ' . $a->nombre])
                        )
                        ->searchable()->nullable()
                        ->helperText('Por defecto: 220501 Proveedores nacionales'),

                    DatePicker::make('fecha_pago_proveedor')
                        ->label('Fecha de pago al proveedor')
                        ->nullable(),
                ]),

        ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('numero')
                    ->label('N°')->searchable()->sortable()
                    ->weight('bold')->color('primary')->fontFamily('mono'),

                TextColumn::make('property.direccion')
                    ->label('Inmueble')->searchable()->limit(28),

                TextColumn::make('proveedor.nombre_completo')
                    ->label('Proveedor')->limit(22)->searchable(),

                TextColumn::make('tipo')
                    ->label('Tipo')->badge()
                    ->formatStateUsing(fn($state) => match($state) {
                        'mantenimiento' => 'Mantenimiento',
                        'reparacion'    => 'Reparación',
                        'remodelacion'  => 'Remodelación',
                        'limpieza'      => 'Limpieza',
                        'inspeccion'    => 'Inspección',
                        default         => 'Otro',
                    })
                    ->color(fn($state) => match($state) {
                        'mantenimiento' => 'info',
                        'reparacion'    => 'warning',
                        'remodelacion'  => 'primary',
                        'limpieza'      => 'success',
                        'inspeccion'    => 'gray',
                        default         => 'gray',
                    }),

                TextColumn::make('fecha_servicio')->label('Fecha')->date('d/m/Y')->sortable(),

                TextColumn::make('valor')
                    ->label('Valor')->money('COP')->sortable()->alignEnd(),

                TextColumn::make('quien_paga')
                    ->label('A cargo de')->badge()
                    ->formatStateUsing(fn($state) => match($state) {
                        'propietario'     => 'Propietario',
                        'inquilino'       => 'Inquilino',
                        'deduccion_canon' => 'Desc. Canon',
                        default           => $state,
                    })
                    ->color(fn($state) => match($state) {
                        'propietario'     => 'warning',
                        'inquilino'       => 'info',
                        'deduccion_canon' => 'success',
                        default           => 'gray',
                    }),

                TextColumn::make('estado')->label('Estado')->badge()
                    ->color(fn($state) => match($state) {
                        'pendiente'  => 'warning',
                        'aprobado'   => 'info',
                        'ejecutado'  => 'primary',
                        'pagado'     => 'success',
                        'cancelado'  => 'danger',
                        default      => 'gray',
                    })
                    ->formatStateUsing(fn($state) => match($state) {
                        'pendiente'  => 'Pendiente',
                        'aprobado'   => 'Aprobado',
                        'ejecutado'  => 'Ejecutado',
                        'pagado'     => 'Pagado',
                        'cancelado'  => 'Cancelado',
                        default      => $state,
                    }),

                TextColumn::make('accounting_entry_id')
                    ->label('Contabilizado')
                    ->formatStateUsing(fn($state) => $state ? 'Sí' : 'No')
                    ->badge()
                    ->color(fn($state) => $state ? 'success' : 'gray'),
            ])
            ->defaultSort('fecha_servicio', 'desc')
            ->striped()
            ->filters([
                SelectFilter::make('tipo')->label('Tipo')->options([
                    'mantenimiento' => 'Mantenimiento',
                    'reparacion'    => 'Reparación',
                    'remodelacion'  => 'Remodelación',
                    'limpieza'      => 'Limpieza',
                    'inspeccion'    => 'Inspección',
                    'otro'          => 'Otro',
                ]),
                SelectFilter::make('estado')->label('Estado')->options([
                    'pendiente'  => 'Pendiente',
                    'aprobado'   => 'Aprobado',
                    'ejecutado'  => 'Ejecutado',
                    'pagado'     => 'Pagado',
                    'cancelado'  => 'Cancelado',
                ]),
                SelectFilter::make('quien_paga')->label('A cargo de')->options([
                    'propietario'     => 'Propietario',
                    'inquilino'       => 'Inquilino',
                    'deduccion_canon' => 'Descuento canon',
                ]),
            ])
            ->recordActions([
                EditAction::make()->label('Editar')->outlined()
                    ->visible(fn($record) => !in_array($record->estado, ['pagado', 'cancelado'])),

                \Filament\Actions\Action::make('contabilizar')
                    ->label('Contabilizar')
                    ->icon('heroicon-o-document-check')
                    ->outlined()
                    ->color('primary')
                    ->requiresConfirmation()
                    ->modalHeading('Generar comprobante contable')
                    ->modalDescription('Se generará un comprobante CC con la cuenta por pagar al proveedor.')
                    ->visible(fn($record) => $record->estado === 'ejecutado' && !$record->accounting_entry_id)
                    ->action(function ($record) {
                        try {
                            $record->contabilizar();
                            Notification::make()->title('Comprobante generado')->success()->send();
                        } catch (\RuntimeException $e) {
                            Notification::make()->title($e->getMessage())->danger()->send();
                        }
                    }),

                \Filament\Actions\Action::make('marcar_pagado')
                    ->label('Marcar pagado')
                    ->icon('heroicon-o-check-badge')
                    ->outlined()
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('¿Confirmar pago al proveedor?')
                    ->visible(fn($record) => $record->estado === 'ejecutado')
                    ->action(function ($record) {
                        $record->update([
                            'estado'                 => 'pagado',
                            'estado_pago_proveedor'  => 'pagado',
                            'fecha_pago_proveedor'   => now()->toDateString(),
                        ]);
                        Notification::make()->title('Servicio marcado como pagado')->success()->send();
                    }),

                \Filament\Actions\Action::make('ver_comprobante')
                    ->label('Ver comprobante')
                    ->icon('heroicon-o-document-text')
                    ->outlined()
                    ->color('gray')
                    ->url(fn($record) => $record->accounting_entry_id
                        ? \App\Filament\Resources\Accounting\AccountingEntryResource::getUrl('view', ['record' => $record->accounting_entry_id])
                        : null
                    )
                    ->visible(fn($record) => (bool) $record->accounting_entry_id),

                \Filament\Actions\Action::make('enviar_correo')
                    ->label('Enviar correo')
                    ->icon('heroicon-o-envelope')
                    ->outlined()
                    ->color('info')
                    ->visible(fn($record) => !empty($record->proveedor->email))
                    ->action(function ($record) {
                        try {
                            Mail::to($record->proveedor->email)
                                ->send(new \App\Mail\ServicioAsignadoProveedor($record));
                            Notification::make()
                                ->title('Correo enviado a ' . $record->proveedor->email)
                                ->success()->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Error al enviar correo')
                                ->body($e->getMessage())
                                ->danger()->send();
                        }
                    }),

                \Filament\Actions\Action::make('enviar_whatsapp')
                    ->label('WhatsApp')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->outlined()
                    ->color('success')
                    ->action(function ($record) {
                        $celular = preg_replace('/\D/', '', $record->proveedor->celular ?? '');
                        if (strlen($celular) === 10) {
                            $celular = '57' . $celular;
                        }

                        $mensaje =
                            "Hola *{$record->proveedor->nombre_completo}*, le contacta *Serviarrendar S.A.S* 🏠\n\n" .
                            "Ha sido asignado para el siguiente servicio:\n\n" .
                            "📋 *N° Servicio:* {$record->numero}\n" .
                            "🔧 *Tipo:* {$record->tipo_label}\n" .
                            "🏠 *Inmueble:* " . ($record->property->direccion ?? '—') .
                                ($record->property->apto_casa_oficina ? ' — ' . $record->property->apto_casa_oficina : '') . "\n" .
                            "📅 *Fecha:* " . $record->fecha_servicio->format('d/m/Y') . "\n" .
                            "💰 *Valor:* \$" . number_format($record->valor, 0, ',', '.') . " COP\n\n" .
                            "📝 *Descripción:*\n{$record->descripcion}" .
                            ($record->notas ? "\n\n📌 *Notas:* {$record->notas}" : '') .
                            "\n\nPor favor confírmenos su disponibilidad. ¡Gracias! 🙏";

                        $wap = app(WhatsAppService::class);

                        if (!$wap->isConnected()) {
                            Notification::make()
                                ->title('WhatsApp no conectado')
                                ->body('Redirigiendo a WhatsApp Web...')
                                ->warning()->send();
                            redirect("https://wa.me/{$celular}?text=" . urlencode($mensaje));
                            return;
                        }

                        $result = $wap->enviar($celular, $mensaje);

                        if ($result['ok'] ?? false) {
                            Notification::make()
                                ->title('WhatsApp enviado a ' . $record->proveedor->nombre_completo)
                                ->success()->send();
                        } else {
                            Notification::make()
                                ->title('Error al enviar WhatsApp')
                                ->body($result['error'] ?? 'Error desconocido')
                                ->danger()->send();
                        }
                    })
                    ->visible(fn($record) => !empty($record->proveedor->celular)),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListPropertyServices::route('/'),
            'create' => CreatePropertyService::route('/create'),
            'edit'   => EditPropertyService::route('/{record}/edit'),
        ];
    }
}

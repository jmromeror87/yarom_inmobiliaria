<?php

namespace App\Filament\Resources\PropertyHandovers\Schemas;

use App\Models\RentalContract;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;

class PropertyHandoverForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Wizard::make([

                // ── PASO 1: Datos generales ──────────────────────
                Step::make('Acta')
                    ->description('Datos del acta de entrega')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->schema([
                        TextInput::make('numero')
                            ->label('N° Acta')->disabled()
                            ->placeholder('Auto: ACT-2026-0001'),

                        Select::make('tipo')
                            ->label('Tipo de acta')
                            ->options([
                                'entrega'    => '🔑 Entrega — Inmueble al arrendatario',
                                'devolucion' => '🔄 Devolución — Arrendatario devuelve',
                            ])
                            ->default('entrega')->required(),

                        Select::make('rental_contract_id')
                            ->label('Contrato de arriendo')
                            ->options(fn () =>
                                RentalContract::whereIn('estado', ['activo','firmado'])
                                    ->with(['property','arrendatario'])
                                    ->get()
                                    ->mapWithKeys(fn ($c) => [
                                        $c->id => $c->numero_contrato . ' — ' . $c->property?->codigo . ' · ' . $c->arrendatario?->nombre_completo
                                    ])
                            )
                            ->searchable()->required()->live()
                            ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                if (!$state) return;
                                $c = RentalContract::with(['property','arrendatario'])->find($state);
                                if ($c) {
                                    $set('property_id', $c->property_id);
                                    $set('arrendatario_id', $c->arrendatario_id);
                                    $set('asesor_id', $c->asesor_id);
                                    $set('lugar_acta', $c->property?->direccion);
                                    $set('firmado_arrendatario', $c->arrendatario?->nombre_completo);
                                }
                            }),

                        \Filament\Forms\Components\Hidden::make('property_id'),
                        \Filament\Forms\Components\Hidden::make('arrendatario_id'),

                        Select::make('asesor_id')
                            ->label('Asesor responsable')
                            ->relationship('asesor', 'name')
                            ->searchable()->preload(),

                        DatePicker::make('fecha_acta')
                            ->label('Fecha del acta')->required()->default(now()),

                        TimePicker::make('hora_acta')
                            ->label('Hora del acta')->default(now()),

                        TextInput::make('lugar_acta')
                            ->label('Dirección del inmueble')->columnSpanFull(),

                        Select::make('estado')
                            ->label('Estado del acta')
                            ->options([
                                'borrador'   => '📝 Borrador',
                                'en_proceso' => '🔄 En proceso',
                                'firmada'    => '✍️ Firmada',
                                'cerrada'    => '✅ Cerrada',
                            ])->default('borrador'),
                    ])->columns(2),

                // ── PASO 2: Medidores y llaves ───────────────────
                Step::make('Medidores y Llaves')
                    ->description('Lecturas y llaves entregadas')
                    ->icon('heroicon-o-key')
                    ->schema([
                        Section::make('Lecturas de medidores')
                            ->icon('heroicon-o-bolt')
                            ->columns(3)
                            ->schema([
                                TextInput::make('lectura_agua')
                                    ->label('💧 Agua (m³)')->placeholder('000000'),
                                TextInput::make('lectura_energia')
                                    ->label('⚡ Energía (kWh)')->placeholder('000000'),
                                TextInput::make('lectura_gas')
                                    ->label('🔥 Gas (m³)')->placeholder('000000'),
                            ]),

                        Section::make('Llaves entregadas')
                            ->icon('heroicon-o-key')
                            ->columns(2)
                            ->schema([
                                TextInput::make('llaves_entregadas')
                                    ->label('🔑 Llaves inmueble')->numeric()->default(0),
                                TextInput::make('llaves_control_acceso')
                                    ->label('📟 Control de acceso')->numeric()->default(0),
                                TextInput::make('llaves_parqueadero')
                                    ->label('🚗 Parqueadero')->numeric()->default(0),
                                TextInput::make('llaves_deposito')
                                    ->label('📦 Depósito')->numeric()->default(0),
                                Textarea::make('notas_llaves')
                                    ->label('Observaciones llaves')->rows(2)->columnSpanFull(),
                            ]),
                    ]),

                // ── PASO 3: Inventario por ambientes ─────────────
                Step::make('Inventario')
                    ->description('Estado de cada ambiente y elemento')
                    ->icon('heroicon-o-home')
                    ->schema([
                        \Filament\Forms\Components\Repeater::make('items')
                            ->label('Inventario de ambientes')
                            ->relationship()
                            ->schema([
                                Select::make('ambiente')
                                    ->label('Ambiente')
                                    ->options([
                                        'sala'               => '🛋️ Sala',
                                        'comedor'            => '🪑 Comedor',
                                        'cocina'             => '🍳 Cocina',
                                        'habitacion_principal' => '🛏️ Habitación principal',
                                        'habitacion_2'       => '🛏️ Habitación 2',
                                        'habitacion_3'       => '🛏️ Habitación 3',
                                        'bano_principal'     => '🚿 Baño principal',
                                        'bano_secundario'    => '🚿 Baño secundario',
                                        'bano_social'        => '🚿 Baño social',
                                        'garaje'             => '🚗 Garaje',
                                        'deposito'           => '📦 Depósito',
                                        'patio'              => '🌿 Patio',
                                        'balcon'             => '🏠 Balcón',
                                        'zona_lavanderia'    => '👕 Zona lavandería',
                                        'estudio'            => '💻 Estudio',
                                        'otro'               => '📍 Otro',
                                    ])->required(),

                                TextInput::make('elemento')
                                    ->label('Elemento / Descripción')
                                    ->placeholder('Ej: Puerta principal, ventana, piso...')
                                    ->required(),

                                Select::make('estado')
                                    ->label('Estado')
                                    ->options([
                                        'excelente' => '🟢 Excelente',
                                        'bueno'     => '🔵 Bueno',
                                        'regular'   => '🟡 Regular',
                                        'malo'      => '🔴 Malo',
                                        'no_aplica' => '⚪ No aplica',
                                    ])->default('bueno')->required(),

                                Textarea::make('descripcion')
                                    ->label('Observaciones')->rows(2),

                                FileUpload::make('foto_path')
                                    ->label('📷 Foto de evidencia')
                                    ->image()->disk('public')
                                    ->directory('actas/fotos')
                                    ->maxSize(5120)
                                    ->imagePreviewHeight('80'),
                            ])
                            ->columns(2)
                            ->addActionLabel('+ Agregar elemento')
                            ->defaultItems(0)
                            ->collapsible()
                            ->itemLabel(fn (array $state) =>
                                ($state['ambiente'] ? match($state['ambiente']) {
                                    'sala'               => '🛋️ Sala',
                                    'cocina'             => '🍳 Cocina',
                                    'habitacion_principal' => '🛏️ Hab. Principal',
                                    'bano_principal'     => '🚿 Baño Principal',
                                    default              => ucfirst(str_replace('_',' ',$state['ambiente'])),
                                } : '') . ' — ' . ($state['elemento'] ?? 'Nuevo elemento')
                            )
                            ->columnSpanFull(),
                    ]),

                // ── PASO 4: Estado general y firma ───────────────
                Step::make('Firma')
                    ->description('Estado general y firmas')
                    ->icon('heroicon-o-pencil-square')
                    ->schema([
                        Select::make('estado_general')
                            ->label('Estado general del inmueble')
                            ->options([
                                'excelente' => '🟢 Excelente',
                                'bueno'     => '🔵 Bueno',
                                'regular'   => '🟡 Regular',
                                'malo'      => '🔴 Malo',
                            ])->default('bueno')->required(),

                        Textarea::make('observaciones_generales')
                            ->label('Observaciones generales')
                            ->rows(4)->columnSpanFull(),

                        TextInput::make('firmado_arrendatario')
                            ->label('Nombre arrendatario (firma)'),

                        TextInput::make('firmado_asesor')
                            ->label('Nombre asesor (firma)')
                            ->default(fn () => auth()->user()?->name),

                        DatePicker::make('fecha_firma')
                            ->label('Fecha de firma')->default(now()),

                        FileUpload::make('path_acta_firmada')
                            ->label('📄 Acta firmada (PDF o imagen)')
                            ->disk('public')->directory('actas/firmadas')
                            ->acceptedFileTypes(['application/pdf','image/jpeg','image/png'])
                            ->maxSize(20480)->downloadable()->openable()
                            ->columnSpanFull(),
                    ])->columns(2),

            ])->skippable()->columnSpanFull(),
        ]);
    }
}

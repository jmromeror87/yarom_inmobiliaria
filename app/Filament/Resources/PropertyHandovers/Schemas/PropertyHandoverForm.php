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
                            ->options(fn (Get $get) =>
                                RentalContract::whereIn('estado', ['activo','firmado'])
                                    ->with(['property','arrendatario'])
                                    ->get()
                                    ->mapWithKeys(fn ($c) => [
                                        $c->id => $c->numero_contrato . ' — ' . $c->property?->codigo . ' · ' . $c->arrendatario?->nombre_completo
                                    ])
                            )
                            ->searchable()->required()->live()
                            ->helperText('Seleccione el contrato del inmueble que va a entregar o recibir.')
                            ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                if (!$state) return;
                                $c = RentalContract::with(['property.tipo','arrendatario'])->find($state);
                                if (!$c) return;
                                $set('property_id',          $c->property_id);
                                $set('arrendatario_id',      $c->arrendatario_id);
                                $set('asesor_id',            $c->asesor_id);
                                $set('lugar_acta',           $c->property?->direccion);
                                $set('firmado_arrendatario', $c->arrendatario?->nombre_completo);

                                // Pre-poblar ítems de inventario según características del inmueble
                                $p = $c->property;
                                if (!$p) return;
                                $items = [];
                                $orden = 0;

                                // Zonas según tipo
                                $zonas = [
                                    ['ambiente' => 'sala',     'elemento' => 'Sala — pisos, paredes, cielo raso, puertas, ventanas'],
                                    ['ambiente' => 'comedor',  'elemento' => 'Comedor — pisos, paredes, cielo raso'],
                                    ['ambiente' => 'cocina',   'elemento' => 'Cocina — mesón, poceta, paredes, grifería, enchapes'],
                                ];

                                for ($i = 1; $i <= ($p->habitaciones ?? 0); $i++) {
                                    $amb = $i === 1 ? 'habitacion_principal' : 'habitacion_' . $i;
                                    $zonas[] = ['ambiente' => $amb, 'elemento' => "Habitación {$i} — pisos, paredes, cielo raso, closet, puerta, ventana"];
                                }
                                for ($i = 1; $i <= ($p->banos ?? 0); $i++) {
                                    $amb = $i === 1 ? 'bano_principal' : ($i === 2 ? 'bano_secundario' : 'bano_social');
                                    $zonas[] = ['ambiente' => $amb, 'elemento' => "Baño {$i} — sanitario, lavamanos, ducha, grifería, enchapes, espejo"];
                                }
                                if ($p->garajes > 0) $zonas[] = ['ambiente' => 'garaje', 'elemento' => 'Garaje — piso, puerta, estado general'];
                                $zonas[] = ['ambiente' => 'zona_lavanderia', 'elemento' => 'Zona lavandería / patio — pisos, paredes, grifería'];

                                foreach ($zonas as $z) {
                                    $items[] = [
                                        'ambiente'    => $z['ambiente'],
                                        'elemento'    => $z['elemento'],
                                        'estado'      => 'bueno',
                                        'descripcion' => '',
                                        'foto_path'   => null,
                                        'orden'       => $orden++,
                                    ];
                                }
                                $set('items', $items);
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
                    ->description('Lecturas actuales y llaves entregadas')
                    ->icon('heroicon-o-key')
                    ->schema([
                        Section::make('📊 Lecturas de medidores')
                            ->description('Anote el número que aparece en el medidor al momento de la entrega. Si el servicio no existe en el inmueble, deje el campo en blanco.')
                            ->icon('heroicon-o-bolt')
                            ->columns(3)
                            ->schema([
                                TextInput::make('lectura_agua')
                                    ->label('💧 Agua (m³)')
                                    ->placeholder('Ej: 001234')
                                    ->helperText('Número del medidor de acueducto'),
                                TextInput::make('lectura_energia')
                                    ->label('⚡ Energía (kWh)')
                                    ->placeholder('Ej: 005678')
                                    ->helperText('Número del medidor eléctrico'),
                                TextInput::make('lectura_gas')
                                    ->label('🔥 Gas (m³)')
                                    ->placeholder('Ej: 000456')
                                    ->helperText('Solo si tiene gas natural'),
                            ]),

                        Section::make('🔑 Llaves entregadas')
                            ->description('Cuente físicamente las llaves y anote la cantidad. Ponga 0 si no aplica.')
                            ->icon('heroicon-o-key')
                            ->columns(2)
                            ->schema([
                                TextInput::make('llaves_entregadas')
                                    ->label('🔑 Llaves del inmueble')
                                    ->numeric()->default(1)->minValue(0)
                                    ->helperText('Llaves de la puerta principal'),
                                TextInput::make('llaves_control_acceso')
                                    ->label('📟 Control de acceso / portero')
                                    ->numeric()->default(0)->minValue(0)
                                    ->helperText('Solo si el edificio tiene control de acceso'),
                                TextInput::make('llaves_parqueadero')
                                    ->label('🚗 Llaves de parqueadero')
                                    ->numeric()->default(0)->minValue(0)
                                    ->helperText('0 si no tiene parqueadero'),
                                TextInput::make('llaves_deposito')
                                    ->label('📦 Llaves de depósito')
                                    ->numeric()->default(0)->minValue(0)
                                    ->helperText('0 si no tiene depósito'),
                                Textarea::make('notas_llaves')
                                    ->label('Observaciones sobre las llaves')
                                    ->placeholder('Ej: Se entregan 2 llaves originales y 1 copia. La llave del garaje es magnética.')
                                    ->rows(2)->columnSpanFull(),
                            ]),
                    ]),

                // ── PASO 3: Inventario por ambientes ─────────────
                Step::make('Inventario')
                    ->description('Estado de cada ambiente — se precargaron los espacios del inmueble')
                    ->icon('heroicon-o-home')
                    ->schema([
                        \Filament\Forms\Components\Repeater::make('items')
                            ->label('Inventario de ambientes')
                            ->helperText('Los ambientes se cargaron automáticamente según el inmueble. Revise el estado de cada uno, tome fotos y agregue observaciones si hay daños o novedades.')
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
                            ->label('Nombre completo del arrendatario')
                            ->helperText('Escriba el nombre tal como aparece en la cédula.')
                            ->placeholder('Ej: JUAN CARLOS PÉREZ GÓMEZ'),

                        TextInput::make('firmado_asesor')
                            ->label('Nombre del asesor que entrega')
                            ->default(fn () => \Illuminate\Support\Facades\Auth::user()?->name)
                            ->helperText('Su nombre como representante de la inmobiliaria.'),

                        DatePicker::make('fecha_firma')
                            ->label('Fecha en que se firmó el acta')
                            ->default(now())
                            ->helperText('Fecha real de la firma física.'),

                        FileUpload::make('path_acta_firmada')
                            ->label('📄 Subir acta firmada (foto o PDF)')
                            ->disk('public')->directory('actas/firmadas')
                            ->acceptedFileTypes(['application/pdf','image/jpeg','image/png','image/jpg'])
                            ->maxSize(20480)->downloadable()->openable()
                            ->helperText('Tome una foto clara del acta con las firmas de ambas partes y súbala aquí.')
                            ->columnSpanFull(),
                    ])->columns(2),

            ])->columnSpanFull(),
        ]);
    }
}

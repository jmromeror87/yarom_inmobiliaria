<?php
/*
|--------------------------------------------------------------------------
| YarOM ERP - Soluciones de Gestión
|--------------------------------------------------------------------------
| Proyecto privado desarrollado por:
| Ingeniero Jhoan Romero Rivera
| LinkedIn: https://linkedin.com/in/jmromeror87
|
| Módulo: \1
| Archivo: PropertyForm.php
| Fecha: CURRENT_DAY/05/2026
| Versión: v1.0
|--------------------------------------------------------------------------
*/
        
namespace App\Filament\Resources\Properties\Schemas;

use App\Models\Departamento;
use App\Models\Municipio;
use App\Models\Third;
use App\Forms\Components\MapboxAddressInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;

class PropertyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Wizard::make([

                // ── PASO 1: Tipo y destino ───────────────────────
                Step::make('Tipo y Destino')
                    ->description('¿Para qué se entrega el inmueble?')
                    ->icon('heroicon-o-home')
                    ->schema([
                        Select::make('property_type_id')
                            ->label('Tipo de inmueble')
                            ->relationship('tipo', 'nombre')
                            ->searchable()->preload()->required(),

                        Select::make('propietario_id')
                            ->label('Propietario')
                            ->getSearchResultsUsing(function (string $search) {
                                return Third::propietarios()
                                    ->where(function ($q) use ($search) {
                                        $q->where('nombre_completo', 'like', "%{$search}%")
                                          ->orWhere('numero_documento', 'like', "%{$search}%");
                                    })
                                    ->limit(50)
                                    ->get()
                                    ->mapWithKeys(fn($t) => [
                                        $t->id => $t->nombre_completo . ($t->numero_documento ? '  —  ' . $t->numero_documento : ''),
                                    ])
                                    ->toArray();
                            })
                            ->getOptionLabelUsing(fn($value) => Third::find($value)?->nombre_completo)
                            ->searchable()
                            ->required()
                            ->createOptionForm([
                                Select::make('tipo_persona')
                                    ->label('Tipo de persona')
                                    ->options(['natural' => '👤 Persona natural', 'juridica' => '🏢 Persona jurídica'])
                                    ->default('natural')
                                    ->required()
                                    ->live(),

                                Select::make('tipo_documento')
                                    ->label('Tipo de documento')
                                    ->options(['CC' => 'Cédula de ciudadanía', 'CE' => 'Cédula de extranjería', 'NIT' => 'NIT', 'PP' => 'Pasaporte'])
                                    ->required(),

                                TextInput::make('numero_documento')
                                    ->label('Número de documento')
                                    ->required(),

                                TextInput::make('primer_nombre')
                                    ->label('Primer nombre')
                                    ->required()
                                    ->visible(fn(Get $get) => $get('tipo_persona') !== 'juridica'),

                                TextInput::make('segundo_nombre')
                                    ->label('Segundo nombre')
                                    ->visible(fn(Get $get) => $get('tipo_persona') !== 'juridica'),

                                TextInput::make('primer_apellido')
                                    ->label('Primer apellido')
                                    ->required()
                                    ->visible(fn(Get $get) => $get('tipo_persona') !== 'juridica'),

                                TextInput::make('segundo_apellido')
                                    ->label('Segundo apellido')
                                    ->visible(fn(Get $get) => $get('tipo_persona') !== 'juridica'),

                                TextInput::make('razon_social')
                                    ->label('Razón social')
                                    ->required()
                                    ->visible(fn(Get $get) => $get('tipo_persona') === 'juridica'),

                                TextInput::make('celular')
                                    ->label('Celular')
                                    ->tel(),

                                TextInput::make('email')
                                    ->label('Correo electrónico')
                                    ->email(),
                            ])
                            ->createOptionUsing(function (array $data) {
                                $data['es_propietario'] = true;
                                return Third::create($data)->id;
                            })
                            ->createOptionAction(fn($action) => $action
                                ->modalHeading('Crear nuevo propietario')
                                ->modalWidth('lg')
                            ),

                        TextInput::make('codigo')
                            ->label('Código')
                            ->disabled()->placeholder('Auto: INM-2026-0001'),

                        Select::make('estado')
                            ->label('Estado')
                            ->options([
                                'en_captacion'          => '📋 En captación',
                                'documentos_pendientes' => '📄 Documentos pendientes',
                                'disponible'            => '✅ Disponible',
                                'arrendado'             => '🔑 Arrendado',
                                'en_venta'              => '🏷️ En venta',
                                'vendido'               => '🤝 Vendido',
                                'en_mantenimiento'      => '🔧 En mantenimiento',
                                'inactivo'              => '❌ Inactivo',
                            ])->default('en_captacion')->required(),

                        Toggle::make('disponible_arriendo')
                            ->label('Disponible para ARRIENDO')
                            ->default(true)->live()
                            ->helperText('Activa los campos de arriendo'),

                        Toggle::make('disponible_venta')
                            ->label('Disponible para VENTA')
                            ->live()
                            ->helperText('Activa los campos de venta'),

                        Select::make('asesor_id')
                            ->label('Asesor responsable')
                            ->relationship('asesor', 'name')
                            ->searchable()->preload(),

                        DatePicker::make('fecha_captacion')
                            ->label('Fecha de captación')->default(now()),
                    ])->columns(2),

                // ── PASO 2: Ubicación ────────────────────────────
                Step::make('Ubicación')
                    ->description('Dirección y localización del inmueble')
                    ->icon('heroicon-o-map-pin')
                    ->schema([
                        Select::make('departamento_id')
                            ->label('Departamento')
                            ->options(fn () => Departamento::orderBy('nombre')->pluck('nombre', 'id'))
                            ->searchable()->live()
                            ->afterStateUpdated(fn (Set $set) => $set('municipio_id', null)),

                        Select::make('municipio_id')
                            ->label('Municipio')
                            ->options(fn (Get $get) =>
                                Municipio::where('departamento_id', $get('departamento_id'))
                                    ->orderBy('nombre')->pluck('nombre', 'id')
                            )->searchable(),

                        MapboxAddressInput::make('direccion')
                            ->label('Dirección')
                            ->required()
                            ->placeholder('Ej: Calle 10 # 34-56, Ocaña')
                            ->columnSpanFull(),

                        TextInput::make('barrio')->label('Barrio / Localidad'),

                        TextInput::make('conjunto_edificio')
                            ->label('Conjunto / Edificio')
                            ->placeholder('Torres del Parque'),

                        TextInput::make('apto_casa_oficina')
                            ->label('Apto / Casa / Oficina')
                            ->placeholder('Apto 301'),

                        TextInput::make('latitud')->label('Latitud')->numeric(),
                        TextInput::make('longitud')->label('Longitud')->numeric(),
                    ])->columns(2),

                // ── PASO 3: Características ──────────────────────
                Step::make('Características')
                    ->description('Especificaciones físicas del inmueble')
                    ->icon('heroicon-o-building-office')
                    ->schema([
                        TextInput::make('estrato')
                            ->label('Estrato')->numeric()->default(1)->minValue(1)->maxValue(6),
                        TextInput::make('anio_construccion')
                            ->label('Año construcción')->numeric()->placeholder('2010'),
                        TextInput::make('area_construida_m2')
                            ->label('Área construida m²')->numeric()->suffix('m²'),
                        TextInput::make('area_privada_m2')
                            ->label('Área privada m²')->numeric()->suffix('m²'),
                        TextInput::make('area_total_m2')
                            ->label('Área total m²')->numeric()->suffix('m²'),
                        TextInput::make('habitaciones')
                            ->label('Habitaciones')->numeric()->default(0),
                        TextInput::make('banos')
                            ->label('Baños')->numeric()->default(0),
                        TextInput::make('garajes')
                            ->label('Garajes')->numeric()->default(0),
                        TextInput::make('depositos')
                            ->label('Depósitos')->numeric()->default(0),
                        TextInput::make('piso')->label('Piso')->numeric(),
                        TextInput::make('total_pisos')
                            ->label('Total pisos edificio')->numeric(),
                        TextInput::make('porcentaje_propiedad')
                            ->label('% Propiedad')->numeric()->suffix('%')->default(100)
                            ->helperText('50% si es copropiedad'),
                        TextInput::make('coeficiente_copropiedad')
                            ->label('Coeficiente copropiedad')->numeric()->suffix('%'),
                        TextInput::make('escritura_ph_numero')
                            ->label('N° Escritura PH')->placeholder('562'),
                    ])->columns(3),

                // ── PASO 4: Amenidades ───────────────────────────
                Step::make('Amenidades')
                    ->description('Características y servicios adicionales')
                    ->icon('heroicon-o-sparkles')
                    ->schema([
                        Toggle::make('tiene_ascensor')->label('Ascensor'),
                        Toggle::make('tiene_piscina')->label('Piscina'),
                        Toggle::make('tiene_gym')->label('Gimnasio'),
                        Toggle::make('tiene_salon_comunal')->label('Salón comunal'),
                        Toggle::make('tiene_vigilancia')->label('Vigilancia 24h'),
                        Toggle::make('permite_mascotas')->label('Permite mascotas'),
                        Toggle::make('amoblado')->label('Amoblado'),
                    ])->columns(3),

                // ── PASO 5: Valores económicos ───────────────────
                Step::make('Valores')
                    ->description('Canon, precio y avalúos')
                    ->icon('heroicon-o-banknotes')
                    ->schema([
                        TextInput::make('canon_arriendo')
                            ->label('Canon mensual de arriendo')
                            ->numeric()->prefix('$')
                            ->helperText('Valor mensual que paga el arrendatario')
                            ->visible(fn (Get $get) => (bool)$get('disponible_arriendo')),

                        TextInput::make('cuota_administracion')
                            ->label('Cuota de administración')
                            ->numeric()->prefix('$')->default(0)
                            ->helperText('Cuota mensual del conjunto/edificio')
                            ->visible(fn (Get $get) => (bool)$get('disponible_arriendo')),

                        TextInput::make('precio_venta')
                            ->label('Precio de venta')
                            ->numeric()->prefix('$')
                            ->helperText('Precio total de venta del inmueble')
                            ->visible(fn (Get $get) => (bool)$get('disponible_venta')),

                        TextInput::make('avaluo_catastral')
                            ->label('Avalúo catastral')->numeric()->prefix('$'),
                        TextInput::make('avaluo_comercial')
                            ->label('Avalúo comercial')->numeric()->prefix('$'),
                        TextInput::make('anio_avaluo')
                            ->label('Año del avalúo')->numeric(),
                    ])->columns(2),

                // ── PASO 6: Documentos con subida ────────────────
                Step::make('Documentos')
                    ->description('Suba y visualice los documentos del propietario')
                    ->icon('heroicon-o-document-check')
                    ->schema([

                        Section::make('Escritura pública')
                            ->icon('heroicon-o-document-text')->collapsible()
                            ->schema([
                                Toggle::make('doc_escritura')->label('Documento recibido'),
                                FileUpload::make('doc_escritura_path')
                                    ->label('Subir escritura')
                                    ->disk('public')->directory('inmuebles/documentos')
                                    ->acceptedFileTypes(['application/pdf','image/jpeg','image/png','image/jpg'])
                                    ->maxSize(10240)->downloadable()->openable()
                                    ->columnSpanFull(),
                            ])->columns(2),

                        Section::make('Certificado de libertad y tradición')
                            ->icon('heroicon-o-document-check')->collapsible()
                            ->schema([
                                Toggle::make('doc_certificado_libertad')
                                    ->label('Documento recibido')->live(),
                                DatePicker::make('doc_certificado_libertad_fecha')
                                    ->label('Fecha del certificado')
                                    ->helperText('Vigencia máxima 30 días')
                                    ->visible(fn (Get $get) => (bool)$get('doc_certificado_libertad')),
                                FileUpload::make('doc_certificado_libertad_path')
                                    ->label('Subir certificado')
                                    ->disk('public')->directory('inmuebles/documentos')
                                    ->acceptedFileTypes(['application/pdf','image/jpeg','image/png','image/jpg'])
                                    ->maxSize(10240)->downloadable()->openable()
                                    ->columnSpanFull(),
                            ])->columns(2),

                        Section::make('Predial al día')
                            ->icon('heroicon-o-building-library')->collapsible()
                            ->schema([
                                Toggle::make('doc_predial')->label('Documento recibido'),
                                FileUpload::make('doc_predial_path')
                                    ->label('Subir predial')
                                    ->disk('public')->directory('inmuebles/documentos')
                                    ->acceptedFileTypes(['application/pdf','image/jpeg','image/png','image/jpg'])
                                    ->maxSize(10240)->downloadable()->openable()
                                    ->columnSpanFull(),
                            ])->columns(2),

                        Section::make('Paz y salvo administración')
                            ->icon('heroicon-o-check-badge')->collapsible()
                            ->schema([
                                Toggle::make('doc_paz_salvo_admin')->label('Documento recibido'),
                                FileUpload::make('doc_paz_salvo_admin_path')
                                    ->label('Subir paz y salvo')
                                    ->disk('public')->directory('inmuebles/documentos')
                                    ->acceptedFileTypes(['application/pdf','image/jpeg','image/png','image/jpg'])
                                    ->maxSize(10240)->downloadable()->openable()
                                    ->columnSpanFull(),
                            ])->columns(2),

                        Section::make('Documento de identidad propietario')
                            ->icon('heroicon-o-identification')->collapsible()
                            ->schema([
                                Toggle::make('doc_documento_propietario')->label('Documento recibido'),
                                FileUpload::make('doc_propietario_path')
                                    ->label('Subir documento')
                                    ->disk('public')->directory('inmuebles/documentos')
                                    ->acceptedFileTypes(['application/pdf','image/jpeg','image/png','image/jpg'])
                                    ->maxSize(10240)->downloadable()->openable()
                                    ->columnSpanFull(),
                            ])->columns(2),

                        Section::make('Recibo de servicios públicos')
                            ->icon('heroicon-o-bolt')->collapsible()
                            ->schema([
                                Toggle::make('doc_recibo_servicios')->label('Documento recibido'),
                                FileUpload::make('doc_recibo_servicios_path')
                                    ->label('Subir recibo')
                                    ->disk('public')->directory('inmuebles/documentos')
                                    ->acceptedFileTypes(['application/pdf','image/jpeg','image/png','image/jpg'])
                                    ->maxSize(10240)->downloadable()->openable()
                                    ->columnSpanFull(),
                            ])->columns(2),

                    ])->columns(1),

                // ── PASO 7: Galería de fotos ─────────────────────
                Step::make('Galería de Fotos')
                    ->description('Fotos del inmueble — arrastra para reordenar')
                    ->icon('heroicon-o-photo')
                    ->schema([
                        \Filament\Forms\Components\Repeater::make('images')
                            ->label('Fotos del inmueble')
                            ->relationship()
                            ->mutateRelationshipDataBeforeCreateUsing(function (array $data): ?array {
                                return empty($data['path']) ? null : $data;
                            })
                            ->mutateRelationshipDataBeforeSaveUsing(function (array $data): ?array {
                                return empty($data['path']) ? null : $data;
                            })
                            ->schema([
                                FileUpload::make('path')
                                    ->label('Foto')
                                    ->image()->imageEditor()
                                    ->disk('public')
                                    ->directory('inmuebles/fotos')
                                    ->maxSize(8192)
                                    ->imagePreviewHeight('200')
                                    ->required()
                                    ->columnSpanFull(),
                                TextInput::make('titulo')
                                    ->label('Descripción de la foto')
                                    ->placeholder('Sala principal, vista frontal...'),
                                Select::make('categoria')
                                    ->label('Categoría')
                                    ->options([
                                        'fachada'    => '🏠 Fachada',
                                        'sala'       => '🛋️ Sala',
                                        'cocina'     => '🍳 Cocina',
                                        'habitacion' => '🛏️ Habitación',
                                        'bano'       => '🚿 Baño',
                                        'zona_comun' => '🏊 Zona común',
                                        'vista'      => '🌅 Vista',
                                        'plano'      => '📐 Plano',
                                        'otro'       => '📷 Otro',
                                    ])->default('otro'),
                                Toggle::make('es_portada')
                                    ->label('⭐ Foto de portada')
                                    ->helperText('Se mostrará como imagen principal'),
                                \Filament\Forms\Components\Hidden::make('orden'),
                            ])
                            ->columns(2)
                            ->reorderable('orden')
                            ->reorderableWithButtons()
                            ->collapsible()
                            ->itemLabel(fn (array $state) => $state['titulo'] ?? 'Nueva foto')
                            ->addActionLabel('+ Agregar foto')
                            ->columnSpanFull(),
                    ]),

                // ── PASO 8: Publicación ──────────────────────────
                Step::make('Publicación')

                    ->description('Descripción pública y notas internas')
                    ->icon('heroicon-o-megaphone')
                    ->schema([
                        DatePicker::make('fecha_disponible')->label('Fecha disponible'),
                        Textarea::make('servicios_publicos')
                            ->label('Servicios públicos')
                            ->placeholder('Agua: ESPO S.A · Energía: CENS')
                            ->rows(2)->columnSpanFull(),
                        Textarea::make('descripcion_publica')
                            ->label('Descripción para portales y anuncios')
                            ->rows(4)->columnSpanFull(),
                        Textarea::make('notas_internas')
                            ->label('Notas internas del equipo')
                            ->rows(3)->columnSpanFull(),
                    ])->columns(2),

            ])->skippable()->columnSpanFull(),
        ]);
    }
}

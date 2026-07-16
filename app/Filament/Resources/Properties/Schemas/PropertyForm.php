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
use App\Models\Company;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
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
use Illuminate\Support\HtmlString;

class PropertyForm
{
    private static function recalcularSeguro(Get $get, Set $set, $canon = null, $admin = null): void
    {
        if (!(bool)$get('tiene_seguro_sura')) return;

        $canon = $canon !== null ? (float)$canon : (float)($get('canon_arriendo') ?? 0);
        $admin = $admin !== null ? (float)$admin  : (float)($get('cuota_administracion') ?? 0);

        if ($canon <= 0) return;

        $company  = Company::first();
        $tSura    = (float)($company?->tarifa_seguro_sura ?? 2.50);
        $tIva     = (float)($company?->tarifa_iva ?? 19);
        $seguro   = round($canon * ($tSura / 100), 2);
        $iva      = round($seguro * ($tIva / 100), 2);
        $exacto   = $canon + $admin + $seguro + $iva;
        $sugerido = (int)(ceil($exacto / 1000) * 1000);

        $set('canon_cobrado_inquilino', $sugerido);
    }

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

                        Select::make('destinacion')
                            ->label('Destinación')
                            ->options([
                                'vivienda_familiar' => '🏠 Vivienda familiar',
                                'vivienda_estudiantil' => '🎓 Vivienda estudiantil',
                                'comercial'         => '🏢 Local comercial',
                                'oficina'           => '💼 Oficina',
                                'bodega'            => '📦 Bodega / Industrial',
                                'mixto'             => '🔀 Mixto (vivienda + comercio)',
                            ])
                            ->required()
                            ->helperText('Define el uso permitido — determina el tipo de contrato aplicable (Ley 820 o comercial)'),

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

                        Select::make('business_origin_id')
                            ->label('Origen del negocio')
                            ->relationship('businessOrigin', 'nombre')
                            ->default(fn () => \App\Models\BusinessOrigin::where('nombre', 'Serviarrendar')->value('id'))
                            ->searchable()->preload()
                            ->helperText('Identifica de qué negocio proviene este inmueble (útil para inmuebles recién incorporados de otra inmobiliaria)'),

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
                            ->label('Área construida m²')
                            ->numeric()->suffix('m²')->minValue(1)
                            ->live(onBlur: true)
                            ->helperText('Área total techada del inmueble.'),

                        TextInput::make('area_privada_m2')
                            ->label('Área privada m²')
                            ->numeric()->suffix('m²')->minValue(0)
                            ->helperText('Debe ser menor o igual al área construida.')
                            ->rules([
                                fn (Get $get) => function (string $attribute, $value, \Closure $fail) use ($get) {
                                    $construida = (float) $get('area_construida_m2');
                                    if ($construida > 0 && (float) $value > $construida) {
                                        $fail('El área privada no puede ser mayor al área construida.');
                                    }
                                },
                            ]),

                        TextInput::make('area_total_m2')
                            ->label('Área total m²')
                            ->numeric()->suffix('m²')->minValue(0)
                            ->helperText('Incluye zonas comunes, patio, terraza, etc.'),
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
                            ->label('% Propiedad')
                            ->numeric()->suffix('%')->default(100)
                            ->minValue(0)->maxValue(100)
                            ->helperText('50% si es copropiedad — máximo 100%'),

                        TextInput::make('coeficiente_copropiedad')
                            ->label('Coeficiente copropiedad')
                            ->numeric()->suffix('%')
                            ->minValue(0)->maxValue(100)
                            ->helperText('Porcentaje sobre el total del edificio — máximo 100%'),
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
                            ->label('Canon mensual de arriendo (base propietario)')
                            ->numeric()->prefix('$')
                            ->minValue(0)
                            ->required(fn (Get $get) => (bool)$get('disponible_arriendo'))
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Get $get, Set $set, $state) =>
                                self::recalcularSeguro($get, $set, $state, null))
                            ->helperText('Valor base del canon pactado con el propietario.')
                            ->visible(fn (Get $get) => (bool)$get('disponible_arriendo')),

                        TextInput::make('cuota_administracion')
                            ->label('Cuota de administración')
                            ->numeric()->prefix('$')->default(0)
                            ->minValue(0)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Get $get, Set $set) =>
                                self::recalcularSeguro($get, $set, null, null))
                            ->helperText('0 si no aplica conjunto/edificio.')
                            ->visible(fn (Get $get) => (bool)$get('disponible_arriendo')),

                        Toggle::make('tiene_seguro_sura')
                            ->label('🛡️ Tiene seguro SURA (Suramericana)')
                            ->helperText('Actívelo si el inmueble tiene póliza de arrendamiento SURA.')
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                if (!$state) { $set('canon_cobrado_inquilino', null); return; }
                                self::recalcularSeguro($get, $set, null, null);
                            })
                            ->visible(fn (Get $get) => (bool)$get('disponible_arriendo')),

                        Placeholder::make('calculadora_sura')
                            ->label('')
                            ->visible(fn (Get $get) => (bool)$get('disponible_arriendo') && (bool)$get('tiene_seguro_sura'))
                            ->columnSpanFull()
                            ->content(function (Get $get, $record): HtmlString {
                                $canon          = (float)($get('canon_arriendo') ?? 0);
                                $admin          = (float)($get('cuota_administracion') ?? 0);
                                $canonInquilino = (float)($get('canon_cobrado_inquilino') ?? 0);
                                $company        = Company::first();
                                $tarifaSura     = (float)($company?->tarifa_seguro_sura ?? 2.50);
                                $tarifaIva      = (float)($company?->tarifa_iva ?? 19);

                                if ($canon <= 0) {
                                    return new HtmlString('<div style="padding:12px;background:#f8fafc;border:1px dashed #cbd5e1;border-radius:8px;color:#94a3b8;font-size:13px;">Digite el canon para ver el desglose.</div>');
                                }

                                $seguro      = round($canon * ($tarifaSura / 100), 2);
                                $ivaSeguro   = round($seguro * ($tarifaIva / 100), 2);
                                $totalExacto = $canon + $admin + $seguro + $ivaSeguro;
                                $sugerido    = ceil($totalExacto / 1000) * 1000;
                                $valorFinal  = $canonInquilino > 0 ? $canonInquilino : $sugerido;
                                $diferencia  = round($valorFinal - $totalExacto, 2);
                                $totalAsura  = $seguro + $ivaSeguro;
                                $fmt         = fn($v) => '$' . number_format($v, 0, ',', '.');

                                $filas = [
                                    ['🏠 Canon base (propietario)',            $fmt($canon),     '#1e40af', '#eff6ff'],
                                    ['🏢 Cuota administración',                $fmt($admin),     '#374151', '#f9fafb'],
                                    ['🛡️ Seguro SURA (' . $tarifaSura . '%)', $fmt($seguro),    '#7c3aed', '#faf5ff'],
                                    ['🧾 IVA seguro (19%)',                    $fmt($ivaSeguro), '#7c3aed', '#faf5ff'],
                                ];

                                $html  = '<div style="border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;font-size:13px;font-family:monospace;">';
                                $html .= '<div style="background:#1e293b;color:#f8fafc;padding:10px 14px;font-weight:700;font-size:12px;letter-spacing:.05em;">🧮 CALCULADORA SEGURO SURA</div>';

                                foreach ($filas as [$label, $valor, $color, $bg]) {
                                    $html .= "<div style='display:flex;justify-content:space-between;padding:8px 14px;background:{$bg};border-bottom:1px solid #e2e8f0;'>"
                                        . "<span style='color:#475569;'>{$label}</span>"
                                        . "<span style='font-weight:700;color:{$color};'>{$valor}</span></div>";
                                }

                                $html .= "<div style='display:flex;justify-content:space-between;padding:10px 14px;background:#fef3c7;border-bottom:2px solid #f59e0b;'>"
                                    . "<span style='color:#92400e;font-weight:600;'>📊 Total exacto al inquilino</span>"
                                    . "<span style='font-weight:800;color:#92400e;font-size:15px;'>{$fmt($totalExacto)}</span></div>";

                                $colorDif = $diferencia >= 0 ? '#166534' : '#991b1b';
                                $bgDif    = $diferencia >= 0 ? '#f0fdf4'  : '#fef2f2';

                                $html .= "<div style='display:flex;justify-content:space-between;padding:10px 14px;background:#dbeafe;border-bottom:1px solid #93c5fd;'>"
                                    . "<span style='color:#1e40af;font-weight:600;'>💰 Cobrado al inquilino (campo abajo)</span>"
                                    . "<span style='font-weight:800;color:#1e40af;font-size:15px;'>{$fmt($valorFinal)}</span></div>";

                                $html .= "<div style='display:flex;justify-content:space-between;padding:8px 14px;background:{$bgDif};border-bottom:2px solid #e2e8f0;'>"
                                    . "<span style='color:{$colorDif};font-weight:600;'>↗ Diferencia (va al propietario)</span>"
                                    . "<span style='font-weight:700;color:{$colorDif};font-size:14px;'>{$fmt($diferencia)}</span></div>";

                                $html .= "<div style='display:flex;justify-content:space-between;padding:10px 14px;background:#fdf4ff;'>"
                                    . "<span style='color:#7e22ce;font-weight:600;'>🏦 Total a pagar a ASURA</span>"
                                    . "<span style='font-weight:800;color:#7e22ce;font-size:15px;'>{$fmt($totalAsura)}</span></div>";

                                $html .= '</div>';
                                return new HtmlString($html);
                            }),

                        TextInput::make('canon_cobrado_inquilino')
                            ->label('💰 Canon cobrado al inquilino (valor redondeado)')
                            ->numeric()->prefix('$')->minValue(0)
                            ->live(onBlur: true)
                            ->visible(fn (Get $get) => (bool)$get('disponible_arriendo') && (bool)$get('tiene_seguro_sura'))
                            ->helperText('Se llena automáticamente con el valor sugerido. Cámbielo si acordó un valor diferente con el inquilino.')
                            ->columnSpanFull(),

                        TextInput::make('precio_venta')
                            ->label('Precio de venta')
                            ->numeric()->prefix('$')
                            ->minValue(0)
                            ->required(fn (Get $get) => (bool)$get('disponible_venta'))
                            ->helperText('Obligatorio si el inmueble está en venta.')
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

                        Section::make('Certificado de libertad y tradición (CTL)')
                            ->icon('heroicon-o-document-check')->collapsible()
                            ->description('Emitido por ORIP Ocaña — vigencia máxima 30 días. Decreto 1250/1970.')
                            ->schema([
                                Toggle::make('doc_certificado_libertad')
                                    ->label('CTL recibido')
                                    ->live(),

                                DatePicker::make('doc_certificado_libertad_fecha')
                                    ->label('Fecha de emisión del CTL')
                                    ->helperText(function (Get $get) {
                                        $fecha = $get('doc_certificado_libertad_fecha');
                                        if (!$fecha) return 'Vigencia máxima recomendada: 30 días';
                                        $dias = now()->diffInDays(\Carbon\Carbon::parse($fecha), false);
                                        if ($dias < -30) return '🚫 CTL VENCIDO — han pasado ' . abs($dias) . ' días. Solicitar nuevo certificado.';
                                        if ($dias < -15) return '⚠️ CTL próximo a vencer — ' . abs($dias) . ' días. Renovar pronto.';
                                        return '✅ CTL vigente — ' . abs($dias) . ' días de antigüedad.';
                                    })
                                    ->live(onBlur: true)
                                    ->visible(fn (Get $get) => (bool)$get('doc_certificado_libertad')),

                                // Limitación jurídica
                                Toggle::make('ctl_tiene_limitacion')
                                    ->label('⚠️ El CTL muestra hipoteca, embargo o medida cautelar')
                                    ->helperText('Si está activo, el inmueble queda BLOQUEADO hasta solución jurídica.')
                                    ->live()
                                    ->visible(fn (Get $get) => (bool)$get('doc_certificado_libertad')),

                                Select::make('ctl_tipo_limitacion')
                                    ->label('Tipo de limitación')
                                    ->options([
                                        'hipoteca'         => '🏦 Hipoteca vigente',
                                        'embargo'          => '⚖️ Embargo',
                                        'medida_cautelar'  => '🔒 Medida cautelar',
                                        'patrimonio_familia'=> '👨‍👩‍👧 Afectación patrimonio familiar',
                                        'otro'             => '❓ Otro',
                                    ])
                                    ->visible(fn (Get $get) => (bool)$get('ctl_tiene_limitacion'))
                                    ->required(fn (Get $get) => (bool)$get('ctl_tiene_limitacion')),

                                \Filament\Forms\Components\Textarea::make('ctl_observacion_limitacion')
                                    ->label('Descripción de la limitación')
                                    ->helperText('El asesor NO puede avanzar a contrato hasta que el área jurídica autorice.')
                                    ->rows(2)
                                    ->visible(fn (Get $get) => (bool)$get('ctl_tiene_limitacion'))
                                    ->columnSpanFull(),

                                FileUpload::make('doc_certificado_libertad_path')
                                    ->label('Subir CTL (PDF o imagen)')
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
                            ->description('Último período disponible — confirma dirección, estrato y que los servicios están activos.')
                            ->schema([
                                Toggle::make('doc_recibo_servicios')
                                    ->label('Recibo recibido')
                                    ->live(),

                                Select::make('doc_recibo_tipo')
                                    ->label('Tipo de servicio')
                                    ->options([
                                        'agua'    => '💧 Agua (ESPO u otro)',
                                        'energia' => '⚡ Energía (CENS u otro)',
                                        'gas'     => '🔥 Gas natural',
                                    ])
                                    ->visible(fn (Get $get) => (bool)$get('doc_recibo_servicios')),

                                TextInput::make('doc_recibo_periodo')
                                    ->label('Período del recibo')
                                    ->placeholder('Ej: Mayo 2026')
                                    ->helperText('Debe ser el último mes facturado.')
                                    ->visible(fn (Get $get) => (bool)$get('doc_recibo_servicios')),

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

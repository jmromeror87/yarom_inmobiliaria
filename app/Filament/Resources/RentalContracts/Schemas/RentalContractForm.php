<?php

namespace App\Filament\Resources\RentalContracts\Schemas;

use App\Models\ContractTemplate;
use App\Models\Property;
use App\Models\Request as Solicitud;
use App\Models\RentalContract;
use App\Models\RentalContractClause;
use App\Models\ContractClause;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Hidden;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;

class RentalContractForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Wizard::make([

                // ── PASO 1: Tipo, inmueble y solicitud ───────────
                Step::make('Contrato')
                    ->description('Tipo, inmueble y solicitud aprobada')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        TextInput::make('numero_contrato')
                            ->label('N° Contrato')
                            ->disabled()->placeholder('Auto: VIV-2026-0001'),

                        Select::make('tipo')
                            ->label('Tipo de contrato')
                            ->options([
                                'vivienda_urbana' => '🏠 Vivienda urbana (Ley 820)',
                                'comercial'       => '🏢 Comercial',
                            ])
                            ->default('vivienda_urbana')->required()->live()
                            ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                $set('duracion_meses',   $state === 'comercial' ? 12 : 6);
                                $set('meses_preaviso',   $state === 'comercial' ? 6 : 3);
                                $set('tipo_incremento',  $state === 'comercial' ? 'porcentaje_fijo' : 'ipc_vivienda');
                                $set('porcentaje_incremento', $state === 'comercial' ? 10 : null);
                                $set('contract_template_id', null);
                                $set('destinacion', $state === 'comercial' ? 'LOCAL COMERCIAL' : 'VIVIENDA FAMILIAR');
                                $set('servicios_cargo_arrendatario', $state === 'comercial' ? 'LUZ' : 'AGUA, GAS Y LUZ');
                                if ($get('fecha_inicio')) {
                                    $meses = $state === 'comercial' ? 12 : 6;
                                    $set('fecha_fin', \Carbon\Carbon::parse($get('fecha_inicio'))->addMonths($meses)->toDateString());
                                }
                            }),

                        // ── Inmueble: solo con contrato admin activo ──
                        Select::make('property_id')
                            ->label('Inmueble')
                            ->options(fn () =>
                                Property::with(['tipo','administrationContracts'])
                                    ->where('estado', 'disponible')
                                    ->whereHas('administrationContracts', fn ($q) =>
                                        $q->whereIn('estado', ['activo','firmado'])
                                    )
                                    ->get()
                                    ->mapWithKeys(fn ($p) => [
                                        $p->id => $p->codigo . ' — ' . $p->direccion . ' (' . ($p->tipo?->nombre ?? '') . ')'
                                    ])
                            )
                            ->searchable()->required()->live()
                            ->helperText('Solo inmuebles con contrato de administración activo')
                            ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                if (!$state) return;
                                $p = Property::with(['administrationContracts'])->find($state);
                                if ($p) {
                                    $set('canon_mensual', $p->canon_arriendo);
                                    $set('cuota_administracion', $p->cuota_administracion);
                                    $set('servicios_cargo_arrendatario', $p->servicios_publicos);
                                    $set('folio_inmobiliario', $p->escritura_ph_numero);
                                    $contratoAdmin = $p->administrationContracts
                                        ->whereIn('estado', ['activo','firmado'])->first();
                                    if ($contratoAdmin) {
                                        $set('administration_contract_id', $contratoAdmin->id);
                                    }
                                }
                                // Resetear solicitud y arrendatario al cambiar inmueble
                                $set('request_id', null);
                                $set('arrendatario_id', null);
                            }),

                        \Filament\Forms\Components\Hidden::make('administration_contract_id'),
                        \Filament\Forms\Components\Placeholder::make('admin_contrato_display')
                            ->label('Contrato de administración vinculado')
                            ->content(fn (Get $get) =>
                                $get('administration_contract_id')
                                    ? \App\Models\AdministrationContract::find($get('administration_contract_id'))?->numero_contrato . ' — vinculado automáticamente'
                                    : '⏳ Se vincula al seleccionar el inmueble'
                            ),

                        // ── Solicitud aprobada → trae arrendatario + deudores ──
                        Select::make('request_id')
                            ->label('📋 Solicitud de estudio aprobada')
                            ->options(fn (Get $get) =>
                                Solicitud::where('tipo', 'estudio_arrendatario')
                                    ->where('estado', 'aprobada')
                                    ->when($get('property_id'), fn ($q, $pid) => $q->where('property_id', $pid))
                                    ->with(['thirds.third'])
                                    ->get()
                                    ->mapWithKeys(fn ($s) => [
                                        $s->id => $s->numero . ' — ' . $s->thirds->where('rol','titular')->first()?->third?->nombre_completo . ' (Aprobada Sura)'
                                    ])
                            )
                            ->searchable()->required()->live()
                            ->helperText('Seleccione la solicitud aprobada por Suramericana')
                            ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                if (!$state) return;
                                $solicitud = Solicitud::with(['thirds.third'])->find($state);
                                if (!$solicitud) return;

                                // Auto-poblar arrendatario desde titular de la solicitud
                                $titular = $solicitud->thirds->where('rol','titular')->first();
                                if ($titular) {
                                    $set('arrendatario_id', $titular->third_id);
                                }

                                // Auto-poblar canon desde solicitud
                                if ($solicitud->canon_evaluar) {
                                    $set('canon_mensual', $solicitud->canon_evaluar);
                                }
                            }),

                        // ── Arrendatario: Hidden guarda, Placeholder muestra ──
                        \Filament\Forms\Components\Hidden::make('arrendatario_id'),
                        \Filament\Forms\Components\Placeholder::make('arrendatario_display')
                            ->label('Arrendatario principal')
                            ->content(fn (Get $get) =>
                                $get('arrendatario_id')
                                    ? \App\Models\Third::find($get('arrendatario_id'))?->nombre_completo . ' — cargado desde la solicitud'
                                    : '⏳ Se completa al seleccionar la solicitud aprobada'
                            ),

                        Select::make('contract_template_id')
                            ->label('Plantilla de contrato')
                            ->options(fn (Get $get) =>
                                ContractTemplate::where('is_active', true)
                                    ->whereIn('tipo_contrato', $get('tipo') === 'comercial'
                                        ? ['arrendamiento_comercial']
                                        : ['arrendamiento_vivienda']
                                    )
                                    ->pluck('nombre', 'id')
                            )
                            ->required(),

                        Select::make('asesor_id')
                            ->label('Asesor')
                            ->relationship('asesor', 'name')
                            ->searchable()->preload(),

                        Select::make('estado')
                            ->label('Estado')
                            ->options([
                                'borrador'             => '📝 Borrador',
                                'enviado_arrendatario' => '📤 Enviado al arrendatario',
                                'aprobado'             => '✅ Aprobado',
                                'firmado'              => '✍️ Firmado',
                                'activo'               => '🟢 Activo',
                                'terminado'            => '🔴 Terminado',
                                'cancelado'            => '❌ Cancelado',
                            ])->default('borrador'),

                        TextInput::make('lugar_contrato')
                            ->label('Lugar del contrato')->default('Ocaña'),

                        DatePicker::make('fecha_contrato')
                            ->label('Fecha del contrato')->default(now()),

                        TextInput::make('folio_inmobiliario')
                            ->label('Folio inmobiliario')->placeholder('270-XXXX'),
                    ])->columns(2),

                // ── PASO 2: Destinación ──────────────────────────
                Step::make('Uso')
                    ->description('Uso y destinación del inmueble')
                    ->icon('heroicon-o-home')
                    ->schema([
                      
                        Select::make('destinacion')
    ->label('Destinación del inmueble')
    ->options([
        'VIVIENDA FAMILIAR'  => '🏠 Vivienda familiar',
        'LOCAL COMERCIAL'    => '🏢 Local comercial',
        'OFICINA'            => '🏛️ Oficina',
        'BODEGA'             => '📦 Bodega',
        'CONSULTORIO'        => '🏥 Consultorio',
    ])
    ->default(fn (Get $get) => $get('tipo') === 'comercial' ? 'LOCAL COMERCIAL' : 'VIVIENDA FAMILIAR')
    ->required()->columnSpanFull(),

                        TextInput::make('actividad_comercial')
                            ->label('Actividad comercial específica')
                            ->placeholder('Ej: INGENIERÍA Y CONSTRUCCIÓN')
                            ->visible(fn (Get $get) => $get('tipo') === 'comercial')
                            ->columnSpanFull(),

                        Textarea::make('servicios_cargo_arrendatario')
                            ->label('Servicios a cargo del arrendatario')
                            ->rows(2)->columnSpanFull(),
                    ])->columns(2),

                // ── PASO 3: Valores ──────────────────────────────
                Step::make('Valores')
                    ->description('Canon, depósito y garantía')
                    ->icon('heroicon-o-banknotes')
                    ->schema([
                        TextInput::make('canon_mensual')
                            ->label('Canon mensual')->numeric()->prefix('$')->required(),

                        TextInput::make('deposito')
                            ->label('Depósito en garantía')->numeric()->prefix('$')->default(0),

                        TextInput::make('cuota_administracion')
                            ->label('Cuota de administración')->numeric()->prefix('$')->default(0),

                        Select::make('tipo_incremento')
                            ->label('Tipo de incremento')
                            ->options([
                                'ipc_vivienda'    => '📊 IPC Vivienda urbana (Ley 820)',
                                'porcentaje_fijo' => '% Porcentaje fijo',
                            ])
                            ->default('ipc_vivienda')->live(),

                        TextInput::make('porcentaje_incremento')
                            ->label('Porcentaje fijo de incremento')
                            ->numeric()->suffix('%')->default(10)
                            ->visible(fn (Get $get) => $get('tipo_incremento') === 'porcentaje_fijo'),

                        Select::make('tipo_garantia')
                            ->label('Tipo de garantía')
                            ->options([
                                'codeudor'             => '🤝 Codeudor solidario',
                                'garantia_bancaria'    => '🏦 Garantía bancaria',
                                'seguro_arrendamiento' => '🛡️ Seguro Suramericana',
                                'ninguna'              => 'Ninguna',
                            ])->default('codeudor'),
                    ])->columns(2),

                // ── PASO 4: Vigencia con cálculo automático ──────
                Step::make('Vigencia')
                    ->description('Fechas y duración')
                    ->icon('heroicon-o-calendar')
                    ->schema([
                        TextInput::make('duracion_meses')
                            ->label('Duración en meses')
                            ->numeric()->default(6)->live()
                            ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                if ($state && $get('fecha_inicio')) {
                                    $set('fecha_fin', \Carbon\Carbon::parse($get('fecha_inicio'))->addMonths((int)$state)->toDateString());
                                }
                            }),

                        TextInput::make('meses_preaviso')
                            ->label('Meses de preaviso')->numeric()->default(3)
                            ->helperText('Ley 820: vivienda 3 meses · comercial 6 meses'),

                        DatePicker::make('fecha_inicio')
                            ->label('Fecha de inicio')->required()->default(now())->live()
                            ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                if ($state && $get('duracion_meses')) {
                                    $set('fecha_fin', \Carbon\Carbon::parse($state)->addMonths((int)$get('duracion_meses'))->toDateString());
                                }
                            }),

                        DatePicker::make('fecha_fin')
                            ->label('Fecha de terminación')->required()
                            ->helperText('Se calcula automáticamente'),
                    ])->columns(2),

                // ── PASO 5: Deudores (auto desde solicitud) ──────
                Step::make('Terceros')
                    ->description('Deudores solidarios — se cargan desde la solicitud')
                    ->icon('heroicon-o-users')
                    ->schema([
                        \Filament\Forms\Components\Repeater::make('thirds')
                            ->label('Deudores solidarios')
                            ->relationship()
                            ->schema([
                                Select::make('third_id')
                                    ->label('Tercero')
                                    ->relationship('third', 'nombre_completo')
                                    ->searchable()->preload()->required(),

                                Select::make('rol')
                                    ->label('Rol')
                                    ->options([
                                        'deudor_solidario' => '🤝 Deudor solidario',
                                        'fiador'           => '🛡️ Fiador',
                                        'codeudor'         => '💼 Codeudor',
                                    ])->default('deudor_solidario')->required(),

                                TextInput::make('ciudad_expedicion_doc')->label('Ciudad expedición CC'),
                                TextInput::make('direccion_notificacion')->label('Dirección'),
                                TextInput::make('email_notificacion')->label('Correo')->email(),
                                TextInput::make('celular_notificacion')->label('Celular'),
                            ])
                            ->columns(3)
                            ->addActionLabel('+ Agregar deudor solidario')
                            ->defaultItems(0)->collapsible()
                            ->helperText('Los deudores de la solicitud aprobada se agregan aquí — puede agregar más si es necesario')
                            ->itemLabel(fn (array $state) => match($state['rol'] ?? '') {
                                'deudor_solidario' => '🤝 Deudor solidario',
                                'fiador'           => '🛡️ Fiador',
                                'codeudor'         => '💼 Codeudor',
                                default            => 'Nuevo tercero',
                            })
                            ->columnSpanFull(),
                    ]),

                // ── PASO 6: Cláusulas ────────────────────────────
                Step::make('Cláusulas')
                    ->description('Revise y edite las cláusulas')
                    ->icon('heroicon-o-document-check')
                    ->schema([
                        Section::make('Cláusulas del contrato')
                            ->schema([
                                \Filament\Forms\Components\Repeater::make('clauses')
                                    ->label('')
                                    ->relationship()
                                    ->schema([
                                        TextInput::make('numero')->label('N°')->disabled()->columnSpan(1),
                                        TextInput::make('titulo')->label('Título')->disabled()->columnSpan(3),
                                        Select::make('tipo')
                                            ->label('Tipo')
                                            ->options(['clausula'=>'Cláusula','paragrafo'=>'Parágrafo'])
                                            ->disabled()->columnSpan(1),
                                        Textarea::make('contenido_actual')
                                            ->label('Contenido')->rows(4)->columnSpanFull()
                                            ->hint(fn ($record) => $record?->fue_editada ? '⚠️ Modificada' : null)
                                            ->hintColor('warning'),
                                    ])
                                    ->columns(5)->reorderable(false)
                                    ->addable(false)->deletable(false)
                                    ->defaultItems(0)->columnSpanFull(),
                            ]),
                    ]),

            ])->skippable()->columnSpanFull(),
        ]);
    }

    public static function copyClausesFromTemplate(RentalContract $contract): void
    {
        if (!$contract->contract_template_id) return;
        if ($contract->clauses()->count() > 0) return;

        ContractClause::where('contract_template_id', $contract->contract_template_id)
            ->where('is_active', true)->orderBy('orden')->get()
            ->each(fn ($clause) => RentalContractClause::create([
                'rental_contract_id' => $contract->id,
                'contract_clause_id' => $clause->id,
                'numero'             => $clause->numero,
                'titulo'             => $clause->titulo,
                'tipo'               => $clause->tipo,
                'contenido_original' => $clause->contenido,
                'contenido_actual'   => $clause->contenido,
                'fue_editada'        => false,
                'es_editable'        => $clause->es_editable,
                'es_obligatoria'     => $clause->es_obligatoria,
                'orden'              => $clause->orden,
            ]));
    }

    // ── Copiar deudores solidarios desde la solicitud ────────
    public static function copyThirdsFromRequest(RentalContract $contract): void
    {
        if (!$contract->request_id) return;
        if ($contract->thirds()->count() > 0) return;

        $solicitud = Solicitud::with('thirds.third')->find($contract->request_id);
        if (!$solicitud) return;

        // Copiar codeudores/fiadores (no el titular — ese es el arrendatario)
        foreach ($solicitud->thirds->where('rol', '!=', 'titular') as $idx => $t) {
            \App\Models\RentalContractThird::create([
                'rental_contract_id'  => $contract->id,
                'third_id'            => $t->third_id,
                'rol'                 => 'deudor_solidario',
                'celular_notificacion'=> $t->third?->celular ?? null,
                'email_notificacion'  => $t->third?->email ?? null,
                'orden'               => $idx,
            ]);
        }
    }
}

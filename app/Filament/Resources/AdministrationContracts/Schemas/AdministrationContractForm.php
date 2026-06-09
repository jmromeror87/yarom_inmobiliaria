<?php

namespace App\Filament\Resources\AdministrationContracts\Schemas;

use App\Models\AdministrationContract;
use App\Models\AdministrationContractClause;
use App\Models\ContractClause;
use App\Models\ContractTemplate;
use Filament\Forms\Components\DatePicker;
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

class AdministrationContractForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Wizard::make([

                // ── PASO 1: Tipo y partes ────────────────────────
                Step::make('Tipo de Contrato')
                    ->description('Seleccione el tipo y las partes')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Select::make('tipo_contrato')
                            ->label('Tipo de contrato')
                            ->options([
                                'administracion_arriendo' => '🔑 Administración — Arriendo',
                                'administracion_venta'    => '🏷️ Administración — Venta',
                            ])
                            ->default('administracion_arriendo')
                            ->required()
                            ->live()
                            ->helperText(fn (Get $get) =>
                                $get('tipo_contrato') === 'administracion_arriendo'
                                    ? 'El inmueble se entrega para ser arrendado por la inmobiliaria.'
                                    : 'El inmueble se entrega para ser vendido por la inmobiliaria.'
                            ),

                        Select::make('property_id')
                            ->label('Inmueble')
                            ->options(fn ($record) => \App\Models\Property::whereDoesntHave('administrationContracts', fn ($q) =>
                                    $q->whereIn('estado', ['activo', 'firmado', 'en_revision', 'aprobado_gerencia'])
                                      ->when($record?->id, fn ($q2) => $q2->where('id', '!=', $record->id))
                                )
                                ->get()
                                ->mapWithKeys(fn ($p) => [
                                    $p->id => $p->codigo . ' — ' . $p->direccion
                                ])
                            )
                            ->helperText('Solo inmuebles sin contrato de administración activo.')
                            ->searchable()->required()->live()
                            ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                if ($state) {
                                    $property = \App\Models\Property::find($state);
                                    if ($property) {
                                        $set('propietario_id', $property->propietario_id);
                                        if ($get('tipo_contrato') === 'administracion_arriendo') {
                                            $set('canon_pactado', $property->canon_arriendo);
                                        } else {
                                            $set('precio_venta_pactado', $property->precio_venta);
                                        }
                                        // Alerta si el inmueble tiene CTL bloqueado
                                        if ($property->ctl_tiene_limitacion) {
                                            \Filament\Notifications\Notification::make()
                                                ->title('⚠️ Inmueble con limitación jurídica')
                                                ->body('El CTL de este inmueble tiene: ' . $property->ctl_tipo_limitacion . '. Resolver antes de firmar el contrato.')
                                                ->warning()->persistent()->send();
                                        }
                                    }
                                }
                            }),

                        Select::make('propietario_id')
                            ->label('Propietario')
                            ->relationship('propietario', 'nombre_completo')
                            ->searchable()->preload()->required(),

                        Select::make('asesor_id')
                            ->label('Asesor responsable')
                            ->relationship('asesor', 'name')
                            ->searchable()->preload(),

                        Select::make('contract_template_id')
                            ->label('Plantilla de contrato')
                            ->relationship('template', 'nombre')
                            ->default(fn () => ContractTemplate::where('is_default', true)->first()?->id)
                            ->required(),

                        Select::make('estado')
                            ->label('Estado del contrato')
                            ->options([
                                'borrador'            => '📝 Borrador',
                                'enviado_propietario' => '📤 Enviado al propietario',
                                'en_revision'         => '🔍 En revisión propietario',
                                'aprobado_gerencia'   => '✅ Aprobado por gerencia',
                                'enviado_notaria'     => '🏛️ Enviado a notaría',
                                'autenticado_notaria' => '📋 Autenticado en notaría',
                                'firmado'             => '✍️ Firmado',
                                'activo'              => '🟢 Activo',
                                'terminado'           => '🔴 Terminado',
                                'cancelado'           => '❌ Cancelado',
                            ])
                            ->default('borrador')
                            ->helperText(fn (Get $get) => match($get('estado')) {
                                'firmado'  => 'Cambie a Activo para que el inmueble quede disponible para arrendar.',
                                'activo'   => 'El inmueble ya aparece disponible en Solicitudes.',
                                'cancelado'=> 'El inmueble queda disponible nuevamente.',
                                default    => '',
                            })
                            ->live(),

                    ])->columns(2),

                // ── PASO 2: Vigencia ─────────────────────────────
                Step::make('Vigencia')
                    ->description('Fechas y duración del contrato')
                    ->icon('heroicon-o-calendar')
                    ->schema([
                        DatePicker::make('fecha_inicio')
                            ->label('Fecha de inicio')
                            ->required()->default(now())->live()
                            ->helperText('Fecha desde la cual la inmobiliaria administra el inmueble.'),

                        DatePicker::make('fecha_fin')
                            ->label('Fecha de terminación')
                            ->required()
                            ->default(now()->addYear())
                            ->after('fecha_inicio')
                            ->helperText('Debe ser posterior a la fecha de inicio.'),

                        Select::make('renovacion')
                            ->label('Tipo de renovación')
                            ->options([
                                'automatica' => '🔄 Automática — se prorroga si no hay aviso',
                                'manual'     => '✋ Manual — requiere acuerdo entre partes',
                            ])
                            ->default('automatica'),

                        TextInput::make('dias_aviso_terminacion')
                            ->label('Días de aviso para terminación')
                            ->numeric()->default(30)->suffix('días')
                            ->helperText('Días de anticipación para notificar terminación'),

                        DatePicker::make('fecha_firma')
                            ->label('Fecha de firma'),

                        TextInput::make('firmado_por')
                            ->label('Firmado por'),
                    ])->columns(2),

                // ── PASO 3: Condiciones económicas ARRIENDO ──────
                Step::make('Condiciones Económicas')
                    ->description(fn (Get $get) =>
                        $get('tipo_contrato') === 'administracion_venta'
                            ? 'Precio de venta y comisiones'
                            : 'Canon, comisiones y tarifas de arriendo'
                    )
                    ->icon('heroicon-o-banknotes')
                    ->schema([

                        // ── CAMPOS SOLO ARRIENDO ──────────────────
                        TextInput::make('canon_pactado')
                            ->label('Canon mensual de arriendo')
                            ->numeric()->prefix('$')->required()
                            ->helperText('Valor mensual pactado con el propietario')
                            ->visible(fn (Get $get) => $get('tipo_contrato') !== 'administracion_venta'),

                        TextInput::make('comision_porcentaje')
                            ->label('Comisión de administración %')
                            ->numeric()->suffix('%')->default(10)
                            ->helperText('% sobre el canon mensual que cobra la inmobiliaria')
                            ->visible(fn (Get $get) => $get('tipo_contrato') !== 'administracion_venta'),

                        Toggle::make('incluye_administracion')
                            ->label('El canon incluye cuota de administración')
                            ->helperText('Si el propietario asume la cuota de administración del conjunto')
                            ->visible(fn (Get $get) => $get('tipo_contrato') !== 'administracion_venta'),

                        TextInput::make('cuota_administracion_valor')
                            ->label('Valor cuota administración')
                            ->numeric()->prefix('$')->default(0)->minValue(0)
                            ->helperText('0 si no aplica conjunto o edificio.')
                            ->visible(fn (Get $get) => $get('tipo_contrato') !== 'administracion_venta'),

                        // ── CAMPOS SOLO VENTA ─────────────────────
                        TextInput::make('precio_venta_pactado')
                            ->label('Precio de venta pactado')
                            ->numeric()->prefix('$')
                            ->helperText('Precio acordado con el propietario para la venta')
                            ->visible(fn (Get $get) => $get('tipo_contrato') === 'administracion_venta'),

                        TextInput::make('comision_venta_porcentaje')
                            ->label('Comisión por venta %')
                            ->numeric()->suffix('%')->default(3)
                            ->helperText('% del precio de venta que cobra la inmobiliaria')
                            ->visible(fn (Get $get) => $get('tipo_contrato') === 'administracion_venta'),

                        // ── CAMPOS COMUNES ────────────────────────
                        Toggle::make('autoriza_venta')
                            ->label('El propietario autoriza gestión de venta')
                            ->helperText('Autoriza a la inmobiliaria a promover la venta del inmueble.')
                            ->visible(fn (Get $get) => $get('tipo_contrato') !== 'administracion_venta')
                            ->live(),

                        Textarea::make('notas')
                            ->label('Notas y observaciones')
                            ->rows(3)->columnSpanFull(),

                    ])->columns(2),

                // ── PASO 4: Cláusulas ────────────────────────────
                Step::make('Cláusulas del Contrato')
                    ->description('Revise y edite — cada cambio queda registrado con auditoría')
                    ->icon('heroicon-o-document-check')
                    ->schema([
                        Section::make('Cláusulas')
                            ->description('Las cláusulas con borde amarillo han sido editadas. El historial queda guardado automáticamente.')
                            ->schema([
                                \Filament\Forms\Components\Repeater::make('clauses')
                                    ->label('')
                                    ->relationship()
                                    ->schema([
                                        TextInput::make('numero')
                                            ->label('N°')->disabled()->columnSpan(1),
                                        TextInput::make('titulo')
                                            ->label('Título')->disabled()->columnSpan(3),
                                        Select::make('tipo')
                                            ->label('Tipo')
                                            ->options([
                                                'clausula'     => 'Cláusula',
                                                'paragrafo'    => 'Parágrafo',
                                                'considerando' => 'Considerando',
                                                'nota'         => 'Nota',
                                            ])->disabled()->columnSpan(1),
                                        \Filament\Forms\Components\Textarea::make('contenido_actual')
                                            ->label('Contenido')
                                            ->rows(5)
                                            ->columnSpanFull()
                                            ->hint(fn ($record) => $record?->fue_editada
                                                ? '⚠️ Modificada — ' . $record->history()->count() . ' cambio(s)'
                                                : null
                                            )
                                            ->hintColor('warning'),
                                    ])
                                    ->columns(5)
                                    ->reorderable(false)
                                    ->addable(false)
                                    ->deletable(false)
                                    ->defaultItems(0)
                                    ->columnSpanFull(),
                            ]),
                    ]),

            ])->skippable()->columnSpanFull(),
        ]);
    }

    public static function copyClausesFromTemplate(AdministrationContract $contract): void
    {
        if (!$contract->contract_template_id) return;
        if ($contract->clauses()->count() > 0) return;

        $clauses = ContractClause::where('contract_template_id', $contract->contract_template_id)
            ->where('is_active', true)
            ->orderBy('orden')
            ->get();

        foreach ($clauses as $clause) {
            AdministrationContractClause::create([
                'administration_contract_id' => $contract->id,
                'contract_clause_id'         => $clause->id,
                'numero'                     => $clause->numero,
                'titulo'                     => $clause->titulo,
                'tipo'                       => $clause->tipo,
                'contenido_original'         => $clause->contenido,
                'contenido_actual'           => $clause->contenido,
                'fue_editada'                => false,
                'es_editable'                => $clause->es_editable,
                'es_obligatoria'             => $clause->es_obligatoria,
                'orden'                      => $clause->orden,
            ]);
        }
    }
}

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
| Archivo: RequestForm.php
| Fecha: CURRENT_DAY/05/2026
| Versión: v1.0
|--------------------------------------------------------------------------
*/
        

namespace App\Filament\Resources\Requests\Schemas;

use App\Models\Property;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;

class RequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Wizard::make([

                // ── PASO 1: Datos generales ──────────────────────
                Step::make('Solicitud')
                    ->description('Tipo, inmueble y responsable')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->schema([
                        TextInput::make('numero')
                            ->label('N° Solicitud')
                            ->disabled()->placeholder('Auto: SOL-2026-0001'),

                        Select::make('tipo')
                            ->label('Tipo de solicitud')
                            ->options([
                                'estudio_propietario'  => '🏠 Análisis a Propietario — Captación inmueble',
                                'estudio_arrendatario' => '🔑 Estudio arrendatario — Candidato arriendo',
                                'estudio_comprador'    => '🛒 Análisis a Comprador — Candidato compra',
                            ])
                            ->default('estudio_arrendatario')
                            ->required()->live()
                            ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                if ($state === 'estudio_propietario' && $get('property_id')) {
                                    $p = Property::find($get('property_id'));
                                    if ($p?->propietario_id) {
                                        $set('thirds', [[
                                            'third_id'            => $p->propietario_id,
                                            'rol'                 => 'propietario',
                                            'resultado_individual'=> 'pendiente',
                                        ]]);
                                    }
                                }
                            })
                            ->helperText(fn (Get $get) => match($get('tipo')) {
                                'estudio_propietario'  => 'Al aprobar → gerencia pacta contrato de administración. El inmueble pasa a DISPONIBLE cuando el contrato de administración se active.',
                                'estudio_arrendatario' => 'Al aprobar → candidato habilitado para firmar contrato de arrendamiento. El inmueble pasa a ARRENDADO cuando el contrato de arrendamiento quede activo.',
                                'estudio_comprador'    => 'Al aprobar → candidato habilitado para proceder con la compra. El estado del inmueble cambia cuando se formalice la negociación.',
                                default => '',
                            }),

                        Select::make('property_id')
                            ->label('Inmueble')
                            ->options(fn () => Property::with('tipo')
                                ->get()
                                ->mapWithKeys(fn ($p) => [
                                    $p->id => $p->codigo . ' — ' . $p->direccion
                                ])
                            )
                            ->searchable()->required()->live()
                            ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                if ($state) {
                                    $p = Property::find($state);
                                    if ($p) {
                                        $set('canon_evaluar', $p->canon_arriendo);
                                        $set('precio_venta_evaluar', $p->precio_venta);
                                        if ($get('tipo') === 'estudio_propietario' && $p->propietario_id) {
                                            $set('thirds', [[
                                                'third_id'            => $p->propietario_id,
                                                'rol'                 => 'propietario',
                                                'resultado_individual'=> 'pendiente',
                                            ]]);
                                        }
                                    }
                                }
                            }),

                        Select::make('asesor_id')
                            ->label('Asesor responsable')
                            ->relationship('asesor', 'name')
                            ->searchable()->preload(),

                        Select::make('estado')
                            ->label('Estado de la solicitud')
                            ->options([
                                'radicada'    => '📋 Radicada — En espera de estudio',
                                'en_estudio'  => '🔍 En estudio — Análisis en curso',
                                'aprobada'    => '✅ Aprobada — Genera contrato',
                                'condicional' => '⚠️ Condicional — Con requisitos adicionales',
                                'rechazada'   => '❌ Rechazada — No cumple requisitos',
                                'desistida'   => '🚫 Desistida — Candidato retiró solicitud',
                            ])->default('radicada')->required()->disabled(fn ($record) => $record ?? false && in_array($record->estado, ['aprobada','rechazada','desistida'])),

                        DatePicker::make('fecha_radicacion')
                            ->label('Fecha de radicación')->default(now()),
                    ])->columns(2),

                // ── PASO 2: Valores a evaluar ────────────────────
                Step::make('Valores')
                    ->description('Canon o precio a evaluar')
                    ->icon('heroicon-o-banknotes')
                    ->schema([
                        TextInput::make('canon_evaluar')
                            ->label('Canon mensual a evaluar')
                            ->numeric()->prefix('$')
                            ->helperText('Valor del canon que pagaría el arrendatario')
                            ->visible(fn (Get $get) => $get('tipo') !== 'estudio_comprador'),

                        TextInput::make('precio_venta_evaluar')
                            ->label('Precio de venta a evaluar')
                            ->numeric()->prefix('$')
                            ->helperText('Precio de venta del inmueble')
                            ->visible(fn (Get $get) => $get('tipo') === 'estudio_comprador'),
                    ])->columns(2),

                // ── PASO 3: Terceros de la solicitud ─────────────
                Step::make('Terceros')
                    ->description('Candidatos y sus roles')
                    ->icon('heroicon-o-users')
                    ->schema([
                        \Filament\Forms\Components\Repeater::make('thirds')
                            ->label(fn (Get $get) => $get('tipo') === 'estudio_propietario'
                                ? '🏠 Propietario del inmueble'
                                : 'Terceros vinculados a la solicitud'
                            )
                            ->relationship()
                            ->addable(fn (Get $get) => $get('tipo') !== 'estudio_propietario')
                            ->deletable(fn (Get $get) => $get('tipo') !== 'estudio_propietario')
                            ->schema([
                                Select::make('third_id')
                                    ->label('Tercero')
                                    ->relationship('third', 'nombre_completo')
                                    ->searchable()->preload()->required()
                                    ->disabled(fn (Get $get) => $get('../../tipo') === 'estudio_propietario')
                                    ->dehydrated(),

                                Select::make('rol')
                                    ->label('Rol en la solicitud')
                                    ->options([
                                        'titular'       => '👤 Titular principal',
                                        'codeudor'      => '🤝 Codeudor solidario',
                                        'fiador'        => '🛡️ Fiador personal',
                                        'propietario'   => '🏠 Propietario',
                                        'representante' => '💼 Representante legal',
                                    ])->default('titular')->required()
                                    ->disabled(fn (Get $get) => $get('../../tipo') === 'estudio_propietario')
                                    ->dehydrated(),

                                TextInput::make('ingresos_declarados')
                                    ->label('Ingresos declarados ($)')
                                    ->numeric()->prefix('$')
                                    ->hidden(fn (Get $get) => $get('../../tipo') === 'estudio_propietario'),

                                TextInput::make('ingresos_verificados')
                                    ->label('Ingresos verificados ($)')
                                    ->numeric()->prefix('$')
                                    ->hidden(fn (Get $get) => $get('../../tipo') === 'estudio_propietario'),

                                TextInput::make('score_datacredito')
                                    ->label('Score DataCrédito')
                                    ->numeric()
                                    ->helperText('Puntaje de centrales de riesgo')
                                    ->hidden(fn (Get $get) => $get('../../tipo') === 'estudio_propietario'),

                                Toggle::make('reporte_negativo')
                                    ->label('Reporte negativo en centrales')
                                    ->hidden(fn (Get $get) => $get('../../tipo') === 'estudio_propietario'),

                                Select::make('resultado_individual')
                                    ->label('Resultado individual')
                                    ->options([
                                        'pendiente'   => 'Pendiente',
                                        'aprobado'    => '✅ Aprobado',
                                        'condicional' => '⚠️ Condicional',
                                        'rechazado'   => '❌ Rechazado',
                                    ])->default('pendiente'),

                                Textarea::make('notas_evaluacion')
                                    ->label('Notas de evaluación')
                                    ->rows(2)->columnSpanFull(),
                            ])
                            ->columns(3)
                            ->addActionLabel('+ Agregar tercero')
                            ->defaultItems(fn (Get $get) => $get('tipo') === 'estudio_propietario' ? 0 : 1)
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string =>
                                isset($state['rol']) ? match($state['rol']) {
                                    'titular'       => '👤 Titular',
                                    'codeudor'      => '🤝 Codeudor',
                                    'fiador'        => '🛡️ Fiador',
                                    'propietario'   => '🏠 Propietario',
                                    'representante' => '💼 Representante',
                                    default => 'Tercero',
                                } : 'Nuevo tercero'
                            )
                            ->columnSpanFull(),
                    ]),

                // ── PASO 4: Documentos ────────────────────────────
                Step::make('Documentos')
                    ->description('Documentos requeridos para el estudio')
                    ->icon('heroicon-o-document-check')
                    ->schema([
                        \Filament\Forms\Components\Repeater::make('documents')
                            ->label('Documentos del estudio')
                            ->relationship()
                            ->mutateRelationshipDataBeforeCreateUsing(function (array $data): ?array {
                                \Illuminate\Support\Facades\Log::debug('DOC_CREATE', [
                                    'keys' => array_keys($data),
                                    'request_third_id' => $data['request_third_id'] ?? 'KEY_MISSING',
                                    'path_empty' => empty($data['path']),
                                ]);
                                if (empty($data['path'])) return null;
                                if (!empty($data['request_third_id']) && !is_numeric($data['request_third_id'])) {
                                    $data['request_third_id'] = null;
                                }
                                return $data;
                            })
                            ->mutateRelationshipDataBeforeSaveUsing(function (array $data): ?array {
                                \Illuminate\Support\Facades\Log::debug('DOC_SAVE', [
                                    'keys' => array_keys($data),
                                    'request_third_id' => $data['request_third_id'] ?? 'KEY_MISSING',
                                    'path_empty' => empty($data['path']),
                                ]);
                                if (empty($data['path'])) return null;
                                if (!empty($data['request_third_id']) && !is_numeric($data['request_third_id'])) {
                                    $data['request_third_id'] = null;
                                }
                                return $data;
                            })
                            ->schema([
                                Select::make('tipo_documento')
                                    ->label('Tipo de documento')
                                    ->options([
                                        'cedula'                  => '🪪 Cédula de ciudadanía',
                                        'desprendible_nomina'     => '💰 Desprendible de nómina',
                                        'extracto_bancario'       => '🏦 Extracto bancario',
                                        'certificado_ingresos'    => '📄 Certificado de ingresos',
                                        'declaracion_renta'       => '📊 Declaración de renta',
                                        'carta_laboral'           => '✉️ Carta laboral',
                                        'camara_comercio'         => '🏢 Cámara de comercio',
                                        'rut'                     => '📋 RUT',
                                        'referencia_personal'     => '👥 Referencia personal',
                                        'referencia_comercial'    => '🤝 Referencia comercial',
                                        'otro'                    => '📎 Otro documento',
                                    ])->required()->live(),

                                Select::make('estado_documento')
                                    ->label('Estado')
                                    ->options([
                                        'pendiente'  => '⏳ Pendiente',
                                        'recibido'   => '📥 Recibido',
                                        'verificado' => '✅ Verificado',
                                        'rechazado'  => '❌ Rechazado',
                                    ])->default('pendiente')->live(),

                                Select::make('request_third_id')
                                    ->label('Pertenece a')
                                    ->placeholder('— Sin asignar —')
                                    ->native(false)
                                    ->options(function ($livewire) {
                                        $record = $livewire->record ?? null;
                                        if (!$record?->id) return [];
                                        return \App\Models\RequestThird::with('third')
                                            ->where('request_id', $record->id)
                                            ->get()
                                            ->mapWithKeys(function ($rt) {
                                                $rol = match($rt->rol) {
                                                    'titular'       => 'Titular',
                                                    'codeudor'      => 'Codeudor',
                                                    'fiador'        => 'Fiador',
                                                    'propietario'   => 'Propietario',
                                                    'representante' => 'Representante',
                                                    default         => ucfirst($rt->rol),
                                                };
                                                return [$rt->id => ($rt->third->nombre_completo ?? '?') . ' — ' . $rol];
                                            });
                                    }),

                                FileUpload::make('path')
                                    ->label('Archivo')
                                    ->disk('public')
                                    ->directory('solicitudes/documentos')
                                    ->acceptedFileTypes(['application/pdf','image/jpeg','image/png','image/jpg'])
                                    ->maxSize(10240)
                                    ->required()
                                    ->downloadable()->openable()
                                    ->columnSpanFull(),

                                Textarea::make('notas')
                                    ->label('Notas')->rows(2)->columnSpanFull(),
                            ])
                            ->columns(3)
                            ->addActionLabel('+ Agregar documento')
                            ->defaultItems(0)
                            ->collapsible()
                            ->itemLabel(function (array $state): string {
                                $tipos = [
                                    'cedula'               => '🪪 Cédula',
                                    'desprendible_nomina'  => '💰 Desprendible nómina',
                                    'extracto_bancario'    => '🏦 Extracto bancario',
                                    'certificado_ingresos' => '📄 Cert. ingresos',
                                    'declaracion_renta'    => '📊 Declaración renta',
                                    'carta_laboral'        => '✉️ Carta laboral',
                                    'camara_comercio'      => '🏢 Cámara comercio',
                                    'rut'                  => '📋 RUT',
                                    'referencia_personal'  => '👥 Ref. personal',
                                    'referencia_comercial' => '🤝 Ref. comercial',
                                    'otro'                 => '📎 Otro',
                                ];
                                $estados = [
                                    'pendiente'  => '⏳ Pendiente',
                                    'recibido'   => '📥 Recibido',
                                    'verificado' => '✅ Verificado',
                                    'rechazado'  => '❌ Rechazado',
                                ];
                                $tipo   = $tipos[$state['tipo_documento'] ?? ''] ?? 'Documento';
                                $estado = $estados[$state['estado_documento'] ?? ''] ?? '';
                                $nombre = '';
                                if (!empty($state['request_third_id']) && is_numeric($state['request_third_id'])) {
                                    $rt = \App\Models\RequestThird::with('third')->find($state['request_third_id']);
                                    $nombre = $rt?->third?->nombre_completo ?? '';
                                }
                                return $tipo
                                    . ($estado ? '  —  ' . $estado : '')
                                    . ($nombre ? '  —  ' . $nombre : '');
                            })
                            ->columnSpanFull(),
                    ]),

                // ── PASO 5: Decisión final ───────────────────────
                Step::make('Decisión')
                    ->description('Concepto y resultado final')
                    ->icon('heroicon-o-check-badge')
                    ->schema([
                        Select::make('estado')
                            ->label('Decisión final')
                            ->options([
                                'radicada'    => '📋 Radicada',
                                'en_estudio'  => '🔍 En estudio',
                                'aprobada'    => '✅ Aprobada',
                                'condicional' => '⚠️ Condicional',
                                'rechazada'   => '❌ Rechazada',
                                'desistida'   => '🚫 Desistida',
                            ])->required()
                            ->helperText('La aprobación habilita al candidato. El estado del inmueble cambia al activar el contrato correspondiente.'),

                        DatePicker::make('fecha_decision')
                            ->label('Fecha de decisión'),

                        TextInput::make('decidido_por')
                            ->label('Decidido por'),

                        Textarea::make('concepto_evaluacion')
                            ->label('Concepto de evaluación')
                            ->rows(4)->columnSpanFull(),

                        Textarea::make('condiciones_especiales')
                            ->label('Condiciones especiales (si aplica)')
                            ->rows(3)->columnSpanFull()
                            ->helperText('Solo para solicitudes condicionales'),

                        Textarea::make('notas')
                            ->label('Notas adicionales')
                            ->rows(2)->columnSpanFull(),

                    ])->columns(2),

            ])->skippable()->columnSpanFull(),
        ]);
    }
}

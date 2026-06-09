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
                                'estudio_arrendatario' => '🔑 Estudio arrendatario — Candidato arriendo',
                                'estudio_comprador'    => '🛒 Análisis a Comprador — Candidato compra',
                            ])
                            ->default('estudio_arrendatario')
                            ->required()->live()
                            ->helperText(fn (Get $get) => match($get('tipo')) {
                                'estudio_arrendatario' => 'Al aprobar → candidato habilitado para firmar contrato de arrendamiento.',
                                'estudio_comprador'    => 'Al aprobar → candidato habilitado para proceder con la compra.',
                                default => '',
                            }),

                        Select::make('tipo_aprobacion')
                            ->label('¿Quién realiza el estudio?')
                            ->options([
                                'sura'    => '🏢 SURA — Aseguradora realiza el estudio',
                                'directo' => '👤 Directo — Gerencia decide internamente',
                            ])
                            ->default('directo')
                            ->required()->live()
                            ->helperText(fn (Get $get) => match($get('tipo_aprobacion')) {
                                'sura'    => 'Se enviará un paquete completo de documentos a SURA. Ellos responden con aprobación, rechazo o condicional.',
                                'directo' => 'La gerencia revisa los documentos y toma la decisión internamente.',
                                default   => '',
                            }),

                        TextInput::make('tarifa_estudio_cobrada')
                            ->label('Tarifa del estudio ($)')
                            ->numeric()->prefix('$')
                            ->helperText('Valor cobrado al candidato por el estudio socioeconómico.')
                            ->visible(fn (Get $get) => $get('tipo_aprobacion') === 'directo'),

                        Select::make('property_id')
                            ->label('Inmueble')
                            ->options(fn (Get $get) => Property::with(['tipo', 'administrationContracts'])
                                ->whereHas('administrationContracts', fn ($q) =>
                                    $q->whereIn('estado', ['activo', 'firmado'])
                                )
                                ->when($get('tipo') === 'estudio_arrendatario', fn ($q) =>
                                    $q->where('disponible_arriendo', true)
                                      ->whereIn('estado', ['disponible'])
                                )
                                ->when($get('tipo') === 'estudio_comprador', fn ($q) =>
                                    $q->where('disponible_venta', true)
                                      ->whereIn('estado', ['disponible', 'en_venta'])
                                )
                                ->get()
                                ->mapWithKeys(fn ($p) => [
                                    $p->id => $p->codigo . ' — ' . $p->direccion . ' (' . ($p->tipo?->nombre ?? '') . ')'
                                ])
                            )
                            ->helperText('Solo aparecen inmuebles con contrato de administración activo y disponibles según el tipo de solicitud.')
                            ->searchable()->required()->live()
                            ->afterStateUpdated(function (Set $set, $state) {
                                if ($state) {
                                    $p = Property::find($state);
                                    if ($p) {
                                        $set('canon_evaluar', $p->canon_arriendo);
                                        $set('precio_venta_evaluar', $p->precio_venta);
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
                            ->label('Terceros vinculados a la solicitud')
                            ->relationship()
                            ->addable()
                            ->deletable()
                            ->schema([
                                // ── Siempre visibles ──────────────
                                Select::make('third_id')
                                    ->label('Tercero')
                                    ->relationship('third', 'nombre_completo')
                                    ->searchable()->preload()->required()
                                    ->dehydrated()
                                    ->helperText('Busque por nombre o cédula.'),

                                Select::make('rol')
                                    ->label('Rol')
                                    ->options([
                                        'titular'       => '👤 Titular principal',
                                        'codeudor'      => '🤝 Codeudor solidario',
                                        'fiador'        => '🛡️ Fiador personal',
                                        'representante' => '💼 Representante legal',
                                    ])->default('titular')->required()
                                    ->dehydrated(),

                                TextInput::make('ingresos_declarados')
                                    ->label('Ingresos declarados ($)')
                                    ->numeric()->prefix('$')
                                    ->helperText('Se incluye en el paquete enviado a SURA o en el análisis de gerencia.'),

                                // ── Solo DIRECTO — gerencia los analiza ──
                                TextInput::make('ingresos_verificados')
                                    ->label('Ingresos verificados ($)')
                                    ->numeric()->prefix('$')
                                    ->helperText('Ingresos comprobados con documentos.')
                                    ->visible(fn (Get $get) => $get('../../tipo_aprobacion') === 'directo'),

                                TextInput::make('score_datacredito')
                                    ->label('Score DataCrédito')
                                    ->numeric()
                                    ->helperText('Puntaje consultado en centrales de riesgo.')
                                    ->visible(fn (Get $get) => $get('../../tipo_aprobacion') === 'directo'),

                                Toggle::make('reporte_negativo')
                                    ->label('Reporte negativo en centrales')
                                    ->visible(fn (Get $get) => $get('../../tipo_aprobacion') === 'directo'),

                                // ── Resultado individual ───────────
                                Select::make('resultado_individual')
                                    ->label('Resultado')
                                    ->options([
                                        'pendiente'   => '⏳ Pendiente',
                                        'aprobado'    => '✅ Aprobado',
                                        'condicional' => '⚠️ Condicional',
                                        'rechazado'   => '❌ Rechazado',
                                    ])->default('pendiente'),

                                Textarea::make('notas_evaluacion')
                                    ->label('Notas')
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

                        // ── CAMPOS COMUNES ────────────────────────
                        Select::make('estado')
                            ->label('Estado / Decisión')
                            ->options([
                                'radicada'       => '📋 Radicada',
                                'en_estudio'     => '🔍 En estudio',
                                'aprobada'       => '✅ Aprobada (SURA)',
                                'aprobada_gerente'=> '✅ Aprobada (Gerencia)',
                                'condicional'    => '⚠️ Condicional',
                                'rechazada'      => '❌ Rechazada',
                                'desistida'      => '🚫 Desistida',
                            ])
                            ->required()->live()
                            ->helperText('La aprobación habilita al candidato para firmar contrato.'),

                        DatePicker::make('fecha_decision')
                            ->label('Fecha de decisión'),

                        // ── DIRECTO — campos gerencia ─────────────
                        TextInput::make('decidido_por')
                            ->label('Gerente que decide')
                            ->placeholder('Yaneth Pérez')
                            ->visible(fn (Get $get) => $get('tipo_aprobacion') === 'directo'),

                        Textarea::make('justificacion_gerente')
                            ->label('Justificación del gerente')
                            ->rows(3)->columnSpanFull()
                            ->helperText('Análisis y argumentos que soportan la decisión.')
                            ->visible(fn (Get $get) => $get('tipo_aprobacion') === 'directo'),

                        Textarea::make('concepto_evaluacion')
                            ->label('Concepto de evaluación')
                            ->rows(3)->columnSpanFull()
                            ->visible(fn (Get $get) => $get('tipo_aprobacion') === 'directo'),

                        // ── SURA — campos aseguradora ─────────────
                        TextInput::make('numero_solicitud_sura')
                            ->label('N° Solicitud SURA')
                            ->placeholder('SURA-2026-XXXX')
                            ->helperText('Número asignado por SURA al recibir el paquete.')
                            ->visible(fn (Get $get) => $get('tipo_aprobacion') === 'sura'),

                        TextInput::make('analista_sura')
                            ->label('Analista SURA')
                            ->placeholder('Nombre del analista que respondió')
                            ->visible(fn (Get $get) => $get('tipo_aprobacion') === 'sura'),

                        DatePicker::make('fecha_respuesta_sura')
                            ->label('Fecha respuesta SURA')
                            ->visible(fn (Get $get) => $get('tipo_aprobacion') === 'sura'),

                        FileUpload::make('path_respuesta_sura')
                            ->label('Respuesta SURA (PDF)')
                            ->disk('public')->directory('solicitudes/sura')
                            ->acceptedFileTypes(['application/pdf'])->maxSize(10240)
                            ->downloadable()->openable()
                            ->helperText('Subir el documento oficial de respuesta de SURA.')
                            ->visible(fn (Get $get) => $get('tipo_aprobacion') === 'sura')
                            ->columnSpanFull(),

                        Textarea::make('observaciones_sura')
                            ->label('Observaciones SURA')
                            ->rows(3)->columnSpanFull()
                            ->visible(fn (Get $get) => $get('tipo_aprobacion') === 'sura'),

                        // ── CONDICIONAL — aplica a ambos ──────────
                        Textarea::make('condiciones_especiales')
                            ->label('Condiciones especiales')
                            ->rows(3)->columnSpanFull()
                            ->helperText('Requisitos adicionales que debe cumplir el candidato.')
                            ->visible(fn (Get $get) => $get('estado') === 'condicional'),

                        Textarea::make('notas')
                            ->label('Notas adicionales')
                            ->rows(2)->columnSpanFull(),

                    ])->columns(2),

            ])->skippable()->columnSpanFull(),
        ]);
    }
}

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
use Filament\Schemas\Components\Section;
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
                                'estudio_propietario'  => '🏠 Estudio propietario — Captación inmueble',
                                'estudio_arrendatario' => '🔑 Estudio arrendatario — Candidato arriendo',
                                'estudio_comprador'    => '🛒 Estudio comprador — Candidato compra',
                            ])
                            ->default('estudio_arrendatario')
                            ->required()->live()
                            ->helperText(fn (Get $get) => match($get('tipo')) {
                                'estudio_propietario'  => 'Si se aprueba → el inmueble pasa a DISPONIBLE automáticamente.',
                                'estudio_arrendatario' => 'Si se aprueba → el inmueble pasa a ARRENDADO automáticamente.',
                                'estudio_comprador'    => 'Si se aprueba → el inmueble pasa a EN VENTA automáticamente.',
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
                            ->schema([
                                Select::make('third_id')
                                    ->label('Tercero')
                                    ->relationship('third', 'nombre_completo')
                                    ->searchable()->preload()->required(),

                                Select::make('rol')
                                    ->label('Rol en la solicitud')
                                    ->options([
                                        'titular'       => '👤 Titular principal',
                                        'codeudor'      => '🤝 Codeudor solidario',
                                        'fiador'        => '🛡️ Fiador personal',
                                        'propietario'   => '🏠 Propietario',
                                        'representante' => '💼 Representante legal',
                                    ])->default('titular')->required(),

                                TextInput::make('ingresos_declarados')
                                    ->label('Ingresos declarados ($)')
                                    ->numeric()->prefix('$'),

                                TextInput::make('ingresos_verificados')
                                    ->label('Ingresos verificados ($)')
                                    ->numeric()->prefix('$'),

                                TextInput::make('score_datacredito')
                                    ->label('Score DataCrédito')
                                    ->numeric()
                                    ->helperText('Puntaje de centrales de riesgo'),

                                Toggle::make('reporte_negativo')
                                    ->label('Reporte negativo en centrales'),

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
                            ->defaultItems(1)
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
                                    ])->required(),

                                Select::make('estado_documento')
                                    ->label('Estado')
                                    ->options([
                                        'pendiente'  => '⏳ Pendiente',
                                        'recibido'   => '📥 Recibido',
                                        'verificado' => '✅ Verificado',
                                        'rechazado'  => '❌ Rechazado',
                                    ])->default('pendiente'),

                                FileUpload::make('path')
                                    ->label('Archivo')
                                    ->disk('public')
                                    ->directory('solicitudes/documentos')
                                    ->acceptedFileTypes(['application/pdf','image/jpeg','image/png','image/jpg'])
                                    ->maxSize(10240)
                                    ->downloadable()->openable()
                                    ->columnSpanFull(),

                                Textarea::make('notas')
                                    ->label('Notas')->rows(2)->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->addActionLabel('+ Agregar documento')
                            ->defaultItems(0)
                            ->collapsible()
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
                            ->helperText('Al aprobar se actualizará el estado del inmueble automáticamente'),

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

                        Section::make('Automatización de estado del inmueble')
                            ->icon('heroicon-o-arrow-path')
                            ->schema([
                                Select::make('estado_inmueble_anterior')
                                    ->label('Estado anterior del inmueble')
                                    ->disabled()
                                    ->options([
                                        'en_captacion'          => 'En captación',
                                        'documentos_pendientes' => 'Documentos pendientes',
                                        'disponible'            => 'Disponible',
                                        'arrendado'             => 'Arrendado',
                                        'en_venta'              => 'En venta',
                                        'vendido'               => 'Vendido',
                                        'en_mantenimiento'      => 'En mantenimiento',
                                        'inactivo'              => 'Inactivo',
                                    ]),
                                Select::make('estado_inmueble_nuevo')
                                    ->label('Nuevo estado del inmueble')
                                    ->disabled()
                                    ->options([
                                        'en_captacion'          => 'En captación',
                                        'documentos_pendientes' => 'Documentos pendientes',
                                        'disponible'            => 'Disponible',
                                        'arrendado'             => 'Arrendado',
                                        'en_venta'              => 'En venta',
                                        'vendido'               => 'Vendido',
                                        'en_mantenimiento'      => 'En mantenimiento',
                                        'inactivo'              => 'Inactivo',
                                    ]),
                                Toggle::make('cambio_estado_aplicado')
                                    ->label('Cambio de estado aplicado')
                                    ->disabled(),
                            ])->columns(3)->collapsible(),
                    ])->columns(2),

            ])->skippable()->columnSpanFull(),
        ]);
    }
}

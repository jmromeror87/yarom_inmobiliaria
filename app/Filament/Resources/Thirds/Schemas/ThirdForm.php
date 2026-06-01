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
| Archivo: ThirdForm.php
| Fecha: CURRENT_DAY/05/2026
| Versión: v1.0
|--------------------------------------------------------------------------
*/


namespace App\Filament\Resources\Thirds\Schemas;

use App\Models\Departamento;
use App\Models\Municipio;
use App\Forms\Components\MapboxAddressInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;

class ThirdForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Wizard::make([

                Step::make('Roles y Tipo')
                    ->description('¿Qué rol tiene este tercero?')
                    ->icon('heroicon-o-tag')
                    ->schema([
                        Toggle::make('es_propietario')->live()
                            ->label('Es propietario de inmueble')
                            ->helperText('Entrega inmuebles para administración o venta'),
                        Toggle::make('es_arrendatario')->live()
                            ->label('Es arrendatario')
                            ->helperText('Toma inmuebles en arriendo'),
                        Toggle::make('es_cliente_compra')->live()
                            ->label('Es cliente comprador')
                            ->helperText('Interesado en compra de inmueble'),
                        Toggle::make('es_fiador')->live()
                            ->label('Es fiador / codeudor')
                            ->helperText('Respalda a un arrendatario'),
                        Toggle::make('es_proveedor')->live()
                            ->label('Es proveedor')
                            ->helperText('Presta servicios a la inmobiliaria'),

                        Select::make('tipo_persona')
                            ->label('Tipo de persona')
                            ->options(['natural' => 'Persona Natural', 'juridica' => 'Persona Jurídica'])
                            ->default('natural')->required()->live(),

                        Select::make('tipo_documento')
                            ->label('Tipo de documento')
                            ->options([
                                'CC'       => 'Cédula de Ciudadanía',
                                'CE'       => 'Cédula de Extranjería',
                                'NIT'      => 'NIT',
                                'Pasaporte'=> 'Pasaporte',
                                'TI'       => 'Tarjeta de Identidad',
                                'PEP'      => 'PEP (Permiso Especial de Permanencia)',
                                'PPT'      => 'PPT (Permiso por Protección Temporal)',
                            ])->default('CC')->required(),

                        TextInput::make('numero_documento')
                            ->label('Número de documento')
                            ->required()->unique('thirds', 'numero_documento', ignoreRecord: true),

                        Select::make('fuente_captacion')
                            ->label('Fuente de captación')
                            ->options([
                                'referido'             => 'Referido',
                                'web'                  => 'Sitio web',
                                'redes_sociales'       => 'Redes sociales',
                                'portales_inmobiliarios'=> 'Portales inmobiliarios',
                                'voz_a_voz'            => 'Voz a voz',
                                'llamada'              => 'Llamada entrante',
                                'visita_directa'       => 'Visita directa',
                                'otro'                 => 'Otro',
                            ]),

                        Select::make('asesor_id')
                            ->label('Asesor asignado')
                            ->relationship('asesor', 'name')
                            ->searchable()->preload(),
                    ])->columns(2),

                Step::make('Datos Personales')
                    ->description('Información de identificación')
                    ->icon('heroicon-o-identification')
                    ->schema([
                        TextInput::make('primer_nombre')->label('Primer nombre')
                            ->visible(fn (Get $get) => $get('tipo_persona') === 'natural'),
                        TextInput::make('segundo_nombre')->label('Segundo nombre')
                            ->visible(fn (Get $get) => $get('tipo_persona') === 'natural'),
                        TextInput::make('primer_apellido')->label('Primer apellido')
                            ->visible(fn (Get $get) => $get('tipo_persona') === 'natural'),
                        TextInput::make('segundo_apellido')->label('Segundo apellido')
                            ->visible(fn (Get $get) => $get('tipo_persona') === 'natural'),

                        TextInput::make('razon_social')->label('Razón social')
                            ->visible(fn (Get $get) => $get('tipo_persona') === 'juridica'),
                        TextInput::make('nombre_comercial')->label('Nombre comercial')
                            ->visible(fn (Get $get) => $get('tipo_persona') === 'juridica'),

                        Select::make('genero')
                            ->label('Género')
                            ->options(['masculino' => 'Masculino', 'femenino' => 'Femenino', 'otro' => 'Otro'])
                            ->visible(fn (Get $get) => $get('tipo_persona') === 'natural'),

                        Select::make('estado_civil')
                            ->label('Estado civil')
                            ->options([
                                'soltero'     => 'Soltero(a)',
                                'casado'      => 'Casado(a)',
                                'union_libre' => 'Unión libre',
                                'divorciado'  => 'Divorciado(a)',
                                'viudo'       => 'Viudo(a)',
                                'separado'    => 'Separado(a)',
                            ])->visible(fn (Get $get) => $get('tipo_persona') === 'natural'),

                        DatePicker::make('fecha_nacimiento')->label('Fecha de nacimiento')
                            ->visible(fn (Get $get) => $get('tipo_persona') === 'natural'),
                        TextInput::make('lugar_nacimiento')->label('Lugar de nacimiento')
                            ->visible(fn (Get $get) => $get('tipo_persona') === 'natural'),
                        TextInput::make('nacionalidad')->label('Nacionalidad')->default('Colombiana'),
                    ])->columns(2),

                Step::make('Contacto')
                    ->description('Teléfonos y correos')
                    ->icon('heroicon-o-phone')
                    ->schema([
                        TextInput::make('celular')
                            ->label('Celular principal')->placeholder('+57 300 0000000'),
                        TextInput::make('celular_alt')
                            ->label('Celular alternativo')->placeholder('+57 310 0000000'),
                        TextInput::make('telefono_fijo')
                            ->label('Teléfono fijo')->placeholder('+57 601 0000000'),
                        TextInput::make('whatsapp')
                            ->label('WhatsApp')->placeholder('+57 300 0000000'),
                        TextInput::make('email')
                            ->label('Correo electrónico principal')->email(),
                        TextInput::make('email_alt')
                            ->label('Correo alternativo')->email(),
                    ])->columns(2),

                Step::make('Dirección')
                    ->description('Lugar de residencia')
                    ->icon('heroicon-o-map-pin')
                    ->schema([
                        Select::make('pais_id')->label('País')
                            ->relationship('pais', 'nombre')->default(1)->live(),

                        Select::make('departamento_id')->label('Departamento')
                            ->options(fn (Get $get) =>
                                Departamento::where('pais_id', $get('pais_id') ?? 1)
                                    ->orderBy('nombre')->pluck('nombre', 'id')
                            )->searchable()->live()
                            ->afterStateUpdated(fn (Set $set) => $set('municipio_id', null)),

                        Select::make('municipio_id')->label('Municipio')
                            ->options(fn (Get $get) =>
                                Municipio::where('departamento_id', $get('departamento_id'))
                                    ->orderBy('nombre')->pluck('nombre', 'id')
                            )->searchable(),

                        MapboxAddressInput::make('direccion_residencia')
                            ->label('Dirección de residencia')
                            ->placeholder('Calle 123 # 45 - 67')
                            ->columnSpanFull(),
                        TextInput::make('barrio_residencia')->label('Barrio'),
                        TextInput::make('codigo_postal')->label('Código postal'),
                    ])->columns(2),

                Step::make('Información Laboral')
                    ->description('Empleo e ingresos')
                    ->icon('heroicon-o-briefcase')
                    ->visible(fn (Get $get) => !$get('es_propietario') && !$get('es_cliente_compra'))
                    ->schema([
                        Select::make('tipo_empleo')->label('Tipo de empleo')
                            ->options([
                                'dependiente'   => 'Empleado dependiente',
                                'independiente' => 'Independiente / Empresario',
                                'pensionado'    => 'Pensionado',
                                'rentista'      => 'Rentista de capital',
                                'desempleado'   => 'Desempleado',
                                'otro'          => 'Otro',
                            ]),
                        TextInput::make('empresa_donde_trabaja')->label('Empresa donde trabaja'),
                        TextInput::make('cargo')->label('Cargo'),
                        TextInput::make('telefono_empresa')->label('Teléfono empresa'),
                        MapboxAddressInput::make('direccion_empresa')->label('Dirección empresa'),
                        TextInput::make('meses_empleo_actual')
                            ->label('Meses en empleo actual')->numeric(),
                        TextInput::make('ingresos_mensuales')
                            ->label('Ingresos mensuales ($)')->numeric()->prefix('$'),
                        TextInput::make('otros_ingresos')
                            ->label('Otros ingresos ($)')->numeric()->prefix('$'),
                        Textarea::make('descripcion_otros_ingresos')
                            ->label('Descripción otros ingresos')->rows(2),
                    ])->columns(2),

                Step::make('Datos Bancarios')
                    ->description('Para pagos al propietario')
                    ->icon('heroicon-o-banknotes')
                    ->schema([
                        TextInput::make('banco')->label('Banco')
                            ->placeholder('Bancolombia, Davivienda, BBVA...'),
                        Select::make('tipo_cuenta')->label('Tipo de cuenta')
                            ->options(['ahorros' => 'Cuenta de ahorros', 'corriente' => 'Cuenta corriente']),
                        TextInput::make('numero_cuenta')->label('Número de cuenta'),
                        TextInput::make('titular_cuenta')
                            ->label('Titular de la cuenta'),
                        TextInput::make('comision_pactada')
                            ->label('Comisión pactada %')
                            ->numeric()->suffix('%')
                            ->helperText('Solo para propietarios — si difiere de la tarifa general'),
                    ])->columns(2),

                Step::make('Facturación e Impuestos')
                    ->description('Parámetros tributarios del propietario/proveedor')
                    ->icon('heroicon-o-receipt-percent')
                    ->visible(fn (Get $get) => $get('es_propietario') || $get('es_proveedor'))
                    ->schema([
                        Toggle::make('requiere_iva')
                            ->label('Requiere IVA en liquidación')
                            ->helperText('Marcado si el propietario es responsable de IVA y debe cobrarse sobre la comisión')
                            ->live(),

                        Toggle::make('requiere_retefuente')
                            ->label('Sujeto a retención en la fuente')
                            ->helperText('Se aplicará retención al momento de generar la liquidación')
                            ->live(),

                        Toggle::make('quiere_factura_electronica')
                            ->label('Solicita factura electrónica')
                            ->helperText('El propietario exige comprobante de factura electrónica por la comisión'),

                        TextInput::make('tarifa_iva_pactada')
                            ->label('Tarifa IVA pactada %')
                            ->numeric()->suffix('%')
                            ->placeholder('19')
                            ->visible(fn (Get $get) => $get('requiere_iva'))
                            ->helperText('Dejar vacío para usar la tarifa global de la empresa'),

                        TextInput::make('tarifa_retefuente_pactada')
                            ->label('Tarifa retefuente pactada %')
                            ->numeric()->suffix('%')
                            ->placeholder('3.5')
                            ->visible(fn (Get $get) => $get('requiere_retefuente'))
                            ->helperText('Dejar vacío para usar la tarifa global de la empresa'),
                    ])->columns(2),

                Step::make('Evaluación Crediticia')
                    ->description('Centrales de riesgo y garantía')
                    ->icon('heroicon-o-shield-check')
                    ->visible(fn (Get $get) => !$get('es_propietario') && !$get('es_cliente_compra'))
                    ->schema([
                        Select::make('estado_crediticio')->label('Estado crediticio')
                            ->options([
                                'sin_evaluar' => 'Sin evaluar',
                                'en_proceso'  => 'En proceso',
                                'aprobado'    => 'Aprobado',
                                'condicional' => 'Condicional',
                                'rechazado'   => 'Rechazado',
                            ])->default('sin_evaluar'),

                        DatePicker::make('fecha_evaluacion_crediticia')
                            ->label('Fecha de evaluación'),
                        TextInput::make('score_crediticio')
                            ->label('Score crediticio (DataCrédito)'),
                        Toggle::make('reporte_negativo')
                            ->label('Reportado en centrales de riesgo'),
                        Textarea::make('notas_evaluacion')
                            ->label('Notas de evaluación')->rows(3)->columnSpanFull(),

                        Select::make('tipo_garantia')->label('Tipo de garantía')
                            ->options([
                                'fiador'   => 'Fiador / Codeudor',
                                'poliza'   => 'Póliza de arrendamiento',
                                'deposito' => 'Depósito en garantía',
                                'ninguna'  => 'Ninguna',
                            ]),
                        TextInput::make('aseguradora')
                            ->label('Aseguradora')
                            ->placeholder('Mapfre, Sura, Bolívar...'),
                        TextInput::make('numero_poliza')->label('Número de póliza'),
                    ])->columns(2),

                Step::make('Notas')
                    ->description('Observaciones adicionales')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->schema([
                        Textarea::make('notas_crm')
                            ->label('Notas CRM')->rows(4)->columnSpanFull(),
                        Textarea::make('notas')
                            ->label('Notas internas')->rows(3)->columnSpanFull(),
                    ]),

            ])->skippable()->columnSpanFull(),
        ]);
    }
}

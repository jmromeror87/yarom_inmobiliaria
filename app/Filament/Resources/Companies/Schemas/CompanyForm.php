<?php
/*
|--------------------------------------------------------------------------
| YarOM ERP - Soluciones de Gestión
|--------------------------------------------------------------------------
| Proyecto privado desarrollado por:
| Ingeniero Jhoan Romero Rivera
| LinkedIn: https://linkedin.com/in/jmromeror87
|
| Módulo: Company
| Archivo: CompanyForm.php
| Fecha: 02/05/2026
| Versión: v1.1 — Logo + colores
|--------------------------------------------------------------------------
*/

namespace App\Filament\Resources\Companies\Schemas;

use App\Models\Company;
use App\Models\Departamento;
use App\Models\Municipio;
use App\Forms\Components\MapboxAddressInput;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;

class CompanyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Wizard::make([

                Step::make('Identidad Legal')
                    ->description('Datos ante la DIAN y cámara de comercio')
                    ->icon('heroicon-o-identification')
                    ->schema([
                        Select::make('tipo_persona')
                            ->label('Tipo de persona')
                            ->options(['juridica' => 'Persona Jurídica', 'natural' => 'Persona Natural'])
                            ->required()->default('juridica'),

                        TextInput::make('razon_social')
                            ->label('Razón social')->required(),

                        TextInput::make('nombre_comercial')
                            ->label('Nombre comercial'),

                        TextInput::make('nit')
                            ->label('NIT (sin dígito de verificación)')
                            ->required()->numeric()
                            ->placeholder('Ej: 900123456')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                if ($state) {
                                    $dv = Company::calcularDigitoNit($state);
                                    $set('digito_verificacion', $dv);
                                    $set('nit_completo', $state . '-' . $dv);
                                }
                            }),

                        TextInput::make('digito_verificacion')
                            ->label('Dígito de verificación')
                            ->disabled()->numeric()->placeholder('Auto'),

                        TextInput::make('matricula_mercantil')
                            ->label('N° Matrícula mercantil'),

                        DatePicker::make('fecha_matricula')
                            ->label('Fecha de matrícula'),

                        DatePicker::make('fecha_renovacion')
                            ->label('Fecha de renovación'),

                        TextInput::make('camara_comercio')
                            ->label('Cámara de comercio')
                            ->placeholder('Cámara de Comercio de Bogotá'),

                        TextInput::make('codigo_ciiu')
                            ->label('Código CIIU')
                            ->placeholder('6810')
                            ->helperText('6810 = Actividades inmobiliarias realizadas con bienes propios o arrendados'),

                        TextInput::make('descripcion_ciiu')
                            ->label('Descripción actividad económica'),

                       FileUpload::make('logo_path')
    ->label('Logo de la empresa')
    ->image()
    ->fetchFileInformation(false)
    ->disk('public')
    ->directory('logos')
    ->maxSize(2048)
    ->helperText('PNG o JPG · Máximo 2MB · Recomendado 400x150px')
    ->columnSpanFull(),
                    ])->columns(2),

                Step::make('Régimen Fiscal')
                    ->description('Obligaciones tributarias DIAN')
                    ->icon('heroicon-o-calculator')
                    ->schema([
                        Select::make('tipo_contribuyente')
                            ->label('Tipo de contribuyente')
                            ->options([
                                'persona_juridica'               => 'Persona jurídica',
                                'persona_natural_comerciante'    => 'Persona natural comerciante',
                                'persona_natural_no_comerciante' => 'Persona natural no comerciante',
                                'entidad_sin_animo_lucro'        => 'Entidad sin ánimo de lucro',
                                'gran_contribuyente'             => 'Gran contribuyente',
                            ])->required()->default('persona_juridica'),

                        Select::make('regimen_fiscal')
                            ->label('Régimen fiscal')
                            ->options([
                                'simple_tributacion' => 'Simple de Tributación',
                                'ordinario'          => 'Ordinario',
                                'especial'           => 'Especial',
                            ])->required()->default('ordinario'),

                        TextInput::make('tarifa_iva')
                            ->label('Tarifa IVA %')
                            ->numeric()->suffix('%')->default(19),

                        TextInput::make('tarifa_retefuente_servicios')
                            ->label('Retefuente servicios %')
                            ->numeric()->suffix('%')->default(4),

                        TextInput::make('tarifa_retefuente_honorarios')
                            ->label('Retefuente honorarios %')
                            ->numeric()->suffix('%')->default(10),

                        TextInput::make('tarifa_retefuente_arrendamiento')
                            ->label('Retefuente arrendamiento %')
                            ->numeric()->suffix('%')->default(3.5),

                        Toggle::make('responsable_iva')
                            ->label('Responsable de IVA')->default(true),
                        Toggle::make('gran_contribuyente')
                            ->label('Gran contribuyente'),
                        Toggle::make('autorretenedor')
                            ->label('Autorretenedor'),
                        Toggle::make('agente_retencion_fuente')
                            ->label('Agente retención en la fuente')->default(true),
                        Toggle::make('agente_reteica')
                            ->label('Agente reteICA'),
                        Toggle::make('agente_reteiva')
                            ->label('Agente reteIVA'),
                    ])->columns(2),

                Step::make('Facturación DIAN')
                    ->description('Resolución, operador y configuración FE')
                    ->icon('heroicon-o-qr-code')
                    ->schema([
                        Toggle::make('factura_electronica_activa')
                            ->label('Facturación electrónica activa')
                            ->helperText('Al activar, las nuevas facturas se emitirán automáticamente ante la DIAN.')
                            ->live()->columnSpanFull(),

                        \Filament\Forms\Components\Select::make('fe_operador')
                            ->label('Operador autorizado DIAN')
                            ->options([
                                'factus'      => 'Factus (factus.com.co)',
                                'dataico'     => 'Dataico (dataico.com)',
                                'facturatech' => 'Facturatech (facturatech.co)',
                            ])
                            ->default('factus')
                            ->required()
                            ->helperText('El operador activo se configura también en FE_DRIVER del .env'),

                        \Filament\Forms\Components\Select::make('fe_ambiente')
                            ->label('Ambiente DIAN')
                            ->options([
                                'habilitacion' => '🧪 Habilitación (pruebas)',
                                'produccion'   => '🟢 Producción (real)',
                            ])
                            ->default('habilitacion')
                            ->required(),

                        TextInput::make('resolucion_facturacion')
                            ->label('N° Resolución DIAN')
                            ->placeholder('18760000001'),

                        DatePicker::make('fecha_resolucion')
                            ->label('Fecha de la resolución'),

                        DatePicker::make('fecha_vencimiento_resolucion')
                            ->label('Vencimiento resolución'),

                        TextInput::make('prefijo_factura')
                            ->label('Prefijo de factura')
                            ->placeholder('FE'),

                        TextInput::make('consecutivo_desde')
                            ->label('Consecutivo desde')
                            ->numeric()->default(1),

                        TextInput::make('consecutivo_hasta')
                            ->label('Consecutivo hasta')
                            ->numeric(),

                        TextInput::make('consecutivo_actual')
                            ->label('Consecutivo actual')
                            ->numeric()->default(1)
                            ->helperText('Avanza automáticamente con cada FE aceptada.'),

                        \Filament\Forms\Components\Textarea::make('fe_nota_pie')
                            ->label('Nota al pie de la factura')
                            ->rows(2)->columnSpanFull()
                            ->placeholder('Texto que aparece al pie de cada factura electrónica emitida.'),
                    ])->columns(2),

                Step::make('Representante Legal')
                    ->description('Datos del representante legal')
                    ->icon('heroicon-o-user-circle')
                    ->schema([
                        TextInput::make('rep_legal_nombre')
                            ->label('Nombre completo'),

                        Select::make('rep_legal_tipo_doc')
                            ->label('Tipo de documento')
                            ->options(['CC' => 'Cédula de Ciudadanía', 'CE' => 'Cédula de Extranjería', 'Pasaporte' => 'Pasaporte'])
                            ->default('CC'),

                        TextInput::make('rep_legal_documento')
                            ->label('N° de documento'),

                        TextInput::make('rep_legal_email')
                            ->label('Correo electrónico')->email(),

                        TextInput::make('rep_legal_telefono')
                            ->label('Teléfono')
                            ->placeholder('+57 300 0000000'),
                    ])->columns(2),

                Step::make('Dirección y Contacto')
                    ->description('Ubicación y datos de contacto')
                    ->icon('heroicon-o-map-pin')
                    ->schema([
                        Select::make('pais_id')
                            ->label('País')
                            ->relationship('pais', 'nombre')
                            ->default(1)->live(),

                        Select::make('departamento_id')
                            ->label('Departamento')
                            ->options(fn (Get $get) =>
                                Departamento::where('pais_id', $get('pais_id') ?? 1)
                                    ->orderBy('nombre')->pluck('nombre', 'id')
                            )->searchable()->live()
                            ->afterStateUpdated(fn (Set $set) => $set('municipio_id', null)),

                        Select::make('municipio_id')
                            ->label('Municipio')
                            ->options(fn (Get $get) =>
                                Municipio::where('departamento_id', $get('departamento_id'))
                                    ->orderBy('nombre')->pluck('nombre', 'id')
                            )->searchable(),

                        MapboxAddressInput::make('direccion')
                            ->label('Dirección')
                            ->placeholder('Calle 123 # 45 - 67')
                            ->columnSpanFull(),

                        TextInput::make('barrio')
                            ->label('Barrio / Localidad'),

                        TextInput::make('codigo_postal')
                            ->label('Código postal')
                            ->placeholder('110111'),

                        TextInput::make('telefono')
                            ->label('Teléfono fijo')
                            ->placeholder('+57 601 0000000'),

                        TextInput::make('celular')
                            ->label('Celular')
                            ->placeholder('+57 300 0000000'),

                        TextInput::make('email')
                            ->label('Correo electrónico principal')->email(),

                        TextInput::make('email_notificaciones')
                            ->label('Correo para notificaciones')->email(),

                        TextInput::make('sitio_web')
                            ->label('Sitio web')->placeholder('https://'),

                        ColorPicker::make('color_primario')
                            ->label('Color primario')->default('#E11D48'),

                        ColorPicker::make('color_secundario')
                            ->label('Color secundario')->default('#2563EB'),
                    ])->columns(2),

                Step::make('Configuración Inmobiliaria')
                    ->description('Parámetros operativos del negocio')
                    ->icon('heroicon-o-building-office-2')
                    ->schema([
                        TextInput::make('comision_administracion')
                            ->label('Comisión de administración %')
                            ->numeric()->suffix('%')->default(10)
                            ->helperText('Porcentaje que cobra la inmobiliaria sobre el canon mensual'),

                        TextInput::make('comision_corretaje')
                            ->label('Comisión corretaje comprador %')
                            ->numeric()->suffix('%')->default(3)
                            ->helperText('% sobre precio de venta que paga el comprador'),

                        TextInput::make('comision_corretaje_vendedor')
                            ->label('Comisión corretaje vendedor %')
                            ->numeric()->suffix('%')->default(3)
                            ->helperText('% sobre precio de venta que paga el vendedor'),

                        TextInput::make('dia_corte_mensual')
                            ->label('Día de pago mensual')
                            ->numeric()->default(5)->minValue(1)->maxValue(28)
                            ->helperText('Día del mes en que vence el pago del canon'),

                        TextInput::make('dias_gracia_mora')
                            ->label('Días de gracia antes de mora')
                            ->numeric()->default(5),

                        TextInput::make('tasa_mora_mensual')
                            ->label('Tasa de mora mensual %')
                            ->numeric()->suffix('%')->default(1.5441)
                            ->helperText('Tasa máxima legal vigente certificada por la Superfinanciera'),

                        TextInput::make('sura_tarifa_estudio')
                            ->label('Tarifa SURA por estudio ($)')
                            ->numeric()->prefix('$')->default(35000)
                            ->helperText('Valor que SURA cobra al arrendatario por el estudio socioeconómico'),

                        TextInput::make('inmobiliaria_tarifa_estudio')
                            ->label('Tarifa inmobiliaria por estudio ($)')
                            ->numeric()->prefix('$')->default(60000)
                            ->helperText('Valor que la inmobiliaria cobra al arrendatario. La diferencia con SURA es ingreso de la empresa'),

                        TextInput::make('tarifa_estudio_directo')
                            ->label('Tarifa estudio aprobación directa ($)')
                            ->numeric()->prefix('$')->default(50000)
                            ->helperText('Cuando el gerente aprueba directamente sin pasar por SURA'),

                        Textarea::make('nota_estudio_sura')
                            ->label('Nota informativa en cotización')
                            ->rows(2)->columnSpanFull()
                            ->placeholder('El valor del estudio socioeconómico SURA se descuenta del primer canon...'),
                    ])->columns(2),

            ])
            ->skippable()
            ->columnSpanFull(),
        ]);
    }
}
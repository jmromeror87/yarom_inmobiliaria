<?php

namespace App\Filament\Resources\SaleContracts\Schemas;

use App\Models\Company;
use App\Models\SaleContract;
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

class SaleContractForm
{
    public static function configure(Schema $schema): Schema
    {
        $company = Company::first();
        $comisionDefault = 3.00;

        return $schema->components([
            Wizard::make([

                Step::make('Partes y Negocio')
                    ->description('Inmueble, vendedor y comprador')
                    ->icon('heroicon-o-users')
                    ->schema([
                        Select::make('property_id')
                            ->label('Inmueble')
                            ->relationship('property', 'direccion')
                            ->searchable()->preload()->required(),

                        Select::make('vendedor_id')
                            ->label('Vendedor / Propietario')
                            ->relationship('vendedor', 'nombre_completo')
                            ->searchable()->preload()->required(),

                        Select::make('comprador_id')
                            ->label('Comprador')
                            ->relationship('comprador', 'nombre_completo')
                            ->searchable()->preload()->required(),

                        Select::make('asesor_id')
                            ->label('Asesor a cargo')
                            ->relationship('asesor', 'name')
                            ->searchable()->preload(),

                        Select::make('request_id')
                            ->label('Solicitud de origen')
                            ->relationship('request', 'numero')
                            ->searchable()
                            ->helperText('Si nació de un estudio de comprador'),
                    ])->columns(2),

                Step::make('Precio y Financiación')
                    ->description('Valor del negocio y forma de pago')
                    ->icon('heroicon-o-banknotes')
                    ->schema([
                        TextInput::make('precio_venta')
                            ->label('Precio de venta ($)')
                            ->numeric()->prefix('$')->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                $pct = $get('porcentaje_comision') ?? 3;
                                if ($state && $pct) {
                                    $set('valor_comision', round((float)$state * ((float)$pct / 100), 2));
                                }
                            }),

                        TextInput::make('precio_avaluo')
                            ->label('Precio de avalúo ($)')
                            ->numeric()->prefix('$'),

                        Select::make('forma_pago')
                            ->label('Forma de pago')
                            ->options(SaleContract::FORMAS_PAGO)
                            ->default('contado')->live()->required(),

                        TextInput::make('entidad_financiera')
                            ->label('Entidad financiera')
                            ->visible(fn (Get $get) => in_array($get('forma_pago'), ['credito_hipotecario','leasing','mixto']))
                            ->placeholder('Banco de Bogotá, Davivienda...'),

                        TextInput::make('valor_credito')
                            ->label('Valor del crédito ($)')
                            ->numeric()->prefix('$')
                            ->visible(fn (Get $get) => in_array($get('forma_pago'), ['credito_hipotecario','leasing','mixto'])),

                        TextInput::make('valor_cuota_inicial')
                            ->label('Cuota inicial ($)')
                            ->numeric()->prefix('$')
                            ->visible(fn (Get $get) => in_array($get('forma_pago'), ['credito_hipotecario','leasing','mixto'])),
                    ])->columns(2),

                Step::make('Comisión de Corretaje')
                    ->description('Quién paga y cuánto')
                    ->icon('heroicon-o-receipt-percent')
                    ->schema([
                        Select::make('quien_paga_comision')
                            ->label('¿Quién paga la comisión?')
                            ->options([
                                'comprador' => 'El comprador',
                                'vendedor'  => 'El vendedor',
                                'ambos'     => 'Ambos (50/50)',
                                'ninguno'   => 'Ninguno (exonerado)',
                            ])
                            ->default('comprador')->required(),

                        TextInput::make('porcentaje_comision')
                            ->label('Porcentaje de comisión %')
                            ->numeric()->suffix('%')->default($comisionDefault)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                $precio = $get('precio_venta') ?? 0;
                                if ($precio && $state) {
                                    $set('valor_comision', round((float)$precio * ((float)$state / 100), 2));
                                }
                            })
                            ->helperText('Estándar del mercado: 3% sobre precio de venta'),

                        TextInput::make('valor_comision')
                            ->label('Valor de la comisión ($)')
                            ->numeric()->prefix('$')
                            ->helperText('Se calcula automáticamente. Puede editarse manualmente'),

                        Select::make('estado_comision')
                            ->label('Estado de la comisión')
                            ->options([
                                'pendiente' => 'Pendiente',
                                'parcial'   => 'Pago parcial',
                                'pagada'    => 'Pagada completa',
                            ])->default('pendiente'),

                        TextInput::make('comision_pagada')
                            ->label('Comisión ya recibida ($)')
                            ->numeric()->prefix('$')->default(0),
                    ])->columns(2),

                Step::make('Estado del Proceso')
                    ->description('Etapa legal del negocio')
                    ->icon('heroicon-o-document-check')
                    ->schema([
                        Select::make('estado')
                            ->label('Estado del contrato')
                            ->options(SaleContract::ESTADOS)
                            ->default('promesa')->required()->live(),

                        DatePicker::make('fecha_promesa')
                            ->label('Fecha promesa de compraventa'),

                        DatePicker::make('fecha_escritura')
                            ->label('Fecha de escrituración'),

                        DatePicker::make('fecha_registro')
                            ->label('Fecha de registro en notaría'),

                        DatePicker::make('fecha_entrega')
                            ->label('Fecha de entrega al comprador'),
                    ])->columns(2),

                Step::make('Notaría')
                    ->description('Datos de la escritura pública')
                    ->icon('heroicon-o-building-library')
                    ->schema([
                        TextInput::make('notaria')
                            ->label('Notaría')
                            ->placeholder('Notaría 7 de Bogotá'),

                        TextInput::make('notaria_ciudad')
                            ->label('Ciudad de la notaría'),

                        TextInput::make('numero_escritura')
                            ->label('N° de escritura'),

                        DatePicker::make('fecha_escrituracion')
                            ->label('Fecha de la escritura'),

                        FileUpload::make('path_promesa')
                            ->label('PDF Promesa de compraventa')
                            ->disk('public')->directory('corretaje/promesas')
                            ->acceptedFileTypes(['application/pdf'])->maxSize(10240),

                        FileUpload::make('path_escritura')
                            ->label('PDF Escritura pública')
                            ->disk('public')->directory('corretaje/escrituras')
                            ->acceptedFileTypes(['application/pdf'])->maxSize(10240),

                        Textarea::make('notas')
                            ->label('Notas internas')->rows(3)->columnSpanFull(),
                    ])->columns(2),

            ])->skippable()->columnSpanFull(),
        ]);
    }
}

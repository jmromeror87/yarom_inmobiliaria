<?php

namespace App\Filament\Resources\RentBills\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RentBillForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Datos de la factura')
                ->columns(3)
                ->schema([
                    TextInput::make('numero')->label('N° Factura')->disabled(),
                    TextInput::make('numero_dian')->label('N° DIAN')->placeholder('FE-0001'),
                    Select::make('tipo_documento')
                        ->label('Tipo de documento')
                        ->options([
                            'documento_equivalente' => 'Documento equivalente',
                            'factura_electronica'   => 'Factura electrónica DIAN',
                        ])->default('documento_equivalente'),
                ]),

            Section::make('Periodo y contrato')
                ->columns(3)
                ->schema([
                    Select::make('rental_contract_id')
                        ->label('Contrato de arriendo')
                        ->relationship('rentalContract', 'numero_contrato')
                        ->searchable()->preload()->disabled(),
                    DatePicker::make('periodo_inicio')->label('Inicio periodo')->disabled(),
                    DatePicker::make('periodo_fin')->label('Fin periodo')->disabled(),
                ]),

            Section::make('Valores')
                ->columns(3)
                ->schema([
                    TextInput::make('canon_base')
                        ->label('Canon base')->prefix('$')->disabled(),
                    TextInput::make('cuota_administracion')
                        ->label('Administración')->prefix('$')->disabled(),
                    TextInput::make('mora_acumulada')
                        ->label('Mora acumulada')->prefix('$')->disabled(),
                    TextInput::make('total_factura')
                        ->label('Total factura')->prefix('$')->disabled(),
                    TextInput::make('total_pagado')
                        ->label('Total pagado')->prefix('$')->disabled(),
                    TextInput::make('saldo_pendiente')
                        ->label('Saldo pendiente')->prefix('$')->disabled(),
                ]),

            Section::make('⚙️ Ajustes')
                ->description('Ajustes manuales del asesor: aplicar o no mora en esta factura puntual, y arrastrar saldo pendiente de un periodo anterior.')
                ->columns(2)
                ->schema([
                    Toggle::make('aplicar_mora')
                        ->label('Aplicar mora a esta factura')
                        ->helperText('Si lo apagas, esta factura deja de acumular mora aunque esté vencida.')
                        ->default(true),
                    TextInput::make('saldo_anterior_arrastrado')
                        ->label('Saldo arrastrado de periodo anterior')
                        ->prefix('$')->numeric()->default(0)
                        ->helperText('Se suma al total que debe pagar el inquilino, sin duplicar el asiento contable del periodo anterior.'),
                    TextInput::make('nota_saldo_arrastrado')
                        ->label('Nota del saldo arrastrado')
                        ->placeholder('Ej: saldo pendiente de junio 2026')
                        ->columnSpanFull(),
                ]),

            Section::make('🛡️ Seguro SURA')
                ->icon('heroicon-o-shield-check')
                ->collapsed()
                ->visible(fn ($record) => $record && (float)$record->valor_seguro_sura > 0)
                ->columns(3)
                ->description('Seguro de arrendamiento cobrado al inquilino y pagado a ASURA. No va al propietario.')
                ->schema([
                    TextInput::make('valor_seguro_sura')
                        ->label('Base seguro (2.5% canon)')
                        ->prefix('$')->disabled(),
                    TextInput::make('iva_seguro_sura')
                        ->label('IVA seguro (19%)')
                        ->prefix('$')->disabled(),
                    TextInput::make('redondeo_seguro')
                        ->label('Redondeo (comisión inmobiliaria)')
                        ->prefix('$')->disabled()
                        ->helperText('Diferencia entre cobro redondeado y valor exacto'),
                ]),

            Section::make('Estado y fechas')
                ->columns(3)
                ->schema([
                    Select::make('estado')
                        ->label('Estado')
                        ->options([
                            'pendiente' => '⏳ Pendiente',
                            'parcial'   => '🔶 Pago parcial',
                            'pagada'    => '✅ Pagada',
                            'en_mora'   => '🔴 En mora',
                            'vencida'   => '⚠️ Vencida',
                            'anulada'   => '❌ Anulada',
                        ])->disabled(),
                    DatePicker::make('fecha_limite_pago')->label('Fecha límite')->disabled(),
                    TextInput::make('dias_mora')->label('Días de mora')->disabled()->suffix('días'),
                ]),

            Section::make('Notas')
                ->schema([
                    Textarea::make('notas')->label('Notas')->rows(3)->columnSpanFull(),
                ]),
        ]);
    }
}

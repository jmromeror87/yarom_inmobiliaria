<?php

namespace App\Filament\Resources\OwnerLiquidations\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class OwnerLiquidationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('numero')
                    ->required(),
                Select::make('rental_contract_id')
                    ->relationship('rentalContract', 'id')
                    ->required(),
                Select::make('property_id')
                    ->relationship('property', 'id')
                    ->required(),
                Select::make('propietario_id')
                    ->relationship('propietario', 'id')
                    ->required(),
                TextInput::make('mes')
                    ->required()
                    ->numeric(),
                TextInput::make('anio')
                    ->required()
                    ->numeric(),
                DatePicker::make('periodo_inicio')
                    ->required(),
                DatePicker::make('periodo_fin')
                    ->required(),
                TextInput::make('canon_cobrado')
                    ->required()
                    ->numeric(),
                TextInput::make('comision_porcentaje')
                    ->required()
                    ->numeric(),
                TextInput::make('comision_valor')
                    ->required()
                    ->numeric(),
                TextInput::make('iva_comision')
                    ->required()
                    ->numeric(),
                TextInput::make('otros_descuentos')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                Textarea::make('descripcion_descuentos')
                    ->columnSpanFull(),
                TextInput::make('total_giro')
                    ->required()
                    ->numeric(),
                Select::make('estado')
                    ->options([
            'pendiente' => 'Pendiente',
            'aprobada' => 'Aprobada',
            'pagada' => 'Pagada',
            'anulada' => 'Anulada',
        ])
                    ->default('pendiente')
                    ->required(),
                DatePicker::make('fecha_giro'),
                Select::make('forma_giro')
                    ->options([
            'transferencia' => 'Transferencia',
            'consignacion' => 'Consignacion',
            'cheque' => 'Cheque',
            'efectivo' => 'Efectivo',
        ]),
                TextInput::make('referencia_giro'),
                TextInput::make('comprobante_giro_path'),
                Toggle::make('wap_enviado')
                    ->required(),
                DateTimePicker::make('wap_enviado_at'),
                Textarea::make('notas')
                    ->columnSpanFull(),
            ]);
    }
}

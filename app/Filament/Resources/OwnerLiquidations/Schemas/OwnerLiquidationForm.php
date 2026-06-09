<?php

namespace App\Filament\Resources\OwnerLiquidations\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class OwnerLiquidationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Información del período')
                ->icon('heroicon-o-calendar')
                ->columns(3)
                ->schema([
                    TextInput::make('numero')
                        ->label('N° Liquidación')->disabled(),

                    Select::make('rental_contract_id')
                        ->label('Contrato de arriendo')
                        ->relationship('rentalContract', 'numero_contrato')
                        ->disabled()->columnSpan(2),

                    Select::make('propietario_id')
                        ->label('Propietario')
                        ->relationship('propietario', 'nombre_completo')
                        ->disabled()->columnSpan(2),

                    Select::make('property_id')
                        ->label('Inmueble')
                        ->relationship('property', 'direccion')
                        ->disabled(),

                    DatePicker::make('periodo_inicio')
                        ->label('Inicio del período')->disabled(),

                    DatePicker::make('periodo_fin')
                        ->label('Fin del período')->disabled(),

                    Select::make('estado')
                        ->label('Estado')
                        ->options([
                            'pendiente' => 'Pendiente',
                            'aprobada'  => 'Aprobada',
                            'pagada'    => 'Pagada',
                            'anulada'   => 'Anulada',
                        ])
                        ->required(),
                ]),

            Section::make('Liquidación económica')
                ->icon('heroicon-o-banknotes')
                ->columns(2)
                ->description('Los valores calculados no son editables — se generan automáticamente desde la factura pagada.')
                ->schema([
                    TextInput::make('canon_cobrado')
                        ->label('Canon cobrado al inquilino')
                        ->prefix('$')->numeric()->disabled(),

                    TextInput::make('comision_porcentaje')
                        ->label('Comisión administración')
                        ->suffix('%')->numeric()->disabled(),

                    TextInput::make('comision_valor')
                        ->label('Valor comisión')
                        ->prefix('$')->numeric()->disabled(),

                    TextInput::make('iva_comision')
                        ->label('IVA sobre comisión (19%)')
                        ->prefix('$')->numeric()->disabled(),

                    TextInput::make('retefuente_valor')
                        ->label('Retefuente')
                        ->prefix('$')->numeric()->disabled()
                        ->helperText('Solo aplica si el arrendatario es persona jurídica'),

                    TextInput::make('otros_descuentos')
                        ->label('Otros descuentos')
                        ->prefix('$')->numeric()->default(0)
                        ->live()
                        ->helperText('Reparaciones, deudas u otros cargos al propietario'),

                    Textarea::make('descripcion_descuentos')
                        ->label('Descripción de descuentos')
                        ->rows(2)->columnSpanFull()
                        ->visible(fn (Get $get) => (float)$get('otros_descuentos') > 0),

                    Placeholder::make('total_giro_preview')
                        ->label('Total a girar al propietario')
                        ->content(fn (Get $get, $record) =>
                            '$' . number_format(
                                max(0,
                                    (float)($record?->canon_cobrado ?? 0)
                                    - (float)($record?->comision_valor ?? 0)
                                    - (float)($record?->iva_comision ?? 0)
                                    - (float)($record?->retefuente_valor ?? 0)
                                    - (float)($get('otros_descuentos') ?? 0)
                                ),
                                0, ',', '.'
                            ) . ' COP'
                        )
                        ->columnSpanFull(),

                    TextInput::make('total_giro')
                        ->label('Total giro (guardado)')->prefix('$')->numeric()->disabled(),
                ]),

            Section::make('Giro al propietario')
                ->icon('heroicon-o-building-library')
                ->columns(2)
                ->schema([
                    DatePicker::make('fecha_giro')
                        ->label('Fecha del giro'),

                    Select::make('forma_giro')
                        ->label('Forma de giro')
                        ->options([
                            'transferencia' => 'Transferencia bancaria',
                            'consignacion'  => 'Consignación',
                            'cheque'        => 'Cheque',
                            'efectivo'      => 'Efectivo',
                        ]),

                    TextInput::make('referencia_giro')
                        ->label('N° transacción / referencia')->columnSpanFull(),

                    FileUpload::make('comprobante_giro_path')
                        ->label('Comprobante del giro')
                        ->disk('public')->directory('liquidaciones/comprobantes')
                        ->acceptedFileTypes(['image/*', 'application/pdf'])
                        ->maxSize(5120)->columnSpanFull(),

                    Textarea::make('notas')
                        ->label('Notas internas')->rows(3)->columnSpanFull(),
                ]),

        ]);
    }
}

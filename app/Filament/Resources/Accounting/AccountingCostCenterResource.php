<?php

namespace App\Filament\Resources\Accounting;

use App\Filament\Resources\Accounting\Pages\CreateCostCenter;
use App\Filament\Resources\Accounting\Pages\EditCostCenter;
use App\Filament\Resources\Accounting\Pages\ListCostCenters;
use App\Models\AccountingCostCenter;
use App\Models\Property;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AccountingCostCenterResource extends Resource
{
    protected static ?string $model = AccountingCostCenter::class;
    protected static ?string $modelLabel = 'Centro de Costo';
    protected static ?string $pluralModelLabel = 'Centros de Costo';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-office';
    protected static ?string $navigationLabel = 'Centros de Costo';
    protected static string|\UnitEnum|null $navigationGroup = 'Contabilidad';
    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('codigo')
                ->label('Código')
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(20)
                ->helperText('Ej: CC-001, APTO-101'),

            TextInput::make('nombre')
                ->label('Nombre')
                ->required()
                ->maxLength(150),

            Select::make('property_id')
                ->label('Inmueble asociado')
                ->options(fn() => Property::orderBy('direccion')
                    ->get()
                    ->mapWithKeys(fn($p) => [$p->id => ($p->codigo ?? $p->id) . ' — ' . $p->direccion])
                )
                ->searchable()
                ->nullable()
                ->helperText('Opcional — para asignar movimientos a un inmueble específico'),

            Select::make('estado')
                ->label('Estado')
                ->options(['activo' => 'Activo', 'inactivo' => 'Inactivo'])
                ->default('activo')
                ->required(),

            Textarea::make('descripcion')
                ->label('Descripción')
                ->rows(2)
                ->columnSpanFull(),

        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('codigo')
                    ->label('Código')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->fontFamily('mono'),

                TextColumn::make('nombre')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('property.nombre')
                    ->label('Inmueble')
                    ->placeholder('—')
                    ->searchable(),

                TextColumn::make('lines_count')
                    ->label('Movimientos')
                    ->counts('lines')
                    ->badge()
                    ->color('info'),

                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->color(fn($state) => $state === 'activo' ? 'success' : 'gray'),
            ])
            ->defaultSort('codigo')
            ->striped()
            ->filters([
                SelectFilter::make('estado')
                    ->label('Estado')
                    ->options(['activo' => 'Activo', 'inactivo' => 'Inactivo']),
            ])
            ->recordActions([
                EditAction::make()->label('Editar'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListCostCenters::route('/'),
            'create' => CreateCostCenter::route('/create'),
            'edit'   => EditCostCenter::route('/{record}/edit'),
        ];
    }
}

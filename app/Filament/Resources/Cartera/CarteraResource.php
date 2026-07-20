<?php

namespace App\Filament\Resources\Cartera;

use App\Filament\Resources\Cartera\Pages\CreateCartera;
use App\Filament\Resources\Cartera\Pages\ListCartera;
use App\Filament\Resources\Cartera\Pages\ViewCartera;
use App\Filament\Widgets\CarteraPorEdadWidget;
use App\Models\CuentaPorCobrar;
use App\Models\Property;
use App\Models\RentalContract;
use App\Models\Third;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;

class CarteraResource extends Resource
{
    protected static ?string $model = CuentaPorCobrar::class;

    protected static string|\BackedEnum|null $navigationIcon   = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel                  = 'Cartera';
    protected static string|\UnitEnum|null  $navigationGroup   = 'Cobros';
    protected static ?int    $navigationSort                   = 3;
    protected static ?string $modelLabel                       = 'Cuenta por cobrar';
    protected static ?string $pluralModelLabel                 = 'Cartera';
    protected static ?string $slug                             = 'cartera';

    public static function getNavigationBadge(): ?string
    {
        $count = CuentaPorCobrar::whereIn('estado', ['pendiente','parcial'])
            ->where('fecha_vencimiento', '<', now())->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string
    {
        return 'danger';
    }

    public static function form(Schema $form): Schema
    {
        return $form->schema([
            Select::make('tipo')
                ->label('Tipo de cuenta')
                ->options([
                    'mora'  => 'Intereses de mora',
                    'dano'  => 'Daño en inmueble',
                    'otro'  => 'Otro concepto',
                ])
                ->required()
                ->live(),

            Select::make('third_id')
                ->label('Deudor (tercero)')
                ->relationship('third', 'nombre_completo')
                ->searchable()->preload()->required(),

            Select::make('rental_contract_id')
                ->label('Contrato de arriendo')
                ->options(fn () => RentalContract::with('property')
                    ->get()
                    ->mapWithKeys(fn ($c) => [$c->id => $c->numero_contrato . ' — ' . ($c->property?->codigo ?? '')]))
                ->searchable()
                ->nullable()
                ->live()
                ->afterStateUpdated(function ($state, callable $set) {
                    if ($state) {
                        $contrato = RentalContract::with('property')->find($state);
                        if ($contrato?->property_id) {
                            $set('property_id', $contrato->property_id);
                        }
                    }
                }),

            Select::make('property_id')
                ->label('Inmueble')
                ->options(fn () => Property::orderBy('codigo')->get()
                    ->mapWithKeys(fn ($p) => [$p->id => $p->codigo . ' — ' . $p->direccion]))
                ->searchable()
                ->nullable(),

            TextInput::make('concepto')
                ->label('Concepto / Descripción')
                ->required()
                ->maxLength(255)
                ->columnSpanFull(),

            TextInput::make('valor_original')
                ->label('Valor a cobrar ($)')
                ->numeric()->prefix('$')->required()->minValue(1),

            DatePicker::make('fecha_origen')
                ->label('Fecha origen')->required()->default(today()),

            DatePicker::make('fecha_vencimiento')
                ->label('Fecha vencimiento')->nullable(),

            Textarea::make('notas')
                ->label('Notas internas')->rows(2)->nullable()->columnSpanFull(),
        ])->columns(2);
    }

    public static function getWidgets(): array
    {
        return [CarteraPorEdadWidget::class];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListCartera::route('/'),
            'create' => CreateCartera::route('/create'),
            'view'   => ViewCartera::route('/{record}'),
        ];
    }
}

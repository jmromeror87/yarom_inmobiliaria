<?php

namespace App\Filament\Resources\Accounting;

use App\Filament\Resources\Accounting\Pages\CreateAccount;
use App\Filament\Resources\Accounting\Pages\EditAccount;
use App\Filament\Resources\Accounting\Pages\ListAccounts;
use App\Models\AccountingAccount;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AccountingAccountResource extends Resource
{
    protected static ?string $model = AccountingAccount::class;
    protected static ?string $modelLabel = 'Cuenta Contable';
    protected static ?string $pluralModelLabel = 'Plan de Cuentas';
    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-list-bullet';
    protected static ?string $navigationLabel = 'Plan de Cuentas';
    protected static string|\UnitEnum|null $navigationGroup = 'Contabilidad';
    protected static ?int    $navigationSort  = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('codigo')
                ->label('Código PUC')->required()
                ->unique(ignoreRecord: true)
                ->maxLength(20)
                ->helperText('Ej: 110505 (6 dígitos = subcuenta que acepta movimientos)'),

            TextInput::make('nombre')
                ->label('Nombre de la cuenta')
                ->required()->maxLength(200)->columnSpanFull(),

            Select::make('nivel')
                ->label('Nivel')
                ->options([1=>'1 — Clase',2=>'2 — Grupo',3=>'3 — Cuenta',4=>'4 — Subcuenta'])
                ->required(),

            Select::make('clase')
                ->label('Clase')
                ->options([
                    '1'=>'1 — Activos','2'=>'2 — Pasivos','3'=>'3 — Patrimonio',
                    '4'=>'4 — Ingresos','5'=>'5 — Gastos','6'=>'6 — Costo producción',
                    '7'=>'7 — Costo ventas','8'=>'8 — Orden deudoras','9'=>'9 — Orden acreedoras',
                ])->required(),

            Select::make('naturaleza')
                ->label('Naturaleza')
                ->options(['debito'=>'Débito','credito'=>'Crédito'])
                ->required(),

            Select::make('parent_id')
                ->label('Cuenta padre')
                ->options(fn() => AccountingAccount::where('acepta_movimiento', false)
                    ->orderBy('codigo')
                    ->get()
                    ->mapWithKeys(fn($a) => [$a->id => $a->codigo . ' — ' . $a->nombre])
                )
                ->searchable()->nullable(),

            Select::make('estado')
                ->label('Estado')
                ->options(['activo'=>'Activo','inactivo'=>'Inactivo'])
                ->default('activo')->required(),

            Toggle::make('acepta_movimiento')
                ->label('Acepta movimientos contables')
                ->helperText('Solo subcuentas (nivel 4) aceptan movimientos'),

            Toggle::make('requiere_tercero')
                ->label('Requiere tercero'),

            Toggle::make('requiere_centro_costo')
                ->label('Requiere centro de costo'),
        ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('codigo')
                    ->label('Código')->searchable()->sortable()
                    ->weight('bold')->fontFamily('mono')
                    ->color(fn($record) => match($record->nivel) {
                        1 => 'danger', 2 => 'warning', 3 => 'info', default => null
                    }),

                TextColumn::make('nombre')
                    ->label('Nombre')->searchable()
                    ->formatStateUsing(fn($state, $record) => str_repeat('  ', $record->nivel - 1) . $state),

                TextColumn::make('nivel')
                    ->label('Nivel')->badge()
                    ->formatStateUsing(fn($state) => match((int)$state) {
                        1=>'Clase',2=>'Grupo',3=>'Cuenta',4=>'Subcuenta',default=>$state
                    })
                    ->color(fn($state) => match((int)$state) {
                        1=>'danger',2=>'warning',3=>'info',4=>'success',default=>'gray'
                    }),

                TextColumn::make('clase')
                    ->label('Clase')->badge()
                    ->formatStateUsing(fn($state) => match($state) {
                        '1'=>'Activo','2'=>'Pasivo','3'=>'Patrimonio',
                        '4'=>'Ingreso','5'=>'Gasto','6'=>'C. Prod.',
                        '7'=>'C. Ventas',default=>$state
                    })
                    ->color(fn($state) => match($state) {
                        '1','2','3'=>'primary','4'=>'success','5','6','7'=>'warning',default=>'gray'
                    }),

                TextColumn::make('naturaleza')->label('Naturaleza')->badge()
                    ->color(fn($state) => $state === 'debito' ? 'info' : 'success'),

                IconColumn::make('acepta_movimiento')->label('Mov.')->boolean(),

                TextColumn::make('estado')->label('Estado')->badge()
                    ->color(fn($state) => $state === 'activo' ? 'success' : 'gray'),
            ])
            ->defaultSort('codigo')
            ->striped()
            ->filters([
                SelectFilter::make('clase')->label('Clase')->options([
                    '1'=>'Activos','2'=>'Pasivos','3'=>'Patrimonio',
                    '4'=>'Ingresos','5'=>'Gastos','6'=>'C.Producción','7'=>'C.Ventas',
                ]),
                SelectFilter::make('nivel')->label('Nivel')->options([
                    1=>'Clase',2=>'Grupo',3=>'Cuenta',4=>'Subcuenta',
                ]),
                SelectFilter::make('estado')->label('Estado')->options([
                    'activo'=>'Activo','inactivo'=>'Inactivo',
                ]),
            ])
            ->recordActions([EditAction::make()->label('Editar')]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListAccounts::route('/'),
            'create' => CreateAccount::route('/create'),
            'edit'   => EditAccount::route('/{record}/edit'),
        ];
    }
}

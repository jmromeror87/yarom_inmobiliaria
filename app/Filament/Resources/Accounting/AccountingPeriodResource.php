<?php

namespace App\Filament\Resources\Accounting;

use App\Filament\Resources\Accounting\Pages\ListPeriods;
use App\Models\AccountingPeriod;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class AccountingPeriodResource extends Resource
{
    protected static ?string $model = AccountingPeriod::class;
    protected static ?string $modelLabel = 'Período Contable';
    protected static ?string $pluralModelLabel = 'Períodos Contables';
    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel = 'Períodos Contables';
    protected static string|\UnitEnum|null $navigationGroup = 'Contabilidad';
    protected static ?int    $navigationSort  = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('anio')->label('Año')
                ->options(collect(range(now()->year - 2, now()->year + 1))->mapWithKeys(fn($y)=>[$y=>$y]))
                ->default(now()->year)->required(),

            Select::make('mes')->label('Mes')
                ->options([
                    1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',
                    5=>'Mayo',6=>'Junio',7=>'Julio',8=>'Agosto',
                    9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre',
                ])->default(now()->month)->required(),

            Select::make('estado')->label('Estado')
                ->options(['abierto'=>'Abierto','cerrado'=>'Cerrado'])
                ->default('abierto')->required(),

            Textarea::make('notas')->label('Notas')->rows(2)->columnSpanFull(),
        ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('anio')->label('Año')->sortable()->weight('bold'),

                TextColumn::make('mes_nombre')->label('Mes'),

                TextColumn::make('estado')->label('Estado')->badge()
                    ->formatStateUsing(fn($state) => $state === 'abierto' ? 'Abierto' : 'Cerrado')
                    ->color(fn($state) => $state === 'abierto' ? 'success' : 'gray'),

                TextColumn::make('entries_count')->label('Comprobantes')
                    ->counts('entries')->badge()->color('info'),

                TextColumn::make('cerrado_en')->label('Cerrado el')
                    ->dateTime('d/m/Y H:i')->placeholder('—'),

                TextColumn::make('cerradoPor.name')->label('Cerrado por')->placeholder('—'),
            ])
            ->defaultSort('anio', 'desc')
            ->recordActions([
                \Filament\Actions\Action::make('cerrar')
                    ->label('🔒 Cerrar período')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('¿Cerrar este período?')
                    ->modalDescription('Una vez cerrado no se podrán registrar más comprobantes en este período.')
                    ->visible(fn($record) => $record->estado === 'abierto')
                    ->action(function ($record) {
                        $record->update([
                            'estado'     => 'cerrado',
                            'cerrado_por'=> Auth::id(),
                            'cerrado_en' => now(),
                        ]);
                        Notification::make()->title('Período cerrado')->success()->send();
                    }),

                \Filament\Actions\Action::make('reabrir')
                    ->label('🔓 Reabrir')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn($record) => $record->estado === 'cerrado')
                    ->action(function ($record) {
                        $record->update(['estado'=>'abierto','cerrado_por'=>null,'cerrado_en'=>null]);
                        Notification::make()->title('Período reabierto')->warning()->send();
                    }),
            ])
            ->headerActions([
                \Filament\Actions\Action::make('abrir_mes_actual')
                    ->label('+ Abrir mes actual')
                    ->color('primary')
                    ->action(function () {
                        AccountingPeriod::abrirSiNoExiste(now()->year, now()->month);
                        Notification::make()->title('Período ' . now()->monthName . ' ' . now()->year . ' abierto')->success()->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ListPeriods::route('/')];
    }
}

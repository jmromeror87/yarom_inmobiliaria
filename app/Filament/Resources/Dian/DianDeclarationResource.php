<?php

namespace App\Filament\Resources\Dian;

use App\Exports\Dian\ExogenaExport;
use App\Filament\Resources\Dian\Pages\EditDianDeclaration;
use App\Filament\Resources\Dian\Pages\ListDianDeclarations;
use App\Models\DianDeclaration;
use App\Services\DianObligationService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class DianDeclarationResource extends Resource
{
    protected static ?string $model = DianDeclaration::class;
    protected static ?string $modelLabel = 'Obligación DIAN';
    protected static ?string $pluralModelLabel = 'Obligaciones DIAN';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-library';
    protected static ?string $navigationLabel = 'Obligaciones DIAN';
    protected static string|\UnitEnum|null $navigationGroup = 'Contabilidad';
    protected static ?int $navigationSort = 8;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('periodo_label')->label('Período')->disabled(),
            TextInput::make('fecha_vencimiento')->label('Vencimiento')->disabled(),
            TextInput::make('valor_a_pagar')->label('Valor calculado ($)')->disabled()
                ->prefix('$'),

            Select::make('estado')->label('Estado')
                ->options([
                    'pendiente'  => 'Pendiente',
                    'en_proceso' => 'En proceso',
                    'presentada' => 'Presentada',
                    'pagada'     => 'Pagada',
                    'no_aplica'  => 'No aplica',
                ])->required(),

            TextInput::make('numero_formulario')->label('N° Formulario DIAN'),
            DatePicker::make('fecha_presentacion')->label('Fecha presentación'),
            TextInput::make('valor_pagado')->label('Valor pagado ($)')->numeric()->prefix('$'),
            DatePicker::make('fecha_pago')->label('Fecha pago'),
            TextInput::make('banco_pago')->label('Banco / Referencia bancaria'),
            TextInput::make('referencia_pago')->label('Referencia de pago'),
            Textarea::make('notas')->label('Notas')->rows(3)->columnSpanFull(),
            FileUpload::make('adjunto_path')
                ->label('PDF formulario firmado')
                ->disk('public')->directory('dian-declaraciones')
                ->acceptedFileTypes(['application/pdf'])
                ->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('anio')->label('Año')->sortable(),

                TextColumn::make('obligationType.nombre')->label('Obligación')
                    ->searchable()->weight('bold')->wrap(),

                TextColumn::make('obligationType.formulario')->label('Form.')
                    ->badge()->color('gray'),

                TextColumn::make('periodo_label')->label('Período'),

                TextColumn::make('fecha_vencimiento')->label('Vencimiento')
                    ->date('d/m/Y')->sortable()
                    ->color(fn($record) => $record->esta_vencida ? 'danger' : ($record->es_urgenta ? 'warning' : null)),

                TextColumn::make('dias_para_vencer')
                    ->label('Días')
                    ->getStateUsing(fn($record) => $record->dias_para_vencer)
                    ->formatStateUsing(fn($state, $record) =>
                        in_array($record->estado, ['presentada','pagada','no_aplica'])
                            ? '—'
                            : ($state < 0 ? "VENCIDA {$state}d" : "{$state}d")
                    )
                    ->color(fn($record) => $record->esta_vencida ? 'danger' : ($record->es_urgenta ? 'warning' : 'gray')),

                TextColumn::make('valor_a_pagar')->label('Valor calculado')
                    ->money('COP')->sortable()->alignEnd(),

                TextColumn::make('total_declarado')->label('Declarado')
                    ->money('COP')->alignEnd(),

                TextColumn::make('valor_pagado')->label('Pagado')
                    ->money('COP')->alignEnd(),

                TextColumn::make('estado')->label('Estado')->badge()
                    ->color(fn($state) => match($state) {
                        'pagada'     => 'success',
                        'presentada' => 'info',
                        'en_proceso' => 'warning',
                        'pendiente'  => 'gray',
                        'no_aplica'  => 'gray',
                        default      => 'gray',
                    })
                    ->formatStateUsing(fn($record) => $record->estado_label),
            ])
            ->defaultSort('fecha_vencimiento', 'asc')
            ->striped()
            ->groups([
                \Filament\Tables\Grouping\Group::make('obligationType.nombre')
                    ->label('Tipo de obligación')->collapsible(),
                \Filament\Tables\Grouping\Group::make('anio')
                    ->label('Año')->collapsible(),
            ])
            ->filters([
                SelectFilter::make('estado')->label('Estado')->options([
                    'pendiente'  => 'Pendiente',
                    'en_proceso' => 'En proceso',
                    'presentada' => 'Presentada',
                    'pagada'     => 'Pagada',
                    'no_aplica'  => 'No aplica',
                ]),
                SelectFilter::make('anio')->label('Año')
                    ->options(fn() => collect(range(now()->year - 1, now()->year + 1))
                        ->mapWithKeys(fn($a) => [$a => (string)$a])->toArray()
                    ),
                SelectFilter::make('obligation_type_id')->label('Tipo')
                    ->relationship('obligationType', 'nombre'),
            ])
            ->headerActions([
                Action::make('generar_periodos')
                    ->label('Generar períodos ' . now()->year)
                    ->icon('heroicon-o-calendar-days')
                    ->extraAttributes([
                        'style' => 'background:linear-gradient(135deg,#16a34a,#4ade80)!important;color:#fff!important;border:none!important;box-shadow:0 2px 8px rgba(22,163,74,.25)!important;',
                    ])
                    ->requiresConfirmation()
                    ->action(function () {
                        $n = DianObligationService::generarPeriodosAnio(now()->year);
                        Notification::make()->title("{$n} períodos generados para " . now()->year)->success()->send();
                    }),
            ])
            ->recordActions([
                Action::make('calcular')
                    ->label('Calcular')->icon('heroicon-o-calculator')->color('warning')
                    ->tooltip('Recalcular valores desde contabilidad')
                    ->visible(fn($record) => !in_array($record->estado, ['presentada','pagada','no_aplica']))
                    ->action(function ($record) {
                        try {
                            $decl = DianObligationService::recalcular($record);
                            Notification::make()
                                ->title('Calculado: $' . number_format($decl->valor_a_pagar, 0, ',', '.'))
                                ->success()->send();
                        } catch (\Throwable $e) {
                            Notification::make()->title('Error: ' . $e->getMessage())->danger()->send();
                        }
                    }),

                Action::make('exportar_csv')
                    ->label('CSV Exógena')->icon('heroicon-o-arrow-down-tray')->color('gray')
                    ->tooltip('Exportar medios magnéticos en formato CSV — compatible prevalidador DIAN')
                    ->visible(fn($record) => str_starts_with($record->obligationType?->codigo ?? '', 'exogena_'))
                    ->action(function ($record) {
                        if (empty($record->calculo)) {
                            DianObligationService::recalcular($record);
                            $record->refresh();
                        }
                        $export = new ExogenaExport($record);
                        return Response::make($export->toCsv(), 200, [
                            'Content-Type'        => 'text/csv; charset=UTF-8',
                            'Content-Disposition' => 'attachment; filename="' . $export->nombreArchivo() . '"',
                        ]);
                    }),

                EditAction::make()->label('Registrar')->icon('heroicon-o-pencil'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDianDeclarations::route('/'),
            'edit'  => EditDianDeclaration::route('/{record}/editar'),
        ];
    }
}

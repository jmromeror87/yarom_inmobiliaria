<?php

namespace App\Filament\Resources\Accounting;

use App\Filament\Resources\Accounting\Pages\CreateEntry;
use App\Filament\Resources\Accounting\Pages\EditEntry;
use App\Filament\Resources\Accounting\Pages\ListEntries;
use App\Filament\Resources\Accounting\Pages\ViewEntry;
use App\Models\AccountingAccount;
use App\Models\AccountingCostCenter;
use App\Models\AccountingEntry;
use App\Models\AccountingPeriod;
use App\Models\Third;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Html;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AccountingEntryResource extends Resource
{
    protected static ?string $model = AccountingEntry::class;
    protected static ?string $modelLabel = 'Comprobante';
    protected static ?string $pluralModelLabel = 'Comprobantes';
    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Comprobantes';
    protected static string|\UnitEnum|null $navigationGroup = 'Contabilidad';
    protected static ?int    $navigationSort  = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([

            // ── Encabezado del comprobante ──────────────────────
            Select::make('tipo')
                ->label('Tipo de comprobante')
                ->options([
                    'CC' => '📒 Comprobante de Contabilidad (CC)',
                    'CI' => '💚 Comprobante de Ingreso (CI)',
                    'CE' => '💸 Comprobante de Egreso (CE)',
                    'ND' => '📈 Nota Débito (ND)',
                    'NC' => '📉 Nota Crédito (NC)',
                    'CA' => '🔧 Comprobante de Ajuste (CA)',
                ])
                ->default('CC')->required()->live(),

            TextInput::make('numero')
                ->label('Número')->disabled()
                ->placeholder('Auto al guardar'),

            DatePicker::make('fecha')
                ->label('Fecha')->default(now())->required(),

            Select::make('period_id')
                ->label('Período contable')
                ->options(fn() => AccountingPeriod::where('estado','abierto')
                    ->orderByDesc('anio')->orderByDesc('mes')
                    ->get()
                    ->mapWithKeys(fn($p) => [$p->id => $p->nombre])
                )
                ->default(fn() => AccountingPeriod::actual()?->id)
                ->required()->searchable(),

            Textarea::make('descripcion')
                ->label('Descripción / Concepto')
                ->required()->rows(2)->columnSpanFull(),

            Select::make('third_id')
                ->label('Tercero principal')
                ->options(fn() => Third::orderBy('nombre_completo')
                    ->get()->mapWithKeys(fn($t)=>[$t->id => $t->nombre_completo . ' — ' . $t->numero_documento])
                )->searchable()->nullable(),

            Select::make('cost_center_id')
                ->label('Centro de costo')
                ->options(fn() => AccountingCostCenter::where('estado','activo')
                    ->get()->mapWithKeys(fn($c)=>[$c->id => $c->codigo . ' — ' . $c->nombre])
                )->searchable()->nullable(),

            TextInput::make('referencia')
                ->label('Referencia externa')->placeholder('N° factura, liquidación...')->nullable(),

            // ── Líneas del comprobante ──────────────────────────
            Repeater::make('lines')
                ->label('Movimientos contables')
                ->relationship()
                ->schema([
                    Select::make('account_id')
                        ->label('Cuenta PUC')
                        ->options(fn() => AccountingAccount::where('acepta_movimiento', true)
                            ->where('estado','activo')
                            ->orderBy('codigo')
                            ->get()
                            ->mapWithKeys(fn($a) => [$a->id => $a->codigo . ' — ' . $a->nombre])
                        )
                        ->searchable()->required()
                        ->live()
                        ->afterStateUpdated(function ($state) {
                            if ($state) {
                                $account = AccountingAccount::find($state);
                                if ($account) {
                                    // Sugerir en débito o crédito según naturaleza
                                }
                            }
                        }),

                    TextInput::make('descripcion')
                        ->label('Descripción línea')->nullable(),

                    TextInput::make('debito')
                        ->label('Débito ($)')->numeric()->default(0)
                        ->prefix('$')->minValue(0)->live(),

                    TextInput::make('credito')
                        ->label('Crédito ($)')->numeric()->default(0)
                        ->prefix('$')->minValue(0)->live(),

                    Select::make('third_id')
                        ->label('Tercero')
                        ->options(fn() => Third::orderBy('nombre_completo')
                            ->get()->mapWithKeys(fn($t)=>[$t->id => $t->nombre_completo])
                        )->searchable()->nullable(),

                    Select::make('cost_center_id')
                        ->label('C. Costo')
                        ->options(fn() => AccountingCostCenter::where('estado','activo')
                            ->get()->mapWithKeys(fn($c)=>[$c->id => $c->codigo . ' — ' . $c->nombre])
                        )->searchable()->nullable(),

                    TextInput::make('base_retencion')
                        ->label('Base retención')->numeric()->prefix('$')->nullable(),
                ])
                ->columns(4)
                ->defaultItems(2)
                ->addActionLabel('+ Agregar línea')
                ->reorderable('orden')
                ->collapsible()
                ->itemLabel(function (array $state): string {
                    $account = $state['account_id']
                        ? AccountingAccount::find($state['account_id'])
                        : null;
                    $codigo = $account?->codigo ?? '—';
                    $deb = number_format((float)($state['debito'] ?? 0), 0, ',', '.');
                    $cre = number_format((float)($state['credito'] ?? 0), 0, ',', '.');
                    return $codigo . '  |  Db: $' . $deb . '  —  Cr: $' . $cre;
                })
                ->columnSpanFull(),

            // ── Totales ─────────────────────────────────────────
            Html::make(fn (Get $get): \Illuminate\Support\HtmlString => static::totalesHtml($get('lines') ?? []))
                ->columnSpanFull(),

        ])->columns(3);
    }

    private static function totalesHtml(array $lines): \Illuminate\Support\HtmlString
    {
        $deb = collect($lines)->sum(fn($l) => (float)($l['debito'] ?? 0));
        $cre = collect($lines)->sum(fn($l) => (float)($l['credito'] ?? 0));
        $diff = abs($deb - $cre);
        $cuadrado = $diff < 0.01;
        $color = $cuadrado ? '#16a34a' : '#dc2626';
        $icono = $cuadrado ? '✅' : '❌';
        $fmt = fn($v) => '$' . number_format($v, 2, ',', '.');

        return new \Illuminate\Support\HtmlString(
            '<div style="display:flex;gap:32px;padding:12px 16px;background:#f8fafc;border-radius:10px;border:1.5px solid '.$color.';font-family:monospace;">'
            . '<span>📥 Débitos: <strong>' . $fmt($deb) . '</strong></span>'
            . '<span>📤 Créditos: <strong>' . $fmt($cre) . '</strong></span>'
            . '<span style="color:'.$color.';font-weight:900;">' . $icono . ' ' . ($cuadrado ? 'CUADRADO' : 'DIFERENCIA: ' . $fmt($diff)) . '</span>'
            . '</div>'
        );
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('numero')->label('Número')->searchable()->sortable()
                    ->weight('bold')->color('primary')->fontFamily('mono'),

                TextColumn::make('tipo')->label('Tipo')->badge()
                    ->color(fn($state) => match($state) {
                        'CI'=>'success','CE'=>'danger','ND'=>'warning','NC'=>'info',default=>'gray'
                    }),

                TextColumn::make('fecha')->label('Fecha')->date('d/m/Y')->sortable(),

                TextColumn::make('period.nombre')->label('Período'),

                TextColumn::make('descripcion')->label('Descripción')->limit(40),

                TextColumn::make('total_debitos')->label('Débitos')
                    ->money('COP')->sortable()->alignEnd(),

                TextColumn::make('total_creditos')->label('Créditos')
                    ->money('COP')->sortable()->alignEnd(),

                TextColumn::make('estado')->label('Estado')->badge()
                    ->color(fn($state) => match($state) {
                        'contabilizado'=>'success','borrador'=>'warning','anulado'=>'danger',default=>'gray'
                    })
                    ->formatStateUsing(fn($state) => match($state) {
                        'contabilizado'=>'Contabilizado','borrador'=>'Borrador','anulado'=>'Anulado',default=>$state
                    }),
            ])
            ->defaultSort('fecha', 'desc')
            ->striped()
            ->filters([
                SelectFilter::make('tipo')->label('Tipo')->options([
                    'CC'=>'Comp. Contabilidad','CI'=>'Comp. Ingreso','CE'=>'Comp. Egreso',
                    'ND'=>'Nota Débito','NC'=>'Nota Crédito','CA'=>'Comp. Ajuste',
                ]),
                SelectFilter::make('estado')->label('Estado')->options([
                    'borrador'=>'Borrador','contabilizado'=>'Contabilizado','anulado'=>'Anulado',
                ]),
                SelectFilter::make('period_id')->label('Período')
                    ->relationship('period', 'anio'),
            ])
            ->recordActions([
                EditAction::make()->label('Editar')
                    ->visible(fn($record) => $record->estado === 'borrador'),

                \Filament\Actions\Action::make('contabilizar')
                    ->label('✅ Contabilizar')
                    ->color('success')
                    ->icon('heroicon-o-check-badge')
                    ->requiresConfirmation()
                    ->visible(fn($record) => $record->estado === 'borrador')
                    ->action(function ($record) {
                        try {
                            $record->contabilizar();
                            Notification::make()->title('Comprobante contabilizado')->success()->send();
                        } catch (\RuntimeException $e) {
                            Notification::make()->title($e->getMessage())->danger()->send();
                        }
                    }),

                \Filament\Actions\Action::make('anular')
                    ->label('Anular')
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->schema([
                        Textarea::make('razon_anulacion')
                            ->label('Razón de anulación')->required()->rows(3),
                    ])
                    ->visible(fn($record) => $record->estado === 'contabilizado')
                    ->action(function ($record, array $data) {
                        $record->anular($data['razon_anulacion']);
                        Notification::make()->title('Comprobante anulado')->warning()->send();
                    }),

                \Filament\Actions\Action::make('ver')
                    ->label('Ver')
                    ->icon('heroicon-o-eye')->color('gray')
                    ->url(fn($record) => static::getUrl('view', ['record' => $record]))
                    ->visible(fn($record) => in_array($record->estado, ['contabilizado', 'anulado'])),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListEntries::route('/'),
            'create' => CreateEntry::route('/create'),
            'edit'   => EditEntry::route('/{record}/edit'),
            'view'   => ViewEntry::route('/{record}/ver'),
        ];
    }
}

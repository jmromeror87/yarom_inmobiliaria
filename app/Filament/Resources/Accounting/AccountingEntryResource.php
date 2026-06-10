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
use Filament\Schemas\Components\Section;
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
    protected static ?int    $navigationSort  = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([

            // ── Sección 1: Encabezado ────────────────────────────
            Section::make('Encabezado del comprobante')
                ->description('Tipo, número, fecha y período contable')
                ->icon('heroicon-o-document-text')
                ->columns(4)
                ->schema([
                    Select::make('tipo')
                        ->label('Tipo de comprobante')
                        ->options([
                            'CC' => 'Comp. Contabilidad (CC)',
                            'CI' => 'Comp. Ingreso (CI)',
                            'CE' => 'Comp. Egreso (CE)',
                            'CR' => 'Comp. Recaudo (CR)',
                            'ND' => 'Nota Débito (ND)',
                            'NC' => 'Nota Crédito (NC)',
                            'CA' => 'Comp. Ajuste (CA)',
                        ])
                        ->default('CC')->required()->live(),

                    TextInput::make('numero')
                        ->label('Número')
                        ->disabled()
                        ->placeholder('Auto al guardar')
                        ->helperText('Se asigna automáticamente'),

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
                ]),

            // ── Sección 2: Clasificación ─────────────────────────
            Section::make('Clasificación')
                ->description('Tercero, centro de costo y referencia')
                ->icon('heroicon-o-tag')
                ->columns(3)
                ->collapsed()
                ->schema([
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
                        ->label('Referencia externa')
                        ->placeholder('N° factura, liquidación...')
                        ->nullable(),
                ]),

            // ── Sección 3: Líneas ────────────────────────────────
            Section::make('Movimientos contables')
                ->description('Ingrese cada línea con su cuenta PUC, débito y crédito. El comprobante debe cuadrar (Débitos = Créditos).')
                ->icon('heroicon-o-table-cells')
                ->schema([
                    Repeater::make('lines')
                        ->label('')
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
                                ->searchable()->required()->live()
                                ->afterStateUpdated(function ($state) {
                                    // naturaleza sugerida
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

                    Html::make(fn (Get $get): \Illuminate\Support\HtmlString => static::totalesHtml($get('lines') ?? []))
                        ->columnSpanFull(),
                ]),

        ])->columns(1);
    }

    private static function totalesHtml(array $lines): \Illuminate\Support\HtmlString
    {
        $deb = collect($lines)->sum(fn($l) => (float)($l['debito'] ?? 0));
        $cre = collect($lines)->sum(fn($l) => (float)($l['credito'] ?? 0));
        $diff = abs($deb - $cre);
        $cuadrado = $diff < 0.01;
        $fmt = fn($v) => '$' . number_format($v, 0, ',', '.');

        $bg    = $cuadrado ? 'linear-gradient(135deg,#052e16,#166534)' : 'linear-gradient(135deg,#450a0a,#991b1b)';
        $bdr   = $cuadrado ? '#166534' : '#991b1b';
        $badge = $cuadrado
            ? '<span style="background:#16a34a;color:#fff;border-radius:20px;padding:3px 14px;font-size:11px;font-weight:800;letter-spacing:.05em;">CUADRADO</span>'
            : '<span style="background:#dc2626;color:#fff;border-radius:20px;padding:3px 14px;font-size:11px;font-weight:800;letter-spacing:.05em;">DIFERENCIA: ' . $fmt($diff) . '</span>';

        return new \Illuminate\Support\HtmlString(
            '<div style="background:' . $bg . ';border:1px solid ' . $bdr . ';border-radius:12px;padding:14px 20px;
                         display:flex;align-items:center;justify-content:space-between;font-family:\'Plus Jakarta Sans\',monospace;">'
            . '<div style="display:flex;gap:32px;">'
            . '  <div style="text-align:center;">'
            . '    <div style="font-size:9px;font-weight:700;color:rgba(255,255,255,.45);text-transform:uppercase;letter-spacing:.08em;margin-bottom:2px;">Total Débitos</div>'
            . '    <div style="font-size:20px;font-weight:900;color:#86efac;font-family:monospace;">' . $fmt($deb) . '</div>'
            . '  </div>'
            . '  <div style="width:1px;background:rgba(255,255,255,.1);"></div>'
            . '  <div style="text-align:center;">'
            . '    <div style="font-size:9px;font-weight:700;color:rgba(255,255,255,.45);text-transform:uppercase;letter-spacing:.08em;margin-bottom:2px;">Total Créditos</div>'
            . '    <div style="font-size:20px;font-weight:900;color:#93c5fd;font-family:monospace;">' . $fmt($cre) . '</div>'
            . '  </div>'
            . '</div>'
            . '<div style="display:flex;align-items:center;gap:10px;">'
            . '  <span style="font-size:11px;color:rgba(255,255,255,.5);font-weight:600;">Estado:</span>'
            . $badge
            . '</div>'
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
                        'CI'=>'success','CR'=>'success','CE'=>'danger','ND'=>'warning','NC'=>'info',default=>'gray'
                    })
                    ->formatStateUsing(fn($state) => match($state) {
                        'CC'=>'Contabilidad','CI'=>'Ingreso','CE'=>'Egreso','CR'=>'Recaudo',
                        'ND'=>'Nota Déb.','NC'=>'Nota Cred.','CA'=>'Ajuste',default=>$state
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
                    'CR'=>'Comp. Recaudo','ND'=>'Nota Débito','NC'=>'Nota Crédito','CA'=>'Comp. Ajuste',
                ]),
                SelectFilter::make('estado')->label('Estado')->options([
                    'borrador'=>'Borrador','contabilizado'=>'Contabilizado','anulado'=>'Anulado',
                ]),
                SelectFilter::make('period_id')->label('Período')
                    ->options(fn() => \App\Models\AccountingPeriod::orderByDesc('anio')->orderByDesc('mes')
                        ->get()->mapWithKeys(fn($p) => [$p->id => $p->nombre])->toArray()
                    ),
            ])
            ->recordActions([
                EditAction::make()->label('Editar')->outlined()
                    ->visible(fn($record) => $record->estado === 'borrador'),

                \Filament\Actions\Action::make('contabilizar')
                    ->label('Contabilizar')
                    ->color('success')
                    ->icon('heroicon-o-check-badge')
                    ->outlined()
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
                    ->outlined()
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
                    ->icon('heroicon-o-eye')
                    ->outlined()
                    ->color('gray')
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

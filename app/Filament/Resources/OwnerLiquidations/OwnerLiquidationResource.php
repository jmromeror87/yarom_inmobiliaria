<?php
namespace App\Filament\Resources\OwnerLiquidations;

use App\Filament\Resources\OwnerLiquidations\Pages\CreateOwnerLiquidation;
use App\Filament\Resources\OwnerLiquidations\Pages\EditOwnerLiquidation;
use App\Filament\Resources\OwnerLiquidations\Pages\ListOwnerLiquidations;
use App\Filament\Resources\OwnerLiquidations\Schemas\OwnerLiquidationForm;
use App\Models\OwnerLiquidation;
use App\Models\RentBill;
use Filament\Actions\Action as TableAction;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms;
use Filament\Notifications\Notification;
use App\Filament\Traits\HasResourcePermissions;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OwnerLiquidationResource extends Resource
{
    use HasResourcePermissions;

    protected static string $permissionPrefix = 'liquidaciones';
    protected static ?string $model = OwnerLiquidation::class;

    public static function getNavigationIcon(): string { return 'heroicon-o-banknotes'; }
    public static function getNavigationLabel(): string { return 'Liquidaciones Propietarios'; }
    public static function getNavigationGroup(): ?string { return 'Cartera'; }
    public static function getModelLabel(): string { return 'Liquidación Propietario'; }
    public static function getPluralModelLabel(): string { return 'Liquidaciones Propietarios'; }
    public static function getNavigationSort(): ?int { return 30; }

    public static function form(Schema $schema): Schema
    {
        return OwnerLiquidationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('numero')
                    ->label('N° Liq.')->searchable()->sortable()->copyable()->weight('bold'),
                Tables\Columns\TextColumn::make('propietario.nombre_completo')
                    ->label('Propietario')->searchable()->limit(28),
                Tables\Columns\TextColumn::make('property.direccion')
                    ->label('Inmueble')->limit(22)->searchable(),
                Tables\Columns\TextColumn::make('periodo')->label('Período')
                    ->getStateUsing(fn(OwnerLiquidation $r) => str_pad($r->mes,2,'0',STR_PAD_LEFT).'/'.$r->anio)
                    ->badge()->color('gray'),
                Tables\Columns\TextColumn::make('canon_cobrado')
                    ->label('Canon')->money('COP')->sortable(),
                Tables\Columns\TextColumn::make('comision_valor')
                    ->label('Comisión')->money('COP')->toggleable(),
                Tables\Columns\TextColumn::make('iva_comision')
                    ->label('IVA')->money('COP')->toggleable(),
                Tables\Columns\TextColumn::make('retefuente_valor')
                    ->label('Retefuente')->money('COP')->toggleable()->toggledHiddenByDefault(),
                Tables\Columns\TextColumn::make('total_giro')
                    ->label('Total Giro')->money('COP')->sortable()->weight('bold')->color('success'),
                Tables\Columns\TextColumn::make('estado')->label('Estado')->badge()
                    ->color(fn($state) => match($state) {
                        'pendiente' => 'warning',
                        'aprobada'  => 'info',
                        'pagada'    => 'success',
                        'anulada'   => 'danger',
                        default     => 'gray',
                    })
                    ->formatStateUsing(fn($state) => match($state) {
                        'pendiente' => 'Pendiente',
                        'aprobada'  => 'Aprobada',
                        'pagada'    => 'Pagada',
                        'anulada'   => 'Anulada',
                        default     => $state,
                    }),
                Tables\Columns\IconColumn::make('wap_enviado')
                    ->label('WAP')->boolean()->trueColor('success')->falseColor('gray'),
                Tables\Columns\TextColumn::make('fecha_giro')
                    ->label('Fecha giro')->date('d/m/Y')->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('estado')
                    ->options(['pendiente'=>'Pendiente','aprobada'=>'Aprobada','pagada'=>'Pagada','anulada'=>'Anulada']),
                Tables\Filters\SelectFilter::make('mes')
                    ->options(array_combine(range(1,12), ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'])),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->recordActions([
                TableAction::make('aprobar')
                    ->label('Aprobar')->icon('heroicon-o-check-badge')->color('info')
                    ->requiresConfirmation()
                    ->visible(fn(OwnerLiquidation $r) => $r->estado === 'pendiente')
                    ->action(function(OwnerLiquidation $r) {
                        $r->update(['estado' => 'aprobada']);
                        Notification::make()->title('Liquidación aprobada')->success()->send();
                    }),

                TableAction::make('registrar_giro')
                    ->label('Registrar Giro')->icon('heroicon-o-banknotes')->color('success')
                    ->visible(fn(OwnerLiquidation $r) => $r->estado === 'aprobada')
                    ->schema([
                        Forms\Components\DatePicker::make('fecha_giro')
                            ->label('Fecha de giro')->required()->default(now()),
                        Forms\Components\Select::make('forma_giro')
                            ->label('Forma de giro')
                            ->options(['transferencia'=>'Transferencia','consignacion'=>'Consignación','cheque'=>'Cheque','efectivo'=>'Efectivo'])
                            ->required(),
                        Forms\Components\TextInput::make('referencia_giro')
                            ->label('Referencia / N° transacción'),
                        Forms\Components\FileUpload::make('comprobante_giro_path')
                            ->label('Comprobante')
                            ->directory('liquidaciones/comprobantes')
                            ->acceptedFileTypes(['image/*','application/pdf'])
                            ->maxSize(5120),
                    ])
                    ->action(function(OwnerLiquidation $r, array $data) {
                        $r->update([
                            'estado'                => 'pagada',
                            'fecha_giro'            => $data['fecha_giro'],
                            'forma_giro'            => $data['forma_giro'],
                            'referencia_giro'       => $data['referencia_giro'] ?? null,
                            'comprobante_giro_path' => $data['comprobante_giro_path'] ?? null,
                        ]);
                        Notification::make()->title('Giro registrado — Liquidación pagada')->success()->send();
                    }),

                TableAction::make('anular')
                    ->label('Anular')->icon('heroicon-o-x-circle')->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn(OwnerLiquidation $r) => in_array($r->estado, ['pendiente','aprobada']))
                    ->schema([
                        Forms\Components\Textarea::make('motivo_anulacion')
                            ->label('Motivo de anulación')->required()->rows(3),
                    ])
                    ->action(function(OwnerLiquidation $r, array $data) {
                        $r->update(['estado' => 'anulada', 'notas' => $data['motivo_anulacion']]);
                        Notification::make()->title('Liquidación anulada')->warning()->send();
                    }),

                TableAction::make('enviar_wap')
                    ->label('WhatsApp')->icon('heroicon-o-chat-bubble-left-ellipsis')->color('success')
                    ->visible(fn(OwnerLiquidation $r) => in_array($r->estado,['aprobada','pagada']) && !$r->wap_enviado)
                    ->action(function(OwnerLiquidation $r) {
                        $r->update(['wap_enviado' => true, 'wap_enviado_at' => now()]);
                        Notification::make()->title('WhatsApp enviado al propietario')->success()->send();
                    }),

                TableAction::make('historial')
                    ->label('Historial')->icon('heroicon-o-clock')->color('gray')
                    ->modalHeading(fn(OwnerLiquidation $r) => 'Historial — ' . $r->numero)
                    ->modalContent(fn(OwnerLiquidation $r) => view(
                        'filament.modals.liquidacion-historial',
                        ['historial' => $r->statusHistories()->with('usuario')->get()]
                    ))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Cerrar'),

                EditAction::make()->label('Editar'),
                DeleteAction::make()->label('Eliminar'),
                RestoreAction::make(),
            ])
            ->toolbarActions([
                TableAction::make('generar_mes')
                    ->label('Generar liquidaciones del mes')->icon('heroicon-o-bolt')->color('warning')
                    ->schema([
                        Forms\Components\Select::make('mes')->label('Mes')
                            ->options(array_combine(range(1,12), ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre']))
                            ->required()->default(now()->month),
                        Forms\Components\TextInput::make('anio')
                            ->label('Año')->numeric()->required()->default(now()->year),
                    ])
                    ->action(function(array $data) {
                        $bills = RentBill::where('mes', $data['mes'])
                            ->where('anio', $data['anio'])
                            ->where('estado', 'pagada')
                            ->whereNull('owner_liquidation_id')
                            ->get();
                        $n = 0;
                        foreach ($bills as $b) {
                            if (OwnerLiquidation::generarDesdeFact($b)) $n++;
                        }
                        Notification::make()->title("{$n} liquidaciones generadas")->success()->send();
                    }),
                BulkActionGroup::make([
                    BulkAction::make('aprobar_lote')
                        ->label('Aprobar seleccionadas')->icon('heroicon-o-check-badge')->color('info')
                        ->requiresConfirmation()
                        ->action(function($records) {
                            $records->each(function(OwnerLiquidation $r) {
                                if ($r->estado === 'pendiente') $r->update(['estado' => 'aprobada']);
                            });
                            Notification::make()->title('Liquidaciones aprobadas')->success()->send();
                        }),
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListOwnerLiquidations::route('/'),
            'create' => CreateOwnerLiquidation::route('/create'),
            'edit'   => EditOwnerLiquidation::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}

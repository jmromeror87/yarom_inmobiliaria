<?php

namespace App\Filament\Resources\ElectronicInvoices;

use App\Filament\Resources\ElectronicInvoices\Pages\ListElectronicInvoices;
use App\Filament\Resources\ElectronicInvoices\Pages\ViewElectronicInvoice;
use App\Models\ElectronicInvoice;
use App\Services\FacturacionElectronicaService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ElectronicInvoiceResource extends Resource
{
    protected static ?string $model = ElectronicInvoice::class;
    protected static ?string $modelLabel = 'Factura Electrónica';
    protected static ?string $pluralModelLabel = 'Facturas Electrónicas';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-check';
    protected static ?string $navigationLabel = 'Facturas Electrónicas';
    protected static string|\UnitEnum|null $navigationGroup = 'Cobros';
    protected static ?int $navigationSort = 2;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('rentBill.numero')
                    ->label('N° Factura interna')
                    ->searchable()->sortable()
                    ->weight('bold')->color('primary')->fontFamily('mono'),

                TextColumn::make('numero_factura_dian')
                    ->label('N° DIAN')
                    ->searchable()->fontFamily('mono')
                    ->copyable()->copyMessage('CUFE copiado'),

                TextColumn::make('cufe')
                    ->label('CUFE')
                    ->limit(20)->tooltip(fn($record) => $record->cufe)
                    ->fontFamily('mono')->color('gray'),

                TextColumn::make('operador_label')
                    ->label('Operador')->badge()
                    ->color(fn($record) => match($record->operador) {
                        'factus'      => 'info',
                        'dataico'     => 'warning',
                        'facturatech' => 'success',
                        default       => 'gray',
                    }),

                TextColumn::make('estado_label')
                    ->label('Estado DIAN')->badge()
                    ->color(fn($record) => match($record->estado) {
                        'aceptada', 'aceptada_con_notificacion' => 'success',
                        'rechazada'   => 'danger',
                        'anulada'     => 'warning',
                        'error'       => 'danger',
                        'enviada'     => 'info',
                        default       => 'gray',
                    }),

                TextColumn::make('ambiente')->label('Ambiente')->badge()
                    ->color(fn($state) => $state === 'produccion' ? 'success' : 'warning')
                    ->formatStateUsing(fn($state) => $state === 'produccion' ? 'Producción' : 'Habilitación'),

                TextColumn::make('intentos')->label('Intentos'),

                TextColumn::make('emitido_en')->label('Emitido')->dateTime('d/m/Y H:i')->sortable(),

                TextColumn::make('aceptada_en')->label('Aceptada')->dateTime('d/m/Y H:i')->sortable(),

                TextColumn::make('rentBill.arrendatario.nombre_completo')
                    ->label('Arrendatario')->searchable()->limit(30),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->filters([
                SelectFilter::make('estado')->label('Estado')->options([
                    'pendiente'                  => 'Pendiente',
                    'enviada'                    => 'Enviada',
                    'aceptada'                   => 'Aceptada',
                    'aceptada_con_notificacion'  => 'Aceptada c/nota',
                    'rechazada'                  => 'Rechazada',
                    'anulada'                    => 'Anulada',
                    'error'                      => 'Error',
                ]),
                SelectFilter::make('operador')->label('Operador')->options([
                    'factus'      => 'Factus',
                    'dataico'     => 'Dataico',
                    'facturatech' => 'Facturatech',
                ]),
                SelectFilter::make('ambiente')->label('Ambiente')->options([
                    'habilitacion' => 'Habilitación',
                    'produccion'   => 'Producción',
                ]),
            ])
            ->recordActions([
                Action::make('ver')
                    ->label('Ver')->icon('heroicon-o-eye')->color('gray')
                    ->url(fn($record) => static::getUrl('view', ['record' => $record])),

                Action::make('reintentar')
                    ->label('Reintentar')->icon('heroicon-o-arrow-path')->color('warning')
                    ->visible(fn($record) => $record->puede_reintentar)
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $fe = FacturacionElectronicaService::reintentar($record);
                        if ($fe->es_aceptada) {
                            Notification::make()->title('Factura electrónica aceptada por la DIAN')->success()->send();
                        } else {
                            Notification::make()->title('Reintento procesado — estado: ' . $fe->estado_label)->warning()->send();
                        }
                    }),

                Action::make('consultar')
                    ->label('Consultar DIAN')->icon('heroicon-o-signal')->color('info')
                    ->visible(fn($record) => in_array($record->estado, ['enviada', 'aceptada', 'aceptada_con_notificacion']))
                    ->action(function ($record) {
                        $fe = FacturacionElectronicaService::consultarEstado($record);
                        Notification::make()->title('Estado actualizado: ' . $fe->estado_label)->info()->send();
                    }),

                Action::make('descargar_pdf')
                    ->label('PDF')->icon('heroicon-o-arrow-down-tray')->color('gray')
                    ->visible(fn($record) => $record->es_aceptada && $record->pdf_url)
                    ->url(fn($record) => $record->pdf_url, shouldOpenInNewTab: true),

                Action::make('anular')
                    ->label('Anular')->icon('heroicon-o-x-circle')->color('danger')
                    ->visible(fn($record) => $record->puede_anular)
                    ->requiresConfirmation()
                    ->schema([
                        \Filament\Forms\Components\Textarea::make('razon')
                            ->label('Razón de anulación')->required()->rows(3),
                    ])
                    ->action(function ($record, array $data) {
                        $fe = FacturacionElectronicaService::anular($record, $data['razon']);
                        if ($fe->estado === 'anulada') {
                            Notification::make()->title('Nota crédito emitida — FE anulada')->warning()->send();
                        } else {
                            Notification::make()->title('Error al anular: ' . $fe->ultimo_error)->danger()->send();
                        }
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListElectronicInvoices::route('/'),
            'view'  => ViewElectronicInvoice::route('/{record}/ver'),
        ];
    }
}

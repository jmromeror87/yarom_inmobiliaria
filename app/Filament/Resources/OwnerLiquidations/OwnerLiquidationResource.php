<?php
namespace App\Filament\Resources\OwnerLiquidations;

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

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return (bool) auth()->user()?->can('aprobar_liquidaciones');
    }
    protected static ?string $model = OwnerLiquidation::class;

    public static function getNavigationIcon(): string { return 'heroicon-o-banknotes'; }
    public static function getNavigationLabel(): string { return 'Liquidaciones Propietarios'; }
    public static function getNavigationGroup(): ?string { return 'Cobros'; }
    public static function getNavigationSort(): ?int { return 2; }
    public static function getModelLabel(): string { return 'Liquidación Propietario'; }
    public static function getPluralModelLabel(): string { return 'Liquidaciones Propietarios'; }

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
                Tables\Filters\Filter::make('periodo_rango')
                    ->label('Período')
                    ->schema([
                        Forms\Components\Select::make('mes_desde')->label('Mes desde')
                            ->options(array_combine(range(1,12), ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'])),
                        Forms\Components\TextInput::make('anio_desde')->label('Año desde')->numeric()->default(now()->year),
                        Forms\Components\Select::make('mes_hasta')->label('Mes hasta')
                            ->options(array_combine(range(1,12), ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'])),
                        Forms\Components\TextInput::make('anio_hasta')->label('Año hasta')->numeric()->default(now()->year),
                    ])
                    ->columns(2)
                    ->query(function(Builder $query, array $data) {
                        if (!empty($data['mes_desde']) && !empty($data['anio_desde'])) {
                            $desde = ((int) $data['anio_desde']) * 100 + (int) $data['mes_desde'];
                            $query->whereRaw('(anio * 100 + mes) >= ?', [$desde]);
                        }
                        if (!empty($data['mes_hasta']) && !empty($data['anio_hasta'])) {
                            $hasta = ((int) $data['anio_hasta']) * 100 + (int) $data['mes_hasta'];
                            $query->whereRaw('(anio * 100 + mes) <= ?', [$hasta]);
                        }
                        return $query;
                    })
                    ->indicateUsing(function(array $data): array {
                        $indicators = [];
                        $meses = [1=>'Ene',2=>'Feb',3=>'Mar',4=>'Abr',5=>'May',6=>'Jun',7=>'Jul',8=>'Ago',9=>'Sep',10=>'Oct',11=>'Nov',12=>'Dic'];
                        if (!empty($data['mes_desde']) && !empty($data['anio_desde'])) {
                            $indicators['mes_desde'] = 'Desde ' . ($meses[$data['mes_desde']] ?? $data['mes_desde']) . '/' . $data['anio_desde'];
                        }
                        if (!empty($data['mes_hasta']) && !empty($data['anio_hasta'])) {
                            $indicators['mes_hasta'] = 'Hasta ' . ($meses[$data['mes_hasta']] ?? $data['mes_hasta']) . '/' . $data['anio_hasta'];
                        }
                        return $indicators;
                    }),
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
                    ->visible(fn(OwnerLiquidation $r) => in_array($r->estado, ['aprobada', 'pagada']))
                    ->action(function(OwnerLiquidation $r) {
                        $propietario = $r->propietario;
                        $celular     = $propietario?->celular;

                        if (!$celular) {
                            Notification::make()->title('El propietario no tiene celular registrado')->danger()->send();
                            return;
                        }

                        $company  = \App\Models\Company::first();
                        $empresa  = $company?->razon_social ?? 'Serviarrendar S.A.S';
                        $meses    = [1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre'];
                        $periodo  = ($meses[$r->mes] ?? $r->mes) . ' ' . $r->anio;

                        $msg = "🏠 *Liquidación de Arrendamiento*\n\n"
                            . "Estimado(a) {$propietario->nombre_completo},\n\n"
                            . "📋 *{$r->numero}* — Período: {$periodo}\n"
                            . "🏠 Inmueble: {$r->property?->codigo} — {$r->property?->direccion}\n\n"
                            . "💰 Canon cobrado: \$" . number_format($r->canon_cobrado, 0, ',', '.') . " COP\n"
                            . "📉 Comisión adm. ({$r->comision_porcentaje}%): -\$" . number_format($r->comision_valor, 0, ',', '.') . " COP\n"
                            . "📉 IVA comisión: -\$" . number_format($r->iva_comision, 0, ',', '.') . " COP\n"
                            . ($r->retefuente_valor > 0 ? "📉 Retefuente: -\$" . number_format($r->retefuente_valor, 0, ',', '.') . " COP\n" : '')
                            . ($r->otros_descuentos > 0 ? "📉 Otros descuentos: -\$" . number_format($r->otros_descuentos, 0, ',', '.') . " COP\n" : '')
                            . "💵 *Total a girar: \$" . number_format($r->total_giro, 0, ',', '.') . " COP*\n\n"
                            . ($r->estado === 'pagada' && $r->fecha_giro
                                ? "✅ Giro realizado el {$r->fecha_giro->format('d/m/Y')} — {$r->forma_giro}\n\n"
                                : "⏳ Pendiente de giro.\n\n")
                            . "— {$empresa}\n☎️ " . ($company?->celular ?? '318 693 4710');

                        $wap     = app(\App\Services\WhatsAppService::class);
                        $enviado = false;

                        if ($wap->isConnected()) {
                            // Generar PDF temporal para adjuntar
                            try {
                                $r->load(['propietario','property.tipo','rentalContract.arrendatario','statusHistories.usuario']);
                                $company2    = \App\Models\Company::with('municipio')->first();
                                $logoBase64  = null;
                                if ($company2?->logo_path) {
                                    $p = storage_path('app/public/' . $company2->logo_path);
                                    if (file_exists($p)) $logoBase64 = 'data:' . mime_content_type($p) . ';base64,' . base64_encode(file_get_contents($p));
                                }
                                $liquidation = $r;
                                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.liquidacion-propietario', compact('liquidation','company2','logoBase64'))->setPaper('letter','portrait');
                                $tmpPath = storage_path('app/tmp/liq-' . $r->numero . '-' . time() . '.pdf');
                                if (!is_dir(dirname($tmpPath))) mkdir(dirname($tmpPath), 0755, true);
                                file_put_contents($tmpPath, $pdf->output());
                                $res     = $wap->enviarConArchivo($celular, $msg, $tmpPath, 'Liquidacion-' . $r->numero . '.pdf');
                                $enviado = $res['ok'] ?? false;
                                if (file_exists($tmpPath)) @unlink($tmpPath);
                            } catch (\Throwable) {
                                $res     = $wap->enviar($celular, $msg);
                                $enviado = $res['ok'] ?? false;
                            }
                        }

                        if ($enviado) {
                            $r->update(['wap_enviado' => true, 'wap_enviado_at' => now()]);
                            Notification::make()->title('✅ Liquidación enviada al propietario por WhatsApp')->success()->send();
                        } else {
                            $fallback = \App\Helpers\WhatsApp::urlFallback($celular, $msg);
                            Notification::make()
                                ->title('WhatsApp no disponible — abra el enlace manualmente')
                                ->body($fallback)
                                ->warning()->send();
                        }
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

                TableAction::make('pdf')
                    ->label('PDF')->icon('heroicon-o-arrow-down-tray')->color('gray')
                    ->url(fn(OwnerLiquidation $r) => route('liquidacion.pdf', $r))
                    ->openUrlInNewTab(),

                EditAction::make()->label('Editar'),
                DeleteAction::make()->label('Eliminar'),
                RestoreAction::make(),
            ])
            ->toolbarActions([
                TableAction::make('generar_mes')
                    ->label('Generar liquidaciones')
                    ->icon('heroicon-o-bolt')
                    ->extraAttributes([
                        'style' => 'background:linear-gradient(135deg,#d97706,#f59e0b)!important;color:#fff!important;border:none!important;box-shadow:0 4px 14px rgba(217,119,6,.35)!important;font-weight:700!important;',
                    ])
                    ->modalHeading('Generar liquidaciones por período')
                    ->modalDescription('Elige el mes o un rango de meses (ej. de junio a julio) para generar las liquidaciones de las facturas ya pagadas en ese período.')
                    ->schema([
                        Forms\Components\Select::make('mes_desde')->label('Mes desde')
                            ->options(array_combine(range(1,12), ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre']))
                            ->required()->default(now()->month),
                        Forms\Components\TextInput::make('anio_desde')
                            ->label('Año desde')->numeric()->required()->default(now()->year),
                        Forms\Components\Select::make('mes_hasta')->label('Mes hasta')
                            ->options(array_combine(range(1,12), ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre']))
                            ->required()->default(now()->month),
                        Forms\Components\TextInput::make('anio_hasta')
                            ->label('Año hasta')->numeric()->required()->default(now()->year),
                    ])
                    ->action(function(array $data) {
                        $desde = ((int) $data['anio_desde']) * 100 + (int) $data['mes_desde'];
                        $hasta = ((int) $data['anio_hasta']) * 100 + (int) $data['mes_hasta'];

                        if ($desde > $hasta) {
                            Notification::make()->title('El período "desde" es posterior al "hasta"')->danger()->send();
                            return;
                        }

                        $bills = RentBill::whereRaw('(anio * 100 + mes) >= ?', [$desde])
                            ->whereRaw('(anio * 100 + mes) <= ?', [$hasta])
                            ->where('estado', 'pagada')
                            ->whereNull('owner_liquidation_id')
                            ->get();
                        $n = 0;
                        foreach ($bills as $b) {
                            if (OwnerLiquidation::generarDesdeFact($b)) $n++;
                        }
                        Notification::make()->title("{$n} liquidaciones generadas")->success()->send();
                    }),
                TableAction::make('reporte_pdf')
                    ->label('Reporte PDF del mes')
                    ->icon('heroicon-o-document-arrow-down')
                    ->extraAttributes([
                        'style' => 'background:linear-gradient(135deg,#1e3a8a,#2563eb)!important;color:#fff!important;border:none!important;box-shadow:0 4px 14px rgba(30,58,138,.3)!important;font-weight:700!important;',
                    ])
                    ->schema([
                        Forms\Components\Select::make('mes')->label('Mes')
                            ->options(array_combine(range(1,12), ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre']))
                            ->required()->default(now()->month),
                        Forms\Components\TextInput::make('anio')->label('Año')->numeric()->required()->default(now()->year),
                    ])
                    ->action(function(array $data, \Livewire\Component $livewire) {
                        $url = route('liquidacion.reporte.pdf', ['mes' => $data['mes'], 'anio' => $data['anio']]);
                        $livewire->js("window.open('{$url}', '_blank')");
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
            'edit'   => EditOwnerLiquidation::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}

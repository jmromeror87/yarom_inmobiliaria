<?php
namespace App\Filament\Resources\OwnerLiquidations\Pages;

use App\Filament\Resources\OwnerLiquidations\OwnerLiquidationResource;
use App\Models\Bank;
use App\Models\Company;
use App\Services\WhatsAppService;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class EditOwnerLiquidation extends EditRecord
{
    protected static string $resource = OwnerLiquidationResource::class;

    public function form(Schema $schema): Schema
    {
        // Las liquidaciones pagadas o anuladas quedan de solo lectura — se
        // sigue pudiendo ver, enviar por WhatsApp, descargar PDF y consultar
        // el historial desde los header actions, solo no se editan los
        // valores económicos ya girados/cerrados.
        return parent::form($schema)
            ->disabled(fn () => in_array($this->record->estado, ['pagada', 'anulada']));
    }

    protected function getHeaderActions(): array
    {
        $record = $this->record;

        return [
            Actions\Action::make('aprobar')
                ->label('Aprobar')
                ->icon('heroicon-o-check-badge')
                ->color('info')
                ->outlined()
                ->requiresConfirmation()
                ->visible(fn () => $this->record->estado === 'pendiente')
                ->action(function () {
                    $this->record->update(['estado' => 'aprobada']);
                    Notification::make()->title('Liquidación aprobada')->success()->send();
                    $this->redirect(static::getResource()::getUrl('edit', ['record' => $this->record]));
                }),

            Actions\Action::make('registrar_giro')
                ->label('Registrar giro')
                ->icon('heroicon-o-banknotes')
                ->color('success')
                ->visible(fn () => $this->record->estado === 'aprobada')
                ->schema([
                    DatePicker::make('fecha_giro')->label('Fecha de giro')->required()->default(now())->native(false),
                    Select::make('forma_giro')
                        ->label('Forma de giro')
                        ->options(['transferencia' => 'Transferencia', 'consignacion' => 'Consignación', 'cheque' => 'Cheque', 'efectivo' => 'Efectivo'])
                        ->native(false)
                        ->live()
                        ->required(),
                    Select::make('banco_giro_id')
                        ->label('Cuenta de la que sale el dinero')
                        ->options(fn () => Bank::where('is_active', true)
                            ->where('tipo_cuenta', '!=', 'caja')
                            ->get()
                            ->mapWithKeys(fn ($b) => [$b->id => $b->nombre . ($b->numero_cuenta ? " — {$b->numero_cuenta}" : '')]))
                        ->native(false)
                        ->searchable()
                        ->visible(fn (Get $get) => $get('forma_giro') !== 'efectivo')
                        ->required(fn (Get $get) => $get('forma_giro') !== 'efectivo')
                        ->helperText('De qué cuenta (Bancolombia, Crediservir, etc.) realmente salió la plata.'),
                    TextInput::make('referencia_giro')->label('Referencia / N° transacción'),
                    FileUpload::make('comprobante_giro_path')
                        ->label('Comprobante')
                        ->directory('liquidaciones/comprobantes')
                        ->acceptedFileTypes(['image/*', 'application/pdf'])
                        ->maxSize(5120),
                ])
                ->action(function (array $data) {
                    $this->record->update([
                        'estado'                => 'pagada',
                        'fecha_giro'            => $data['fecha_giro'],
                        'forma_giro'            => $data['forma_giro'],
                        'banco_giro_id'         => $data['banco_giro_id'] ?? null,
                        'referencia_giro'       => $data['referencia_giro'] ?? null,
                        'comprobante_giro_path' => $data['comprobante_giro_path'] ?? null,
                    ]);
                    Notification::make()->title('Giro registrado — Liquidación pagada')->success()->send();
                    $this->redirect(static::getResource()::getUrl('edit', ['record' => $this->record]));
                }),

            Actions\Action::make('enviar_wap')
                ->label('WhatsApp')
                ->icon('heroicon-o-chat-bubble-left-ellipsis')
                ->color('success')
                ->outlined()
                ->visible(fn () => in_array($this->record->estado, ['aprobada', 'pagada']))
                ->action(function () {
                    $r = $this->record;
                    $propietario = $r->propietario;
                    $celular = $propietario?->celular;

                    if (!$celular) {
                        Notification::make()->title('El propietario no tiene celular registrado')->danger()->send();
                        return;
                    }

                    $company = Company::first();
                    $empresa = $company?->razon_social ?? 'Serviarrendar S.A.S';
                    $meses = [1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre'];
                    $periodo = ($meses[$r->mes] ?? $r->mes) . ' ' . $r->anio;

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

                    $wap = app(WhatsAppService::class);
                    $enviado = false;

                    if ($wap->isConnected()) {
                        try {
                            $r->load(['propietario', 'property.tipo', 'rentalContract.arrendatario', 'statusHistories.usuario']);
                            $company2 = Company::with('municipio')->first();
                            $logoBase64 = null;
                            if ($company2?->logo_path) {
                                $p = storage_path('app/public/' . $company2->logo_path);
                                if (file_exists($p)) $logoBase64 = 'data:' . mime_content_type($p) . ';base64,' . base64_encode(file_get_contents($p));
                            }
                            $liquidation = $r;
                            $elaboradoPor = auth()->user()?->name;
                            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.liquidacion-propietario', compact('liquidation', 'company2', 'logoBase64', 'elaboradoPor'))->setPaper('letter', 'portrait');
                            $tmpPath = storage_path('app/tmp/liq-' . $r->numero . '-' . time() . '.pdf');
                            if (!is_dir(dirname($tmpPath))) mkdir(dirname($tmpPath), 0755, true);
                            file_put_contents($tmpPath, $pdf->output());
                            $res = $wap->enviarConArchivo($celular, $msg, $tmpPath, 'Liquidacion-' . $r->numero . '.pdf');
                            $enviado = $res['ok'] ?? false;
                            if (file_exists($tmpPath)) @unlink($tmpPath);
                        } catch (\Throwable) {
                            $res = $wap->enviar($celular, $msg);
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

            Actions\Action::make('historial')
                ->label('Historial')
                ->icon('heroicon-o-clock')
                ->color('gray')
                ->outlined()
                ->modalHeading('Historial — ' . $record->numero)
                ->modalContent(fn () => view(
                    'filament.modals.liquidacion-historial',
                    ['historial' => $this->record->statusHistories()->with('usuario')->get()]
                ))
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Cerrar'),

            Actions\Action::make('pdf')
                ->label('PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->outlined()
                ->url(fn () => route('liquidacion.pdf', $this->record))
                ->openUrlInNewTab(),

            Actions\Action::make('anular')
                ->label('Anular liquidación')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->outlined()
                ->visible(fn () => $this->record->estado !== 'anulada')
                ->requiresConfirmation()
                ->modalHeading('Anular liquidación')
                ->modalDescription(fn () => $this->record->estado === 'pagada'
                    ? "⚠️ Esta liquidación YA FUE GIRADA (\${$this->record->total_giro} el " . ($this->record->fecha_giro?->format('d/m/Y') ?? '—') . " a {$this->record->propietario?->nombre_completo}). Anularla reversa el asiento contable del giro, pero el dinero físico ya salió — coordina aparte cómo se recupera o compensa. La factura del inquilino queda libre para volver a liquidarse."
                    : 'La liquidación queda anulada y no se pierde el registro — puede consultarse siempre, y la factura del inquilino queda libre para volver a liquidarse. Se reversa cualquier asiento contable ya causado.')
                ->modalSubmitActionLabel('Sí, anular')
                ->schema([
                    Textarea::make('motivo_anulacion')
                        ->label('Motivo de anulación')
                        ->required()
                        ->minLength(10)
                        ->rows(3)
                        ->placeholder('Ej: canon mal calculado, propietario equivocado, inmueble equivocado...'),
                ])
                ->action(function (array $data) {
                    try {
                        $this->record->anularConReversion($data['motivo_anulacion'], Auth::id());
                        Notification::make()
                            ->title('Liquidación anulada')
                            ->body('Se reversaron los asientos contables y la factura del inquilino quedó libre para volver a liquidarse.')
                            ->warning()->send();
                        $this->redirect(static::getResource()::getUrl('index'));
                    } catch (\Throwable $e) {
                        Notification::make()->title('Error al anular: ' . $e->getMessage())->danger()->send();
                    }
                }),
            Actions\RestoreAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

<?php

namespace App\Filament\Resources\RentBills\Pages;

use App\Filament\Resources\RentBills\RentBillResource;
use App\Models\Bank;
use App\Models\Company;
use App\Models\RentPayment;
use App\Helpers\WhatsApp;
use App\Services\WhatsAppService;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditRentBill extends EditRecord
{
    protected static string $resource = RentBillResource::class;

    public function getView(): string
    {
        return 'filament.rent-bills.edit-invoice';
    }

    protected function getHeaderActions(): array
    {
        $record   = $this->record->load(['arrendatario','rentalContract','payments','property']);
        $acciones = [];

        // ── Badge pagada ───────────────────────────────────
        if ($record->estado === 'pagada') {
            $acciones[] = Action::make('badge_pagada')
                ->label('✅ PAGADA')->color('success')->disabled();
        }

        // ── Registrar pago ────────────────────────────────
        if (!in_array($record->estado, ['pagada','anulada'])) {
            $acciones[] = Action::make('registrar_pago')
                ->label('💰 Registrar pago')
                ->color('success')
                ->icon('heroicon-o-banknotes')
                ->modalHeading('Registrar pago')
                ->modalDescription(fn () => "Factura {$record->numero} — {$record->arrendatario?->nombre_completo}")
                ->modalSubmitActionLabel('✓ Registrar pago')
                ->slideOver()
                ->modalWidth('md')
                ->modalFooterActionsAlignment('start')
                ->schema([
                    self::grupoLabel('💰 Valor y fecha'),
                    Grid::make(2)->schema([
                        TextInput::make('total_pagado')
                            ->label('Valor recibido')
                            ->numeric()->prefix('$')
                            ->default(fn () => $record->saldo_pendiente + $record->mora_acumulada)
                            ->required(),
                        DatePicker::make('fecha_pago')
                            ->label('Fecha de pago')
                            ->default(now())
                            ->native(false)
                            ->required(),
                    ]),

                    self::grupoLabel('💳 Método de pago', 'El destino contable del dinero se asigna automáticamente según lo que elijas aquí.'),
                    Select::make('forma_pago')
                        ->label('Forma de pago')
                        ->options([
                            'efectivo'      => '💵 Efectivo',
                            'transferencia' => '🏦 Transferencia',
                            'consignacion'  => '🏧 Consignación',
                            'nequi'         => '📱 Nequi',
                            'daviplata'     => '📱 Daviplata',
                            'pse'           => '💻 PSE',
                            'cheque'        => '📝 Cheque',
                        ])
                        ->default('transferencia')
                        ->native(false)
                        ->live()
                        ->required()
                        ->columnSpanFull()
                        ->afterStateUpdated(function ($state, callable $set) {
                            if ($state === 'efectivo') {
                                $set('bank_id', Bank::where('tipo_cuenta', 'caja')->value('id'));
                            } else {
                                $set('bank_id', null);
                            }
                        }),

                    Select::make('bank_id')
                        ->label('Cuenta destino')
                        ->options(fn () => Bank::where('is_active', true)
                            ->where('tipo_cuenta', '!=', 'caja')
                            ->get()
                            ->mapWithKeys(fn ($b) => [$b->id => $b->nombre . ($b->numero_cuenta ? " — {$b->numero_cuenta}" : '')]))
                        ->native(false)
                        ->searchable()
                        ->columnSpanFull()
                        ->visible(fn (Get $get) => $get('forma_pago') !== 'efectivo')
                        ->required(fn (Get $get) => $get('forma_pago') !== 'efectivo')
                        ->helperText('Cuenta bancaria donde efectivamente entró el dinero — determina a qué cuenta contable se contabiliza.'),

                    Placeholder::make('info_caja')
                        ->label('')
                        ->columnSpanFull()
                        ->content('💵 Este pago se contabilizará en Caja general.')
                        ->visible(fn (Get $get) => $get('forma_pago') === 'efectivo'),

                    self::grupoLabel('📎 Soporte'),
                    Grid::make(2)->schema([
                        TextInput::make('referencia_pago')->label('Referencia / N° comprobante'),
                        TextInput::make('banco_origen')->label('Banco de origen del pagador')
                            ->helperText('Ej: si pagó por Nequi, aquí puedes anotar de qué banco venía la plata.'),
                    ]),
                    FileUpload::make('comprobante_path')
                        ->label('Comprobante de pago')
                        ->disk('public')->directory('pagos/comprobantes')
                        ->acceptedFileTypes(['application/pdf','image/jpeg','image/png'])
                        ->maxSize(5120)
                        ->columnSpanFull(),
                    Textarea::make('notas')->label('Notas')->rows(2)->columnSpanFull(),
                ])
                ->action(function (array $data) {
                    $mora  = $this->record->mora_acumulada;
                    $canon = max(0, $data['total_pagado'] - $mora);

                    RentPayment::create([
                        'rent_bill_id'        => $this->record->id,
                        'rental_contract_id'  => $this->record->rental_contract_id,
                        'arrendatario_id'     => $this->record->arrendatario_id,
                        'registrado_por'      => Auth::id(),
                        'valor_canon'         => $canon,
                        'valor_mora'          => $mora,
                        'valor_administracion'=> $this->record->cuota_administracion,
                        'total_pagado'        => $data['total_pagado'],
                        'forma_pago'          => $data['forma_pago'],
                        'fecha_pago'          => $data['fecha_pago'],
                        'referencia_pago'     => $data['referencia_pago'] ?? null,
                        'banco_origen'        => $data['banco_origen'] ?? null,
                        'bank_id'             => $data['bank_id'] ?? null,
                        'comprobante_path'    => $data['comprobante_path'] ?? null,
                        'notas'               => $data['notas'] ?? null,
                    ]);

                    Notification::make()
                        ->title('✅ Pago registrado — Liquidación al propietario generada')
                        ->success()->send();

                    $this->redirect(static::getResource()::getUrl('edit', ['record' => $this->record]));
                });
        }

        // ── Enviar recordatorio WhatsApp ───────────────────
        if (!in_array($record->estado, ['pagada','anulada']) && $record->arrendatario?->celular) {
            $acciones[] = Action::make('enviar_recordatorio')
                ->label('📱 Recordatorio WA')
                ->color('info')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->action(function () {
                    $r     = $this->record;
                    $saldo = '$' . number_format($r->saldo_pendiente + $r->mora_acumulada, 0, ',', '.');
                    $mora  = $r->mora_acumulada > 0 ? "\n📈 Mora: $" . number_format($r->mora_acumulada, 0, ',', '.') : '';
                    $msg   = "Recordatorio de pago — Serviarrendar S.A.S\n\n" .
                             "Estimado(a) {$r->arrendatario->nombre_completo},\n\n" .
                             "📋 Factura: {$r->numero}\n" .
                             "💵 Saldo pendiente: {$saldo}{$mora}\n" .
                             "📆 Venció: {$r->fecha_limite_pago->format('d/m/Y')}\n\n" .
                             "Por favor regularice su pago.\n\nServiarrendar S.A.S ☎️ 3186934710";

                    WhatsApp::enviar($r->arrendatario->celular, $msg);
                    Notification::make()->title('📱 Recordatorio enviado')->success()->send();
                    $this->redirect(static::getResource()::getUrl('edit', ['record' => $this->record]));
                });
        }

        // ── PDF ────────────────────────────────────────────
        $acciones[] = Action::make('pdf')
            ->label('📄 PDF')
            ->color('gray')
            ->icon('heroicon-o-arrow-down-tray')
            ->url(fn () => route('factura.pdf', $record))
            ->openUrlInNewTab();

        return $acciones;
    }

    public function enviarReciboWhatsapp(int $paymentId): void
    {
        $payment = RentPayment::with(['bill.property', 'bill.rentalContract', 'arrendatario', 'bank', 'registradoPor'])
            ->findOrFail($paymentId);

        $celular = $payment->arrendatario?->celular;

        if (!$celular) {
            Notification::make()->title('El arrendatario no tiene celular registrado')->danger()->send();
            return;
        }

        $company = Company::with('municipio')->first();
        $bill    = $payment->bill;

        $msg = "🧾 *Recibo de pago*\n\n"
            . "Estimado(a) {$payment->arrendatario?->nombre_completo},\n\n"
            . "Hemos registrado tu pago correspondiente a la factura {$bill?->numero}.\n\n"
            . "💵 Valor: \$" . number_format((float) $payment->total_pagado, 0, ',', '.') . " COP\n"
            . "📆 Fecha: {$payment->fecha_pago->format('d/m/Y')}\n"
            . "🏠 Inmueble: {$bill?->property?->codigo} — {$bill?->property?->direccion}\n\n"
            . "Adjuntamos el recibo en PDF.\n\n"
            . '— ' . ($company?->razon_social ?? 'Serviarrendar S.A.S') . "\n☎️ " . ($company?->celular ?? '318 693 4710');

        $wap     = app(WhatsAppService::class);
        $enviado = false;

        if ($wap->isConnected()) {
            try {
                $logoBase64 = null;
                if ($company?->logo_path) {
                    $p = storage_path('app/public/' . $company->logo_path);
                    if (file_exists($p)) $logoBase64 = 'data:' . mime_content_type($p) . ';base64,' . base64_encode(file_get_contents($p));
                }
                $pdf     = Pdf::loadView('pdf.recibo-pago', compact('payment', 'company', 'logoBase64'))->setPaper('a5', 'landscape');
                $tmpPath = storage_path('app/tmp/recibo-' . $payment->numero . '-' . time() . '.pdf');
                if (!is_dir(dirname($tmpPath))) mkdir(dirname($tmpPath), 0755, true);
                file_put_contents($tmpPath, $pdf->output());
                $res     = $wap->enviarConArchivo($celular, $msg, $tmpPath, 'Recibo-' . $payment->numero . '.pdf');
                $enviado = $res['ok'] ?? false;
                if (file_exists($tmpPath)) @unlink($tmpPath);
            } catch (\Throwable) {
                $res     = $wap->enviar($celular, $msg);
                $enviado = $res['ok'] ?? false;
            }
        }

        if ($enviado) {
            Notification::make()->title('✅ Recibo enviado por WhatsApp')->success()->send();
        } else {
            $fallback = WhatsApp::urlFallback($celular, $msg);
            Notification::make()
                ->title('WhatsApp no disponible — abra el enlace manualmente')
                ->body($fallback)
                ->warning()->send();
        }
    }

    /**
     * Título de grupo estilo "kicker" (mayúsculas, pequeño, gris) para
     * separar secciones del formulario sin usar cajas/bordes pesados.
     */
    private static function grupoLabel(string $texto, ?string $descripcion = null): Placeholder
    {
        $html = '<div style="margin-top:4px;">'
            . '<span style="font-size:11px;font-weight:800;letter-spacing:.07em;text-transform:uppercase;color:#64748b;">' . e($texto) . '</span>'
            . ($descripcion ? '<p style="font-size:12px;color:#94a3b8;margin-top:2px;">' . e($descripcion) . '</p>' : '')
            . '</div>';

        return Placeholder::make('grupo_' . md5($texto))
            ->hiddenLabel()
            ->columnSpanFull()
            ->content(new \Illuminate\Support\HtmlString($html));
    }
}

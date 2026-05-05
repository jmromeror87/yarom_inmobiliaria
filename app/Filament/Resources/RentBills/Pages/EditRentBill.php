<?php

namespace App\Filament\Resources\RentBills\Pages;

use App\Filament\Resources\RentBills\RentBillResource;
use App\Models\RentPayment;
use App\Helpers\WhatsApp;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
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
                ->form([
                    TextInput::make('total_pagado')
                        ->label('Valor recibido ($)')
                        ->numeric()->prefix('$')
                        ->default(fn () => $record->saldo_pendiente + $record->mora_acumulada)
                        ->required(),
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
                        ])->default('transferencia')->required(),
                    DatePicker::make('fecha_pago')
                        ->label('Fecha de pago')->default(now())->required(),
                    TextInput::make('referencia_pago')->label('Referencia / comprobante'),
                    TextInput::make('banco_origen')->label('Banco origen'),
                    FileUpload::make('comprobante_path')
                        ->label('Comprobante de pago')
                        ->disk('public')->directory('pagos/comprobantes')
                        ->acceptedFileTypes(['application/pdf','image/jpeg','image/png'])
                        ->maxSize(5120),
                    Textarea::make('notas')->label('Notas')->rows(2),
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
                             "Estimad@ {$r->arrendatario->nombre_completo},\n\n" .
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
}

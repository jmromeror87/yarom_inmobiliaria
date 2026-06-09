<?php

namespace App\Filament\Resources\ElectronicInvoices\Pages;

use App\Filament\Resources\ElectronicInvoices\ElectronicInvoiceResource;
use App\Services\FacturacionElectronicaService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewElectronicInvoice extends ViewRecord
{
    protected static string $resource = ElectronicInvoiceResource::class;
    protected string $view = 'filament.fe.view-electronic-invoice';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('reintentar')
                ->label('Reintentar envío')
                ->icon('heroicon-o-arrow-path')->color('warning')
                ->visible(fn() => $this->record->puede_reintentar)
                ->requiresConfirmation()
                ->action(function () {
                    $fe = FacturacionElectronicaService::reintentar($this->record);
                    Notification::make()->title('Estado: ' . $fe->estado_label)
                        ->color($fe->es_aceptada ? 'success' : 'warning')->send();
                    $this->record = $fe;
                }),

            Action::make('consultar_dian')
                ->label('Consultar DIAN')
                ->icon('heroicon-o-signal')->color('info')
                ->visible(fn() => !empty($this->record->cufe))
                ->action(function () {
                    $fe = FacturacionElectronicaService::consultarEstado($this->record);
                    Notification::make()->title('Estado actualizado: ' . $fe->estado_label)->info()->send();
                    $this->record = $fe;
                }),

            Action::make('descargar_pdf')
                ->label('Descargar PDF')
                ->icon('heroicon-o-arrow-down-tray')->color('gray')
                ->visible(fn() => $this->record->es_aceptada && $this->record->pdf_url)
                ->url(fn() => $this->record->pdf_url, shouldOpenInNewTab: true),

            Action::make('anular')
                ->label('Emitir Nota Crédito (Anular)')
                ->icon('heroicon-o-x-circle')->color('danger')
                ->visible(fn() => $this->record->puede_anular)
                ->requiresConfirmation()
                ->schema([
                    \Filament\Forms\Components\Textarea::make('razon')
                        ->label('Razón de anulación')->required()->rows(3),
                ])
                ->action(function (array $data) {
                    $fe = FacturacionElectronicaService::anular($this->record, $data['razon']);
                    Notification::make()
                        ->title($fe->estado === 'anulada' ? 'FE anulada correctamente' : 'Error: ' . $fe->ultimo_error)
                        ->color($fe->estado === 'anulada' ? 'warning' : 'danger')->send();
                    $this->record = $fe;
                }),
        ];
    }
}

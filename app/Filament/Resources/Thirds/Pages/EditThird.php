<?php

namespace App\Filament\Resources\Thirds\Pages;

use App\Filament\Resources\Thirds\ThirdResource;
use App\Filament\Widgets\ThirdHeroWidget;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EditThird extends EditRecord
{
    protected static string $resource = ThirdResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('descargar_habeas_data')
                ->label('Habeas Data PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('info')
                ->outlined()
                ->action(function (): StreamedResponse {
                    $third  = $this->record;
                    $pdf    = Pdf::loadView('pdf.habeas-data', compact('third'))->setPaper('letter', 'portrait');
                    $nombre = 'HabeasData_' . str_replace(' ', '_', $third->nombre_completo ?: 'tercero') . '.pdf';
                    return response()->streamDownload(fn () => print($pdf->output()), $nombre);
                }),
            DeleteAction::make()
                ->label('Eliminar Tercero')
                ->icon('heroicon-o-trash')
                ->outlined()
                ->requiresConfirmation()
                ->modalHeading('¿Eliminar este tercero?')
                ->modalDescription('Esta acción no se puede deshacer. Se eliminará toda la información del tercero. Solo administradores pueden realizar esta acción.')
                ->modalSubmitActionLabel('Sí, eliminar')
                ->visible(fn () => auth()->user()?->hasAnyRole(['super_admin', 'admin'])),
            ForceDeleteAction::make()->outlined()
                ->visible(fn () => auth()->user()?->hasAnyRole(['super_admin', 'admin'])),
            RestoreAction::make()->outlined()
                ->visible(fn () => auth()->user()?->hasAnyRole(['super_admin', 'admin'])),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [ThirdHeroWidget::class];
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return 1;
    }

    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()
            ->label('Guardar cambios')
            ->icon('heroicon-o-check-circle')
            ->extraAttributes([
                'style' => 'background:linear-gradient(135deg,#1e3a8a,#E11D48)!important;border:none!important;color:#fff!important;font-weight:700!important;border-radius:10px!important;padding:9px 22px!important;box-shadow:0 3px 10px rgba(225,29,72,.3)!important;',
            ]);
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()
            ->label('Cancelar')
            ->outlined()
            ->extraAttributes([
                'style' => 'border-radius:10px!important;font-weight:600!important;border-color:#cbd5e1!important;color:#475569!important;',
            ]);
    }
}

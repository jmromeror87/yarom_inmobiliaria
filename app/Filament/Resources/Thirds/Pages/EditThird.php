<?php
/*
|--------------------------------------------------------------------------
| YarOM ERP - Soluciones de Gestión
|--------------------------------------------------------------------------
| Proyecto privado desarrollado por:
| Ingeniero Jhoan Romero Rivera
| LinkedIn: https://linkedin.com/in/jmromeror87
|
| Módulo: \1
| Archivo: EditThird.php
| Fecha: CURRENT_DAY/05/2026
| Versión: v1.0
|--------------------------------------------------------------------------
*/


namespace App\Filament\Resources\Thirds\Pages;

use App\Filament\Resources\Thirds\ThirdResource;
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
                ->action(function (): StreamedResponse {
                    $third = $this->record;
                    $pdf = Pdf::loadView('pdf.habeas-data', compact('third'))
                        ->setPaper('letter', 'portrait');

                    $nombre = 'HabeasData_' . str_replace(' ', '_', $third->nombre_completo ?: 'tercero') . '.pdf';

                    return response()->streamDownload(
                        fn () => print($pdf->output()),
                        $nombre
                    );
                }),

            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}

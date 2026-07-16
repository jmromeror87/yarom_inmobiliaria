<?php
namespace App\Filament\Resources\RentalContracts\Pages;

use App\Exports\RentalContracts\RentalContractImportReportExporter;
use App\Exports\RentalContracts\RentalContractTemplateExporter;
use App\Filament\Resources\RentalContracts\RentalContractResource;
use App\Services\RentalContractImportService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListRentalContracts extends ListRecords
{
    protected static string $resource = RentalContractResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('descargarPlantillaArriendo')
                ->label('Descargar plantilla')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action(fn () => (new RentalContractTemplateExporter())->stream('plantilla_contratos_arriendo.xlsx')),

            Action::make('validarExcelArriendo')
                ->label('Validar archivo')
                ->icon('heroicon-o-shield-check')
                ->color('gray')
                ->schema([
                    FileUpload::make('archivo')
                        ->label('Archivo Excel (.xlsx)')
                        ->helperText('Revisa el archivo sin crear ningún contrato todavía.')
                        ->required()
                        ->disk('local')
                        ->directory('imports-temp')
                        ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                        ->visibility('private'),
                ])
                ->action(fn (array $data) => $this->procesarImportacion($data, dryRun: true)),

            Action::make('importarExcelArriendo')
                ->label('Importar Excel')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('gray')
                ->schema([
                    FileUpload::make('archivo')
                        ->label('Archivo Excel (.xlsx)')
                        ->required()
                        ->disk('local')
                        ->directory('imports-temp')
                        ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                        ->visibility('private'),
                ])
                ->action(fn (array $data) => $this->procesarImportacion($data, dryRun: false)),

            CreateAction::make()
                ->label('Crear contrato')
                ->icon('heroicon-o-plus')
                ->extraAttributes([
                    'style' => 'background:linear-gradient(135deg,#1e3a8a,#E11D48)!important;color:#fff!important;border:none!important;box-shadow:0 4px 14px rgba(30,58,138,.35)!important;font-weight:700!important;',
                ]),
        ];
    }

    private function procesarImportacion(array $data, bool $dryRun): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $relativePath = $data['archivo'];
        $fullPath     = storage_path('app/private/' . $relativePath);
        if (! file_exists($fullPath)) {
            $fullPath = storage_path('app/' . $relativePath);
        }

        $resultado = (new RentalContractImportService())->importFrom($fullPath, dryRun: $dryRun);
        @unlink($fullPath);

        $validos = count($resultado['validos']);
        $creados = count($resultado['creados']);
        $errores = count($resultado['errores']);

        if ($dryRun) {
            $body = "Listos para importar: {$validos} · Con error: {$errores}";
            $titulo = 'Validación completada — no se creó ningún contrato';
        } else {
            $body = "Creados: {$creados} · Con error: {$errores}";
            $titulo = 'Importación de contratos de arriendo completada';
        }

        Notification::make()
            ->title($titulo)
            ->body($body . ' — descargando reporte detallado.')
            ->color($errores > 0 ? 'warning' : 'success')
            ->duration(10000)
            ->send();

        $nombreArchivo = $dryRun ? 'validacion_contratos_arriendo.xlsx' : 'reporte_importacion_contratos_arriendo.xlsx';

        return (new RentalContractImportReportExporter())->stream($resultado['filas'], $nombreArchivo);
    }
}

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
| Archivo: ListAdministrationContracts.php
| Fecha: CURRENT_DAY/05/2026
| Versión: v1.0
|--------------------------------------------------------------------------
*/
    

namespace App\Filament\Resources\AdministrationContracts\Pages;

use App\Exports\AdministrationContracts\AdministrationContractImportReportExporter;
use App\Exports\AdministrationContracts\AdministrationContractTemplateExporter;
use App\Filament\Resources\AdministrationContracts\AdministrationContractResource;
use App\Services\AdministrationContractImportService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListAdministrationContracts extends ListRecords
{
    protected static string $resource = AdministrationContractResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('descargarPlantillaContratos')
                ->label('Descargar plantilla')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action(fn () => (new AdministrationContractTemplateExporter())->stream('plantilla_contratos_administracion.xlsx')),

            Action::make('validarExcelContratos')
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

            Action::make('importarExcelContratos')
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
                ->label('Crear Contrato')
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

        $resultado = (new AdministrationContractImportService())->importFrom($fullPath, dryRun: $dryRun);
        @unlink($fullPath);

        $validos = count($resultado['validos']);
        $creados = count($resultado['creados']);
        $errores = count($resultado['errores']);

        if ($dryRun) {
            $body = "Listos para importar: {$validos} · Con error: {$errores}";
            $titulo = 'Validación completada — no se creó ningún contrato';
        } else {
            $body = "Creados: {$creados} · Con error: {$errores}";
            $titulo = 'Importación de contratos completada';
        }

        Notification::make()
            ->title($titulo)
            ->body($body . ' — descargando reporte detallado.')
            ->color($errores > 0 ? 'warning' : 'success')
            ->duration(10000)
            ->send();

        $nombreArchivo = $dryRun ? 'validacion_contratos.xlsx' : 'reporte_importacion_contratos.xlsx';

        return (new AdministrationContractImportReportExporter())->stream($resultado['filas'], $nombreArchivo);
    }
}

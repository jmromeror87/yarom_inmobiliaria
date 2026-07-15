<?php

namespace App\Filament\Resources\Properties\Pages;

use App\Exports\Properties\PropertyImportReportExporter;
use App\Exports\Properties\PropertyTemplateExporter;
use App\Filament\Resources\Properties\PropertyResource;
use App\Filament\Widgets\PropertiesStatsWidget;
use App\Services\PropertyImportService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Livewire\Attributes\On;

class ListProperties extends ListRecords
{
    protected static string $resource = PropertyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('descargarPlantillaInmuebles')
                ->label('Descargar plantilla')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action(fn () => (new PropertyTemplateExporter())->stream('plantilla_inmuebles.xlsx')),

            Action::make('validarExcelInmuebles')
                ->label('Validar archivo')
                ->icon('heroicon-o-shield-check')
                ->color('gray')
                ->schema([
                    FileUpload::make('archivo')
                        ->label('Archivo Excel (.xlsx)')
                        ->helperText('Revisa el archivo sin crear ningún inmueble todavía.')
                        ->required()
                        ->disk('local')
                        ->directory('imports-temp')
                        ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                        ->visibility('private'),
                ])
                ->action(fn (array $data) => $this->procesarImportacion($data, dryRun: true)),

            Action::make('importarExcelInmuebles')
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
                ->label('Crear Inmueble')
                ->icon('heroicon-o-plus-circle')
                ->extraAttributes([
                    'style' => 'background:linear-gradient(135deg,#1e3a8a,#E11D48)!important;border:none!important;color:#fff!important;font-weight:800!important;letter-spacing:.02em!important;padding:10px 22px!important;border-radius:12px!important;box-shadow:0 4px 14px rgba(225,29,72,.35)!important;transition:transform .12s,box-shadow .12s!important;',
                    'onmouseover' => "this.style.transform='translateY(-1px)';this.style.boxShadow='0 6px 20px rgba(225,29,72,.45)'",
                    'onmouseout'  => "this.style.transform='';this.style.boxShadow='0 4px 14px rgba(225,29,72,.35)'",
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

        $resultado = (new PropertyImportService())->importFrom($fullPath, dryRun: $dryRun);
        @unlink($fullPath);

        $validos = count($resultado['validos']);
        $creados = count($resultado['creados']);
        $errores = count($resultado['errores']);

        if ($dryRun) {
            $body = "Listos para importar: {$validos} · Con error: {$errores}";
            $titulo = 'Validación completada — no se creó ningún inmueble';
        } else {
            $body = "Creados: {$creados} · Con error: {$errores}";
            $titulo = 'Importación de inmuebles completada';
        }

        Notification::make()
            ->title($titulo)
            ->body($body . ' — descargando reporte detallado.')
            ->color($errores > 0 ? 'warning' : 'success')
            ->duration(10000)
            ->send();

        $nombreArchivo = $dryRun ? 'validacion_inmuebles.xlsx' : 'reporte_importacion_inmuebles.xlsx';

        return (new PropertyImportReportExporter())->stream($resultado['filas'], $nombreArchivo);
    }

    protected function getHeaderWidgets(): array
    {
        return [PropertiesStatsWidget::class];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 1;
    }

    #[On('properties-filter')]
    public function applyPropertiesFilter(string $filter, string $value): void
    {
        $this->tableFilters[$filter] = ['value' => $value];
    }

    #[On('properties-filter-clear')]
    public function clearPropertiesFilter(): void
    {
        $this->resetTableFiltersForm();
    }
}

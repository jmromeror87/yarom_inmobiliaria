<?php

namespace App\Filament\Resources\Thirds\Pages;

use App\Exports\Thirds\ThirdImportReportExporter;
use App\Exports\Thirds\ThirdTemplateExporter;
use App\Filament\Resources\Thirds\ThirdResource;
use App\Services\ThirdImportService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Livewire\Attributes\On;

class ListThirds extends ListRecords
{
    protected static string $resource = ThirdResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('descargarPlantilla')
                ->label('Descargar plantilla')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action(fn () => (new ThirdTemplateExporter())->stream('plantilla_terceros.xlsx')),

            Action::make('validarExcel')
                ->label('Validar archivo')
                ->icon('heroicon-o-shield-check')
                ->color('gray')
                ->schema([
                    FileUpload::make('archivo')
                        ->label('Archivo Excel (.xlsx)')
                        ->helperText('Revisa el archivo y te dice qué filas están listas y cuáles tienen error, sin crear ningún tercero todavía.')
                        ->required()
                        ->disk('local')
                        ->directory('imports-temp')
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        ])
                        ->visibility('private'),
                ])
                ->action(fn (array $data) => $this->procesarImportacion($data, dryRun: true)),

            Action::make('importarExcel')
                ->label('Importar Excel')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('gray')
                ->schema([
                    FileUpload::make('archivo')
                        ->label('Archivo Excel (.xlsx)')
                        ->required()
                        ->disk('local')
                        ->directory('imports-temp')
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        ])
                        ->visibility('private'),
                ])
                ->action(fn (array $data) => $this->procesarImportacion($data, dryRun: false)),

            CreateAction::make()
                ->label('Nuevo Tercero')
                ->icon('heroicon-o-user-plus')
                ->color('primary')
                ->extraAttributes([
                    'style' => 'background:linear-gradient(135deg,#1e3a8a,#E11D48)!important;border:none!important;color:#fff!important;font-weight:800!important;letter-spacing:.02em!important;padding:10px 22px!important;border-radius:12px!important;box-shadow:0 4px 14px rgba(225,29,72,.35)!important;transition:transform .12s,box-shadow .12s!important;--c-action-icon-color:#fff!important;',
                    'onmouseover' => "this.style.transform='translateY(-1px)';this.style.boxShadow='0 6px 20px rgba(225,29,72,.45)'",
                    'onmouseout'  => "this.style.transform='';this.style.boxShadow='0 4px 14px rgba(225,29,72,.35)'",
                ]),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\ThirdsStatsWidget::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return 1;
    }

    #[On('thirds-filter')]
    public function applyThirdsFilter(string $filter, string $value): void
    {
        $this->tableFilters[$filter] = ['value' => $value];
    }

    #[On('thirds-filter-clear')]
    public function clearThirdsFilter(): void
    {
        $this->resetTableFiltersForm();
    }

    private function procesarImportacion(array $data, bool $dryRun): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $relativePath = $data['archivo'];
        $fullPath     = storage_path('app/private/' . $relativePath);

        if (! file_exists($fullPath)) {
            $fullPath = storage_path('app/' . $relativePath);
        }

        $resultado = (new ThirdImportService())->importFrom($fullPath, dryRun: $dryRun);

        @unlink($fullPath);

        $validos   = count($resultado['validos']);
        $creados   = count($resultado['creados']);
        $omitidos  = count($resultado['omitidos_existentes']);
        $errores   = count($resultado['errores']);

        if ($dryRun) {
            $body = "Listos para importar: {$validos} · Ya existen: {$omitidos} · Con error: {$errores}";
            $titulo = 'Validación completada — no se creó ningún tercero';
        } else {
            $body = "Creados: {$creados} · Omitidos (ya existían): {$omitidos} · Con error: {$errores}";
            $titulo = 'Importación de terceros completada';
        }

        Notification::make()
            ->title($titulo)
            ->body($body . ' — descargando reporte detallado con el motivo de cada fila.')
            ->color($errores > 0 ? 'warning' : 'success')
            ->duration(10000)
            ->send();

        $nombreArchivo = $dryRun ? 'validacion_terceros.xlsx' : 'reporte_importacion_terceros.xlsx';

        return (new ThirdImportReportExporter())->stream($resultado['filas'], $nombreArchivo);
    }
}

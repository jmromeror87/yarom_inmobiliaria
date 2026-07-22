<?php

namespace App\Filament\Pages\Reports;

use App\Exports\Reports\ExcelExporter;
use App\Exports\Reports\PdfExporter;
use App\Services\Reports\ReportingService;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InformesContables extends Page
{
    protected string $view = 'filament.pages.reports.informes-contables';
    protected static ?string $title = 'Informes Contables';
    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-chart-bar';
    protected static string|\UnitEnum|null $navigationGroup = 'Contabilidad';
    protected static ?string $navigationLabel = 'Informes Contables';
    protected static ?int    $navigationSort  = 10;

    // Filtros públicos (Livewire — solo tipos simples para evitar error JSON)
    public string $tipoInforme = 'estado_resultados';
    public string $desde       = '';
    public string $hasta       = '';
    public bool   $soloConMov  = true;
    public bool   $calculado   = false;

    // reportData NO es propiedad Livewire — se guarda en sesión para evitar
    // el error "Malformed UTF-8" al serializar strings con tildes/ñ a JSON.
    private const SESSION_KEY = 'informe_contable_data';

    public function mount(): void
    {
        $this->desde = now()->startOfYear()->toDateString();
        $this->hasta = now()->toDateString();
        $this->calculado = session()->has(self::SESSION_KEY);
    }

    public function getReportData(): array
    {
        return session(self::SESSION_KEY, []);
    }

    /** Sanitiza recursivamente todos los strings a UTF-8 válido */
    private function sanitizeUtf8(mixed $value): mixed
    {
        if (is_string($value)) {
            // Convierte a UTF-8 limpio; reemplaza secuencias inválidas
            return mb_convert_encoding($value, 'UTF-8', 'UTF-8');
        }
        if (is_array($value)) {
            return array_map(fn($v) => $this->sanitizeUtf8($v), $value);
        }
        return $value;
    }

    // ── Opciones de informes ──────────────────────────────────────────────

    public function getTiposInforme(): array
    {
        return [
            'estado_resultados' => '📈  Estado de Resultados (P&G)',
            'balance_general'   => '🏢  Balance General',
            'flujo_efectivo'    => '💧  Flujo de Caja',
            'libro_mayor'       => '📚  Libro Mayor',
            'balance_prueba'    => '⚖️  Balance de Comprobación',
            'analisis_cartera'  => '📊  Análisis de Cartera por Antigüedad',
            'informe_retenciones'  => '📋  Informe de Retenciones',
            'informe_comisiones'   => '💰  Comisiones e Ingresos',
            'conciliacion_iva'     => '🧾  Conciliación de IVA',
        ];
    }

    public function getRequiereFechaHasta(): bool
    {
        return in_array($this->tipoInforme, ['balance_general', 'analisis_cartera']);
    }

    // ── Calcular ─────────────────────────────────────────────────────────

    public function calcular(): void
    {
        try {
            $desde = Carbon::parse($this->desde);
            $hasta = Carbon::parse($this->hasta);

            $data = match($this->tipoInforme) {
                'estado_resultados'   => ReportingService::estadoResultados($desde, $hasta),
                'balance_general'     => ReportingService::balanceGeneral($hasta),
                'flujo_efectivo'      => ReportingService::flujoEfectivo($desde, $hasta),
                'balance_prueba'      => ReportingService::balancePrueba($desde, $hasta, $this->soloConMov),
                'libro_mayor'         => ReportingService::libroMayor($desde, $hasta, $this->soloConMov),
                'analisis_cartera'    => ReportingService::analisisCartera($hasta),
                'informe_retenciones' => ReportingService::informeRetenciones($desde, $hasta),
                'informe_comisiones'  => ReportingService::informeComisiones($desde, $hasta),
                'conciliacion_iva'    => ReportingService::conciliacionIVA($desde, $hasta),
                default               => [],
            };

            // Sanitizar a UTF-8 antes de guardar en sesión
            $data = $this->sanitizeUtf8($data);

            session([self::SESSION_KEY => $data]);
            $this->calculado = !empty($data);

            Notification::make()->title('Informe calculado')->success()->send();

        } catch (\Throwable $e) {
            Notification::make()
                ->title('Error: ' . $e->getMessage())
                ->danger()->send();
        }
    }

    // ── Exportar Excel ───────────────────────────────────────────────────

    public function exportarExcel(): StreamedResponse
    {
        $data = $this->getReportData();
        if (empty($data)) {
            $this->calcular();
            $data = $this->getReportData();
        }

        $exporter    = new ExcelExporter($data);
        $spreadsheet = $exporter->generate();
        $filename    = ($data['titulo'] ?? 'Informe') . '_' . now()->format('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    // ── Exportar PDF ────────────────────────────────────────────────────

    public function exportarPdf(): \Symfony\Component\HttpFoundation\Response
    {
        $data = $this->getReportData();
        if (empty($data)) {
            $this->calcular();
            $data = $this->getReportData();
        }

        return (new PdfExporter($data))->download();
    }

    // ── Header actions ───────────────────────────────────────────────────

    protected function getHeaderActions(): array
    {
        return [
            Action::make('excel')
                ->label('Exportar Excel')
                ->icon('heroicon-o-table-cells')
                ->color('success')
                ->visible(fn() => $this->calculado)
                ->url(fn() => route('informes.excel'))
                ->openUrlInNewTab(false),

            Action::make('pdf')
                ->label('Exportar PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('danger')
                ->visible(fn() => $this->calculado)
                ->url(fn() => route('informes.pdf'))
                ->openUrlInNewTab(false),
        ];
    }
}

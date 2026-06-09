<?php

namespace App\Exports\Reports;

use App\Models\Company;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * Genera PDFs profesionales para todos los informes contables.
 * Usa DomPDF con vistas Blade que incluyen encabezado empresa, KPIs y tablas.
 */
class PdfExporter
{
    public function __construct(private array $data) {}

    public function download(?string $filename = null): \Symfony\Component\HttpFoundation\Response
    {
        $view = match($this->data['tipo'] ?? '') {
            'estado_resultados'    => 'reports.estado-resultados',
            'balance_general'      => 'reports.balance-general',
            'flujo_efectivo'       => 'reports.flujo-efectivo',
            'balance_prueba'       => 'reports.balance-prueba',
            'analisis_cartera'     => 'reports.analisis-cartera',
            'informe_retenciones'  => 'reports.retenciones',
            'informe_comisiones'   => 'reports.comisiones',
            'conciliacion_iva'     => 'reports.conciliacion-iva',
            default                => 'reports.generico',
        };

        $company = Company::first();
        $name    = $filename ?? ($this->data['titulo'] ?? 'Informe') . '_' . now()->format('Ymd') . '.pdf';

        $pdf = Pdf::loadView($view, [
            'data'    => $this->data,
            'company' => $company,
        ])->setPaper('letter', 'portrait');

        return $pdf->download($name);
    }

    public function stream(?string $filename = null): \Symfony\Component\HttpFoundation\Response
    {
        $view = match($this->data['tipo'] ?? '') {
            'estado_resultados'    => 'reports.estado-resultados',
            'balance_general'      => 'reports.balance-general',
            'flujo_efectivo'       => 'reports.flujo-efectivo',
            'balance_prueba'       => 'reports.balance-prueba',
            'analisis_cartera'     => 'reports.analisis-cartera',
            'informe_retenciones'  => 'reports.retenciones',
            'informe_comisiones'   => 'reports.comisiones',
            'conciliacion_iva'     => 'reports.conciliacion-iva',
            default                => 'reports.generico',
        };

        $company = Company::first();

        $pdf = Pdf::loadView($view, [
            'data'    => $this->data,
            'company' => $company,
        ])->setPaper('letter', 'portrait');

        return $pdf->stream(($filename ?? $this->data['titulo'] ?? 'Informe') . '.pdf');
    }
}

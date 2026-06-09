<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\OwnerLiquidation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class OwnerLiquidationPdfController extends Controller
{
    // PDF individual de una liquidación
    public function individual(OwnerLiquidation $liquidation): Response
    {
        $liquidation->load(['propietario', 'property.tipo', 'rentalContract.arrendatario', 'statusHistories.usuario']);
        $company    = Company::with('municipio')->first();
        $logoBase64 = $this->logoBase64($company);

        $pdf = Pdf::loadView('pdf.liquidacion-propietario', compact('liquidation', 'company', 'logoBase64'))
            ->setPaper('letter', 'portrait');

        return response($pdf->output(), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="LIQ-' . $liquidation->numero . '.pdf"',
        ]);
    }

    // PDF reporte mensual de todas las liquidaciones de un mes
    public function reporte(int $mes, int $anio): Response
    {
        $liquidaciones = OwnerLiquidation::where('mes', $mes)->where('anio', $anio)
            ->with(['propietario', 'property', 'rentalContract'])
            ->orderBy('propietario_id')
            ->get();

        $meses = [1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',
                  7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre'];

        $company       = Company::first();
        $nombreMes     = ($meses[$mes] ?? $mes) . ' ' . $anio;
        $totalCanon    = $liquidaciones->sum('canon_cobrado');
        $totalComision = $liquidaciones->sum('comision_valor');
        $totalIva      = $liquidaciones->sum('iva_comision');
        $totalReteFuente = $liquidaciones->sum('retefuente_valor');
        $totalGiro     = $liquidaciones->sum('total_giro');

        $pdf = Pdf::loadView('reportes.liquidaciones-pdf',
            compact('liquidaciones','company','nombreMes','totalCanon','totalComision','totalIva','totalReteFuente','totalGiro')
        )->setPaper('legal', 'landscape');

        return response($pdf->output(), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="Liquidaciones-' . $mes . '-' . $anio . '.pdf"',
        ]);
    }

    private function logoBase64(?Company $company): ?string
    {
        if (!$company?->logo_path) return null;
        $path = storage_path('app/public/' . $company->logo_path);
        if (!file_exists($path)) return null;
        return 'data:' . mime_content_type($path) . ';base64,' . base64_encode(file_get_contents($path));
    }
}

<?php
namespace App\Http\Controllers;
use App\Models\PropertyHandover;
use App\Models\Company;
use Barryvdh\DomPDF\Facade\Pdf;

class PropertyHandoverPdfController extends Controller
{
    public function download(PropertyHandover $handover)
    {
        $handover->load(['rentalContract','property.tipo','property.municipio','arrendatario','asesor','items']);
        $company    = Company::with(['municipio'])->first();
        $logoBase64 = null;
        if ($company?->logo_path) {
            $path = storage_path('app/public/' . $company->logo_path);
            if (file_exists($path)) {
                $logoBase64 = 'data:' . mime_content_type($path) . ';base64,' . base64_encode(file_get_contents($path));
            }
        }
        $pdf = Pdf::loadView('pdf.acta-entrega', compact('handover','company','logoBase64'))
            ->setPaper('letter','portrait')
            ->setOptions(['defaultFont'=>'DejaVu Sans','isHtml5ParserEnabled'=>true,'dpi'=>150]);
        return $pdf->download('Acta-' . $handover->numero . '.pdf');
    }
}

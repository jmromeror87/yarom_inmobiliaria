<?php
namespace App\Http\Controllers;

use App\Models\RentBill;
use App\Models\Company;
use Barryvdh\DomPDF\Facade\Pdf;

class RentBillPdfController extends Controller
{
    public function download(RentBill $bill)
    {
        $bill->load(['rentalContract','arrendatario','property.tipo','property.municipio','payments.bank']);
        $company    = Company::with(['municipio.departamento'])->first();
        $logoBase64 = null;
        if ($company?->logo_path) {
            $path = storage_path('app/public/' . $company->logo_path);
            if (file_exists($path)) {
                $logoBase64 = 'data:' . mime_content_type($path) . ';base64,' . base64_encode(file_get_contents($path));
            }
        }
        $pdf = Pdf::loadView('pdf.factura-arriendo', compact('bill','company','logoBase64'))
            ->setPaper('letter','portrait')
            ->setOptions(['defaultFont'=>'DejaVu Sans','isHtml5ParserEnabled'=>true,'dpi'=>150]);
        return $pdf->download('Factura-' . $bill->numero . '.pdf');
    }
}

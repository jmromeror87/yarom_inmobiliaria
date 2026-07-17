<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\RentPayment;
use Barryvdh\DomPDF\Facade\Pdf;

class RentPaymentPdfController extends Controller
{
    public function download(RentPayment $payment)
    {
        $payment->load(['bill.property', 'bill.rentalContract', 'arrendatario', 'bank', 'registradoPor']);

        $company    = Company::with('municipio')->first();
        $logoBase64 = null;
        if ($company?->logo_path) {
            $path = storage_path('app/public/' . $company->logo_path);
            if (file_exists($path)) {
                $logoBase64 = 'data:' . mime_content_type($path) . ';base64,' . base64_encode(file_get_contents($path));
            }
        }

        $pdf = Pdf::loadView('pdf.recibo-pago', compact('payment', 'company', 'logoBase64'))
            ->setPaper('a5', 'landscape') // 21cm x 14.85cm
            ->setOptions(['defaultFont' => 'DejaVu Sans', 'isHtml5ParserEnabled' => true, 'dpi' => 150]);

        return $pdf->download('Recibo-' . $payment->numero . '.pdf');
    }
}

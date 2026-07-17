<?php

namespace App\Http\Controllers;

use App\Models\AccountingEntry;
use App\Models\Company;
use App\Models\RentPayment;
use Barryvdh\DomPDF\Facade\Pdf;

class RentPaymentPdfController extends Controller
{
    public function download(RentPayment $payment)
    {
        $payment->load(['bill.property.municipio', 'bill.rentalContract', 'arrendatario', 'bank', 'registradoPor']);

        // Líneas contables reales del pago (excluyendo el débito al banco/caja) para
        // mostrar el mismo formato "código + nombre de cuenta" que usaba el sistema legacy.
        $lineasContables = AccountingEntry::where('referencia_tipo', 'pago_individual')
            ->where('referencia_id', $payment->id)
            ->with('lines.account')
            ->first()
            ?->lines
            ->where('credito', '>', 0)
            ->sortBy('orden')
            ->values() ?? collect();

        $company    = Company::with('municipio')->first();
        $logoBase64 = null;
        if ($company?->logo_path) {
            $path = storage_path('app/public/' . $company->logo_path);
            if (file_exists($path)) {
                $logoBase64 = 'data:' . mime_content_type($path) . ';base64,' . base64_encode(file_get_contents($path));
            }
        }

        $pdf = Pdf::loadView('pdf.recibo-pago', compact('payment', 'company', 'logoBase64', 'lineasContables'))
            ->setPaper([0, 0, 396, 612], 'portrait') // media carta exacta: 5.5" x 8.5"
            ->setOptions(['defaultFont' => 'DejaVu Sans', 'isHtml5ParserEnabled' => true, 'dpi' => 150]);

        return $pdf->download('Recibo-' . $payment->numero . '.pdf');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\AccountingEntry;
use App\Models\Company;
use Barryvdh\DomPDF\Facade\Pdf;

class AccountingEntryPdfController extends Controller
{
    public function download(AccountingEntry $entry)
    {
        $entry->load(['lines.account', 'third', 'period', 'creadoPor']);

        $company = Company::with('municipio')->first();
        $logoBase64 = null;
        if ($company?->logo_path) {
            $path = storage_path('app/public/' . $company->logo_path);
            if (file_exists($path)) {
                $logoBase64 = 'data:' . mime_content_type($path) . ';base64,' . base64_encode(file_get_contents($path));
            }
        }

        $pdf = Pdf::loadView('pdf.comprobante-recibo', compact('entry', 'company', 'logoBase64'))
            ->setPaper([0, 0, 396, 612], 'portrait') // media carta exacta: 5.5" x 8.5"
            ->setOptions(['defaultFont' => 'DejaVu Sans', 'isHtml5ParserEnabled' => true, 'dpi' => 150]);

        return $pdf->download($entry->tipo . '-' . $entry->numero . '.pdf');
    }
}

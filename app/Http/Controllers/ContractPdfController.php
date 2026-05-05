<?php
/*
|--------------------------------------------------------------------------
| YarOM ERP - Soluciones de Gestión
|--------------------------------------------------------------------------
| Proyecto privado desarrollado por:
| Ingeniero Jhoan Romero Rivera
| LinkedIn: https://linkedin.com/in/jmromeror87
|
| Módulo: \1
| Archivo: ContractPdfController.php
| Fecha: CURRENT_DAY/05/2026
| Versión: v1.0
|--------------------------------------------------------------------------
*/
        

namespace App\Http\Controllers;

use App\Models\AdministrationContract;
use App\Models\Company;
use Barryvdh\DomPDF\Facade\Pdf;

class ContractPdfController extends Controller
{
    public function download(AdministrationContract $contract)
    {
        $contract->load([
            'property.tipo',
            'property.municipio.departamento',
            'propietario',
            'asesor',
            'clauses' => fn ($q) => $q->orderBy('orden'),
            'template',
        ]);

        $company = Company::with(['municipio', 'departamento'])->first();

        // Convertir logo a base64 para DomPDF
        $logoBase64 = null;
        if ($company?->logo_path) {
            $logoPath = storage_path('app/public/' . $company->logo_path);
            if (file_exists($logoPath)) {
                $mime = mime_content_type($logoPath);
                $logoBase64 = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($logoPath));
            }
        }

        $pdf = Pdf::loadView('pdf.contrato-administracion', [
            'contrato'    => $contract,
            'empresa'     => $company,
            'logoBase64'  => $logoBase64,
        ])
        ->setPaper('letter', 'portrait')
        ->setOptions([
            'defaultFont'          => 'DejaVu Sans',
            'isHtml5ParserEnabled' => true,
            'isPhpEnabled'         => false,
            'dpi'                  => 150,
            'isRemoteEnabled'      => false,
        ]);

        return $pdf->download('CAD-' . $contract->numero_contrato . '.pdf');
    }
}

<?php

namespace App\Http\Controllers;

use App\Exports\Reports\ExcelExporter;
use App\Exports\Reports\PdfExporter;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class InformeContableController extends Controller
{
    private const SESSION_KEY = 'informe_contable_data';

    public function excel()
    {
        $data = session(self::SESSION_KEY, []);

        if (empty($data)) {
            return redirect()->back()->with('error', 'No hay informe calculado. Calcule primero.');
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

    public function pdf()
    {
        $data = session(self::SESSION_KEY, []);

        if (empty($data)) {
            return redirect()->back()->with('error', 'No hay informe calculado. Calcule primero.');
        }

        return (new PdfExporter($data))->download();
    }
}

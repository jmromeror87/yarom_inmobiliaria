<?php

namespace App\Exports\AdministrationContracts;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class AdministrationContractImportReportExporter
{
    private const COLORES = [
        'creado' => ['fill' => 'DCFCE7', 'font' => '166534'],
        'valido' => ['fill' => 'DBEAFE', 'font' => '1E3A8A'],
        'error'  => ['fill' => 'FEE2E2', 'font' => '991B1B'],
    ];

    private const ETIQUETAS = [
        'creado' => 'Creado',
        'valido' => 'Válido (no importado aún)',
        'error'  => 'Error',
    ];

    public function stream(array $filas, string $filename = 'reporte_importacion_contratos.xlsx'): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $spreadsheet = $this->build($filas);
        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
        }, $filename, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    public function build(array $filas): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Reporte');

        $headers = ['Fila Excel', 'Documento propietario', 'Inmueble', 'Estado', 'Motivo'];
        foreach ($headers as $i => $label) {
            $letter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);
            $sheet->setCellValue("{$letter}1", $label);
        }
        $sheet->getStyle('A1:E1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A8A']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getColumnDimension('A')->setWidth(10);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(40);
        $sheet->getColumnDimension('D')->setWidth(24);
        $sheet->getColumnDimension('E')->setWidth(60);

        $row = 2;
        foreach ($filas as $f) {
            $sheet->setCellValue("A{$row}", $f['fila']);
            $sheet->setCellValue("B{$row}", $f['propietario_documento']);
            $sheet->setCellValue("C{$row}", $f['inmueble_direccion']);
            $sheet->setCellValue("D{$row}", self::ETIQUETAS[$f['estado']] ?? $f['estado']);
            $sheet->setCellValue("E{$row}", $f['motivo']);

            $colores = self::COLORES[$f['estado']] ?? null;
            if ($colores) {
                $sheet->getStyle("A{$row}:E{$row}")->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $colores['fill']]],
                    'font' => ['color' => ['rgb' => $colores['font']]],
                ]);
            }
            $row++;
        }

        $sheet->getStyle("A1:E" . ($row - 1))->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
        ]);
        $sheet->freezePane('A2');

        return $spreadsheet;
    }
}

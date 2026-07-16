<?php

namespace App\Exports\RentalContracts;

use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/**
 * Plantilla Excel para carga masiva de Contratos de Arriendo.
 * El arrendatario se referencia por documento, el inmueble por dirección
 * exacta, y opcionalmente se puede vincular un codeudor (se crea como
 * Tercero con rol Fiador si no existe todavía).
 */
class RentalContractTemplateExporter
{
    private const AZUL = '1E3A8A';
    private const ROJO = 'E11D48';
    private const BLANCO = 'FFFFFF';

    public const COLUMNS = [
        'arrendatario_documento' => 'N° Documento del arrendatario *',
        'inmueble_direccion'     => 'Dirección exacta del inmueble *',
        'fecha_inicio'           => 'Fecha de inicio (AAAA-MM-DD) *',
        'fecha_fin'              => 'Fecha de fin (AAAA-MM-DD) *',
        'canon_mensual'          => 'Canon mensual *',
        'deposito'               => 'Depósito',
        'duracion_meses'         => 'Duración (meses)',
        'tipo_garantia'          => 'Tipo de garantía',
        'codeudor_documento'     => 'Codeudor — N° Documento',
        'codeudor_nombre'        => 'Codeudor — Nombre',
        'codeudor_apellido'      => 'Codeudor — Apellido',
        'codeudor_celular'       => 'Codeudor — Celular',
        'codeudor_direccion'     => 'Codeudor — Dirección',
        'estado'                 => 'Estado',
        'notas'                  => 'Notas',
    ];

    public function build(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $this->buildDataSheet($spreadsheet);
        $this->buildInstructionsSheet($spreadsheet);
        $spreadsheet->setActiveSheetIndex(0);
        return $spreadsheet;
    }

    public function stream(string $filename = 'plantilla_contratos_arriendo.xlsx'): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $spreadsheet = $this->build();
        return response()->streamDownload(function () use ($spreadsheet) {
            (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet))->save('php://output');
        }, $filename, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    private function buildDataSheet(Spreadsheet $spreadsheet): void
    {
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Contratos');

        $col = 1; $headerMap = [];
        foreach (self::COLUMNS as $key => $label) {
            $letter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
            $sheet->setCellValue("{$letter}1", $label);
            $headerMap[$key] = $letter;
            $col++;
        }
        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col - 1);

        $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => self::BLANCO], 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::AZUL]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(38);
        foreach (array_keys(self::COLUMNS) as $key) {
            $sheet->getColumnDimension($headerMap[$key])->setWidth(24);
        }

        $ejemplo = [
            'arrendatario_documento' => '1234567890',
            'inmueble_direccion'     => 'Calle 10 # 5-20 Edificio Central Apto 301',
            'fecha_inicio'           => '2026-01-15',
            'fecha_fin'              => '2027-01-15',
            'canon_mensual'          => '600000',
            'deposito'               => '0',
            'duracion_meses'         => '12',
            'tipo_garantia'          => 'codeudor',
            'codeudor_documento'     => '987654321',
            'codeudor_nombre'        => 'Juan',
            'codeudor_apellido'      => 'Pérez',
            'codeudor_celular'       => '3001234567',
            'codeudor_direccion'     => 'Calle 5 # 3-10',
            'estado'                 => 'activo',
            'notas'                  => '',
        ];
        foreach ($ejemplo as $key => $val) {
            $sheet->setCellValue("{$headerMap[$key]}2", $val);
        }
        $sheet->getStyle("A2:{$lastCol}2")->applyFromArray(['font' => ['italic' => true, 'color' => ['rgb' => '94A3B8']]]);

        $this->addListValidation($sheet, $headerMap['tipo_garantia'], 'codeudor,garantia_bancaria,seguro_arrendamiento,ninguna');
        $this->addListValidation($sheet, $headerMap['estado'], 'borrador,enviado_arrendatario,aprobado,firmado,activo,terminado,cancelado');

        $sheet->freezePane('A2');
    }

    private function addListValidation($sheet, string $columnLetter, string $csvList): void
    {
        for ($row = 2; $row <= 700; $row++) {
            $cell = $sheet->getCell("{$columnLetter}{$row}");
            $validation = $cell->getDataValidation();
            $validation->setType(DataValidation::TYPE_LIST);
            $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
            $validation->setAllowBlank(true);
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setShowDropDown(true);
            $validation->setFormula1('"' . $csvList . '"');
        }
    }

    private function buildInstructionsSheet(Spreadsheet $spreadsheet): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Instrucciones');
        $sheet->getColumnDimension('A')->setWidth(90);

        $lines = [
            ['Instrucciones para cargar Contratos de Arriendo — YarOM ERP', true, 14],
            ['', false, 10],
            ['1. El arrendatario y el inmueble deben existir PREVIAMENTE (cargar primero Terceros e Inmuebles).', false, 10],
            ['2. "Dirección exacta del inmueble" debe coincidir letra por letra con la dirección ya cargada.', false, 10],
            ['3. El número de contrato (VIV-2026-XXXX / COM-2026-XXXX) se genera automáticamente.', false, 10],
            ['4. Si diligencia los datos del codeudor, se crea automáticamente como Tercero (rol Fiador) si no existe, y se vincula al contrato.', false, 10],
            ['5. Si el inmueble tiene un contrato de administración vigente, se vincula automáticamente.', false, 10],
        ];

        $row = 1;
        foreach ($lines as [$text, $bold, $size]) {
            $sheet->setCellValue("A{$row}", $text);
            $sheet->getStyle("A{$row}")->applyFromArray([
                'font' => ['bold' => $bold, 'size' => $size, 'color' => ['rgb' => $bold ? self::ROJO : '334155']],
                'alignment' => ['wrapText' => true, 'vertical' => Alignment::VERTICAL_TOP],
            ]);
            $sheet->getRowDimension($row)->setRowHeight($bold ? 24 : 30);
            $row++;
        }
    }
}

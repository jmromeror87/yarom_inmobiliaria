<?php

namespace App\Exports\Properties;

use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/**
 * Plantilla Excel para carga masiva de Inmuebles.
 * El propietario se referencia por número de documento (debe existir ya como Tercero).
 */
class PropertyTemplateExporter
{
    private const AZUL = '1E3A8A';
    private const ROJO = 'E11D48';
    private const BLANCO = 'FFFFFF';

    public const COLUMNS = [
        'propietario_documento'   => 'N° Documento del propietario *',
        'tipo_inmueble'           => 'Tipo de inmueble *',
        'direccion'               => 'Dirección *',
        'barrio'                  => 'Barrio',
        'municipio'               => 'Municipio',
        'departamento'            => 'Departamento',
        'estrato'                 => 'Estrato',
        'area_construida_m2'      => 'Área construida (m2)',
        'area_privada_m2'         => 'Área privada (m2)',
        'habitaciones'            => 'Habitaciones',
        'banos'                   => 'Baños',
        'garajes'                 => 'Garajes',
        'canon_arriendo'          => 'Canon de arriendo',
        'cuota_administracion'    => 'Cuota de administración',
        'disponible_arriendo'     => 'Disponible para arriendo (SI/NO)',
        'disponible_venta'        => 'Disponible para venta (SI/NO)',
        'estado'                  => 'Estado',
        'notas_internas'          => 'Notas internas (referencias del sistema anterior, etc.)',
    ];

    public function build(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $this->buildDataSheet($spreadsheet);
        $this->buildInstructionsSheet($spreadsheet);
        $spreadsheet->setActiveSheetIndex(0);
        return $spreadsheet;
    }

    public function stream(string $filename = 'plantilla_inmuebles.xlsx'): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $spreadsheet = $this->build();

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function buildDataSheet(Spreadsheet $spreadsheet): void
    {
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Inmuebles');

        $col = 1;
        $headerMap = [];
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
            $sheet->getColumnDimension($headerMap[$key])->setWidth(22);
        }

        $ejemplo = [
            'propietario_documento' => '1234567890', 'tipo_inmueble' => 'Apartamento',
            'direccion' => 'Calle 10 # 5-20 Edificio Central Apto 301', 'barrio' => 'Centro',
            'municipio' => 'Ocaña', 'departamento' => 'Norte de Santander', 'estrato' => '3',
            'area_construida_m2' => '65', 'area_privada_m2' => '60', 'habitaciones' => '3',
            'banos' => '2', 'garajes' => '1', 'canon_arriendo' => '600000', 'cuota_administracion' => '80000',
            'disponible_arriendo' => 'SI', 'disponible_venta' => 'NO', 'estado' => 'disponible',
            'notas_internas' => '',
        ];
        foreach ($ejemplo as $key => $val) {
            $sheet->setCellValue("{$headerMap[$key]}2", $val);
        }
        $sheet->getStyle("A2:{$lastCol}2")->applyFromArray(['font' => ['italic' => true, 'color' => ['rgb' => '94A3B8']]]);

        $this->addListValidation($sheet, $headerMap['tipo_inmueble'], 'Apartamento,Casa,Casa Campestre,Local Comercial,Oficina,Bodega,Lote,Finca,Consultorio,Parqueadero');
        $this->addListValidation($sheet, $headerMap['disponible_arriendo'], 'SI,NO');
        $this->addListValidation($sheet, $headerMap['disponible_venta'], 'SI,NO');
        $this->addListValidation($sheet, $headerMap['estado'], 'en_captacion,documentos_pendientes,disponible,arrendado,en_venta,vendido,en_mantenimiento,inactivo');

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
            ['Instrucciones para cargar Inmuebles — YarOM ERP', true, 14],
            ['', false, 10],
            ['1. El propietario debe existir PREVIAMENTE como Tercero (cargar primero Terceros).', false, 10],
            ['2. "N° Documento del propietario" debe coincidir exacto con el número de documento del Tercero ya creado.', false, 10],
            ['3. "Tipo de inmueble" debe ser uno de los tipos ya configurados en el sistema (ver lista desplegable).', false, 10],
            ['4. Estrato, área, habitaciones, baños, garajes: si no los tiene a mano, déjelos en blanco y complételos después en la ficha del inmueble.', false, 10],
            ['5. El código interno del inmueble (INM-2026-XXXX) se genera automáticamente, no hace falta digitarlo.', false, 10],
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

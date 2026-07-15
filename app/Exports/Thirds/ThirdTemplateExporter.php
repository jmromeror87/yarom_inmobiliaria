<?php

namespace App\Exports\Thirds;

use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Genera la plantilla Excel para carga masiva de Terceros.
 * Las columnas están organizadas por rol: comunes, propietario/proveedor (bancario)
 * y arrendatario/fiador (laboral), igual que las secciones condicionales del formulario.
 */
class ThirdTemplateExporter
{
    private const AZUL   = '1E3A8A';
    private const ROJO   = 'E11D48';
    private const GRIS   = 'F1F5F9';
    private const BLANCO = 'FFFFFF';

    public const COLUMNS = [
        // ── Roles ──
        'es_propietario'      => 'Rol: Propietario (SI/NO)',
        'es_arrendatario'     => 'Rol: Arrendatario (SI/NO)',
        'es_fiador'           => 'Rol: Fiador (SI/NO)',
        'es_cliente_compra'   => 'Rol: Cliente compra (SI/NO)',
        'es_proveedor'        => 'Rol: Proveedor (SI/NO)',

        // ── Identificación (todos) ──
        'tipo_persona'        => 'Tipo de persona',
        'tipo_documento'      => 'Tipo de documento',
        'numero_documento'    => 'Número de documento *',
        'digito_verificacion' => 'Dígito verificación (solo NIT)',
        'primer_nombre'       => 'Primer nombre (natural)',
        'segundo_nombre'      => 'Segundo nombre (natural)',
        'primer_apellido'     => 'Primer apellido (natural)',
        'segundo_apellido'    => 'Segundo apellido (natural)',
        'razon_social'        => 'Razón social (jurídica)',
        'nombre_comercial'    => 'Nombre comercial (jurídica)',

        // ── Contacto y ubicación (todos) ──
        'email'               => 'Correo electrónico',
        'celular'             => 'Celular',
        'telefono_fijo'       => 'Teléfono fijo',
        'direccion_residencia'=> 'Dirección',
        'barrio_residencia'   => 'Barrio',
        'municipio'           => 'Municipio',
        'departamento'        => 'Departamento',
        'nacionalidad'        => 'Nacionalidad',

        // ── Bloque PROPIETARIO / PROVEEDOR ──
        'banco'               => '[Propietario/Proveedor] Banco',
        'tipo_cuenta'         => '[Propietario/Proveedor] Tipo de cuenta',
        'numero_cuenta'       => '[Propietario/Proveedor] Número de cuenta',
        'titular_cuenta'      => '[Propietario/Proveedor] Titular de la cuenta',
        'comision_pactada'    => '[Propietario] Comisión pactada %',

        // ── Bloque ARRENDATARIO / FIADOR ──
        'tipo_empleo'           => '[Arrendatario/Fiador] Tipo de empleo',
        'empresa_donde_trabaja' => '[Arrendatario/Fiador] Empresa donde trabaja',
        'cargo'                 => '[Arrendatario/Fiador] Cargo',
        'ingresos_mensuales'    => '[Arrendatario/Fiador] Ingresos mensuales',
        'otros_ingresos'        => '[Arrendatario/Fiador] Otros ingresos',
    ];

    public function build(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();

        $this->buildDataSheet($spreadsheet);
        $this->buildInstructionsSheet($spreadsheet);

        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    public function stream(string $filename = 'plantilla_terceros.xlsx'): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $spreadsheet = $this->build();

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function buildDataSheet(Spreadsheet $spreadsheet): void
    {
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Terceros');

        $col = 1;
        $headerMap = [];
        foreach (self::COLUMNS as $key => $label) {
            $letter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
            $sheet->setCellValue("{$letter}1", $label);
            $headerMap[$key] = $letter;
            $col++;
        }

        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col - 1);

        // Estilo encabezado
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

        // Filas de ejemplo (fila 2) — se pueden borrar antes de subir
        $ejemplo = [
            'es_propietario' => 'SI', 'es_arrendatario' => 'NO', 'es_fiador' => 'NO',
            'es_cliente_compra' => 'NO', 'es_proveedor' => 'NO',
            'tipo_persona' => 'natural', 'tipo_documento' => 'CC', 'numero_documento' => '1234567890',
            'digito_verificacion' => '', 'primer_nombre' => 'Juan', 'segundo_nombre' => '',
            'primer_apellido' => 'Pérez', 'segundo_apellido' => 'Gómez',
            'razon_social' => '', 'nombre_comercial' => '',
            'email' => 'juan.perez@correo.com', 'celular' => '3001234567', 'telefono_fijo' => '',
            'direccion_residencia' => 'Calle 10 # 5-20', 'barrio_residencia' => 'Centro',
            'municipio' => 'Ocaña', 'departamento' => 'Norte de Santander', 'nacionalidad' => 'Colombiana',
            'banco' => 'Bancolombia', 'tipo_cuenta' => 'ahorros', 'numero_cuenta' => '00012345678',
            'titular_cuenta' => 'Juan Pérez Gómez', 'comision_pactada' => '10',
            'tipo_empleo' => '', 'empresa_donde_trabaja' => '', 'cargo' => '',
            'ingresos_mensuales' => '', 'otros_ingresos' => '',
        ];
        foreach ($ejemplo as $key => $val) {
            $sheet->setCellValue("{$headerMap[$key]}2", $val);
        }
        $sheet->getStyle("A2:{$lastCol}2")->applyFromArray([
            'font' => ['italic' => true, 'color' => ['rgb' => '94A3B8']],
        ]);

        // Validaciones (listas desplegables) desde la fila 2 hasta la 500
        $this->addListValidation($sheet, $headerMap['es_propietario'], 'SI,NO');
        $this->addListValidation($sheet, $headerMap['es_arrendatario'], 'SI,NO');
        $this->addListValidation($sheet, $headerMap['es_fiador'], 'SI,NO');
        $this->addListValidation($sheet, $headerMap['es_cliente_compra'], 'SI,NO');
        $this->addListValidation($sheet, $headerMap['es_proveedor'], 'SI,NO');
        $this->addListValidation($sheet, $headerMap['tipo_persona'], 'natural,juridica');
        $this->addListValidation($sheet, $headerMap['tipo_documento'], 'CC,CE,NIT,Pasaporte,TI,PEP,PPT');
        $this->addListValidation($sheet, $headerMap['tipo_cuenta'], 'ahorros,corriente');
        $this->addListValidation($sheet, $headerMap['tipo_empleo'], 'dependiente,independiente,pensionado,rentista,desempleado,otro');

        $sheet->freezePane('A2');
    }

    private function addListValidation($sheet, string $columnLetter, string $csvList): void
    {
        for ($row = 2; $row <= 500; $row++) {
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
            ['Instrucciones para cargar Terceros — YarOM ERP', true, 14],
            ['', false, 10],
            ['1. No cambie los nombres de las columnas de la hoja "Terceros".', false, 10],
            ['2. Borre la fila de ejemplo (fila 2) antes de subir el archivo, o déjela y será ignorada si el documento "1234567890" ya existe.', false, 10],
            ['3. El campo "Número de documento" es obligatorio y debe ser único. Si ya existe un tercero con ese documento, esa fila se OMITE al importar (no se sobrescribe).', false, 10],
            ['4. Marque los roles con SI o NO. Un tercero puede tener varios roles a la vez (ej: propietario y proveedor).', false, 10],
            ['5. Para persona "natural" llene nombres y apellidos. Para persona "juridica" llene razón social.', false, 10],
            ['6. Los campos marcados [Propietario/Proveedor] son datos bancarios — solo diligéncielos si el tercero tiene ese rol.', false, 10],
            ['7. Los campos marcados [Arrendatario/Fiador] son datos laborales/ingresos — solo diligéncielos si el tercero tiene ese rol.', false, 10],
            ['8. Municipio y Departamento deben escribirse tal cual existen en Colombia (ej: "Ocaña", "Norte de Santander"). Si no coincide exactamente, se dejará en blanco y podrá completarlo luego manualmente.', false, 10],
            ['9. Los campos de evaluación crediticia, CRM, garantías, KYC, etc. no están en esta plantilla — se diligencian después dentro del sistema, tercero por tercero.', false, 10],
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

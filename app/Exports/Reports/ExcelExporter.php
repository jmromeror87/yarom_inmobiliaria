<?php

namespace App\Exports\Reports;

use App\Models\Company;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Genera archivos Excel profesionales para todos los informes contables.
 * Usa PhpSpreadsheet con estilos, colores corporativos, KPIs destacados y tablas formateadas.
 */
class ExcelExporter
{
    private Spreadsheet $spreadsheet;
    private array $data;

    // Colores corporativos
    private const AZUL_OSCURO  = '0F172A';
    private const AZUL_MEDIO   = '1E3A8A';
    private const AZUL_CLARO   = 'DBEAFE';
    private const VERDE        = '16A34A';
    private const VERDE_CLARO  = 'DCFCE7';
    private const ROJO         = 'DC2626';
    private const ROJO_CLARO   = 'FEE2E2';
    private const GRIS         = '64748B';
    private const GRIS_CLARO   = 'F8FAFC';
    private const BLANCO       = 'FFFFFF';
    private const AMARILLO     = 'FEF3C7';
    private const NARANJA      = 'FED7AA';

    public function __construct(array $data)
    {
        $this->data        = $data;
        $this->spreadsheet = new Spreadsheet();
        $this->spreadsheet->getProperties()
            ->setCreator('YarOM ERP — Serviarrendar S.A.S')
            ->setTitle($data['titulo'] ?? 'Informe Contable')
            ->setCompany($data['empresa'] ?? '');
    }

    public function generate(): Spreadsheet
    {
        return match($this->data['tipo'] ?? '') {
            'estado_resultados' => $this->buildEstadoResultados(),
            'balance_general'   => $this->buildBalanceGeneral(),
            'flujo_efectivo'    => $this->buildFlujoEfectivo(),
            'balance_prueba'    => $this->buildBalancePrueba(),
            'libro_mayor'       => $this->buildLibroMayor(),
            'analisis_cartera'  => $this->buildAnalisisCartera(),
            'informe_retenciones'  => $this->buildRetenciones(),
            'informe_comisiones'   => $this->buildComisiones(),
            'conciliacion_iva'     => $this->buildConciliacionIVA(),
            default => $this->spreadsheet,
        };
    }

    public function download(string $filename = null): void
    {
        $this->generate();
        $name = $filename ?? ($this->data['titulo'] ?? 'Informe') . '_' . now()->format('Ymd') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $name . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($this->spreadsheet);
        $writer->save('php://output');
        exit;
    }

    // ══════════════════════════════════════════════════════════════════════
    // HELPERS DE ESTILO
    // ══════════════════════════════════════════════════════════════════════

    private function sheet(int $index = 0): \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet
    {
        return $this->spreadsheet->getSheet($index);
    }

    private function header(
        \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $ws,
        string $titulo,
        string $subtitulo = '',
        int &$row = 1
    ): void {
        $company = Company::first();

        // Fila empresa
        $ws->mergeCells("A{$row}:H{$row}");
        $ws->setCellValue("A{$row}", strtoupper($company?->razon_social ?? 'SERVIARRENDAR S.A.S'));
        $this->style($ws, "A{$row}:H{$row}", [
            'font' => ['bold' => true, 'size' => 14, 'color' => ['argb' => 'FF' . self::BLANCO]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF' . self::AZUL_OSCURO]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $ws->getRowDimension($row)->setRowHeight(30);
        $row++;

        // NIT
        $ws->mergeCells("A{$row}:H{$row}");
        $ws->setCellValue("A{$row}", 'NIT: ' . ($company?->nit_completo ?? '') . '   —   ' . ($company?->email ?? ''));
        $this->style($ws, "A{$row}:H{$row}", [
            'font' => ['size' => 10, 'color' => ['argb' => 'FFADB5BD']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF' . self::AZUL_OSCURO]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $row++;

        // Título informe
        $ws->mergeCells("A{$row}:H{$row}");
        $ws->setCellValue("A{$row}", strtoupper($titulo));
        $this->style($ws, "A{$row}:H{$row}", [
            'font' => ['bold' => true, 'size' => 16, 'color' => ['argb' => 'FF' . self::AZUL_OSCURO]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF' . self::AZUL_CLARO]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $ws->getRowDimension($row)->setRowHeight(36);
        $row++;

        if ($subtitulo) {
            $ws->mergeCells("A{$row}:H{$row}");
            $ws->setCellValue("A{$row}", $subtitulo);
            $this->style($ws, "A{$row}:H{$row}", [
                'font' => ['size' => 11, 'italic' => true, 'color' => ['argb' => 'FF' . self::GRIS]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF0F9FF']],
            ]);
            $row++;
        }

        $row++; // Espacio
    }

    private function kpis(
        \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $ws,
        array $kpis,
        int &$row
    ): void {
        $colors = [
            'green'   => ['bg' => 'DCFCE7', 'fg' => '14532D'],
            'red'     => ['bg' => 'FEE2E2', 'fg' => '7F1D1D'],
            'blue'    => ['bg' => 'DBEAFE', 'fg' => '1E3A8A'],
            'orange'  => ['bg' => 'FED7AA', 'fg' => '7C2D12'],
            'purple'  => ['bg' => 'EDE9FE', 'fg' => '4C1D95'],
            'gray'    => ['bg' => 'F1F5F9', 'fg' => '0F172A'],
            'emerald' => ['bg' => 'D1FAE5', 'fg' => '064E3B'],
            'indigo'  => ['bg' => 'E0E7FF', 'fg' => '312E81'],
        ];

        $startRow = $row;
        $col = 'A';
        $kpisPerRow = 4;
        $kpiWidth   = 2; // columnas por KPI

        foreach (array_chunk($kpis, $kpisPerRow) as $chunk) {
            $col = 'A';
            foreach ($chunk as $kpi) {
                $color  = $colors[$kpi['color'] ?? 'gray'] ?? $colors['gray'];
                $endCol = chr(ord($col) + $kpiWidth - 1);
                $range  = "{$col}{$row}:{$endCol}" . ($row + 2);

                $ws->mergeCells("{$col}{$row}:{$endCol}{$row}");
                $ws->mergeCells("{$col}" . ($row+1) . ":{$endCol}" . ($row+1));
                $ws->mergeCells("{$col}" . ($row+2) . ":{$endCol}" . ($row+2));

                $ws->setCellValue("{$col}{$row}", $kpi['icon'] ?? '📊');
                $ws->setCellValue("{$col}" . ($row+1), $kpi['label'] ?? '');
                $valor = $kpi['es_pct'] ?? false
                    ? $kpi['valor']
                    : (is_numeric($kpi['valor']) ? '$' . number_format((float)$kpi['valor'], 0, ',', '.') : $kpi['valor']);
                $ws->setCellValue("{$col}" . ($row+2), $valor);

                $this->style($ws, "{$col}{$row}:{$endCol}{$row}", [
                    'font' => ['size' => 18], 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF' . $color['bg']]],
                ]);
                $this->style($ws, "{$col}" . ($row+1) . ":{$endCol}" . ($row+1), [
                    'font' => ['size' => 9, 'bold' => true, 'color' => ['argb' => 'FF' . $color['fg']]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF' . $color['bg']]],
                ]);
                $this->style($ws, "{$col}" . ($row+2) . ":{$endCol}" . ($row+2), [
                    'font' => ['size' => 14, 'bold' => true, 'color' => ['argb' => 'FF' . $color['fg']]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF' . $color['bg']]],
                ]);

                $col = chr(ord($endCol) + 1);
            }
            $row += 4;
        }
        $row++; // Espacio
    }

    private function tableHeader(
        \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $ws,
        array $cols,
        int $row,
        string $bgColor = null
    ): void {
        $bg = $bgColor ?? self::AZUL_MEDIO;
        $col = 'A';
        foreach ($cols as $label) {
            $ws->setCellValue("{$col}{$row}", $label);
            $col++;
        }
        $endCol = chr(ord('A') + count($cols) - 1);
        $this->style($ws, "A{$row}:{$endCol}{$row}", [
            'font'      => ['bold' => true, 'color' => ['argb' => 'FF' . self::BLANCO], 'size' => 10],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF' . $bg]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFCBD5E1']]],
        ]);
        $ws->getRowDimension($row)->setRowHeight(22);
    }

    private function tableRow(
        \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $ws,
        array $valores,
        int $row,
        bool $bold = false,
        string $bg = null
    ): void {
        $col = 'A';
        foreach ($valores as $val) {
            $ws->setCellValue("{$col}{$row}", $val);
            $col++;
        }
        $endCol = chr(ord('A') + count($valores) - 1);
        $rowBg  = $bg ?? ($row % 2 === 0 ? self::GRIS_CLARO : self::BLANCO);
        $this->style($ws, "A{$row}:{$endCol}{$row}", [
            'font'    => ['bold' => $bold, 'size' => 10, 'color' => ['argb' => 'FF' . ($bold ? self::AZUL_OSCURO : '1E293B')]],
            'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF' . $rowBg]],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFE2E8F0']]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);
    }

    private function totalRow(
        \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $ws,
        array $valores,
        int $row
    ): void {
        $this->tableRow($ws, $valores, $row, true, 'E2E8F0');
        $endCol = chr(ord('A') + count($valores) - 1);
        $this->style($ws, "A{$row}:{$endCol}{$row}", [
            'borders' => ['top' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => 'FF' . self::AZUL_MEDIO]]],
        ]);
    }

    private function sectionTitle(
        \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $ws,
        string $titulo,
        int $row,
        int $cols = 8,
        string $bg = null
    ): void {
        $endCol = chr(ord('A') + $cols - 1);
        $ws->mergeCells("A{$row}:{$endCol}{$row}");
        $ws->setCellValue("A{$row}", '  ' . $titulo);
        $this->style($ws, "A{$row}:{$endCol}{$row}", [
            'font' => ['bold' => true, 'size' => 11, 'color' => ['argb' => 'FF' . self::BLANCO]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF' . ($bg ?? self::AZUL_OSCURO)]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $ws->getRowDimension($row)->setRowHeight(24);
    }

    private function style(
        \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $ws,
        string $range,
        array $styles
    ): void {
        $ws->getStyle($range)->applyFromArray($styles);
    }

    private function money(float $val): string
    {
        return '$' . number_format($val, 0, ',', '.');
    }

    private function pct(float $val): string
    {
        return number_format($val, 2) . '%';
    }

    private function setColWidths(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $ws, array $widths): void
    {
        $col = 'A';
        foreach ($widths as $w) {
            $ws->getColumnDimension($col)->setWidth($w);
            $col++;
        }
    }

    // ══════════════════════════════════════════════════════════════════════
    // BUILDERS POR TIPO DE INFORME
    // ══════════════════════════════════════════════════════════════════════

    private function buildEstadoResultados(): Spreadsheet
    {
        $ws  = $this->sheet();
        $ws->setTitle('Estado de Resultados');
        $row = 1;

        $this->header($ws, $this->data['titulo'], 'Período: ' . ($this->data['periodo_label'] ?? ''), $row);
        $this->kpis($ws, $this->data['kpis'] ?? [], $row);

        // INGRESOS
        $this->sectionTitle($ws, '📈  INGRESOS OPERACIONALES', $row);
        $row++;
        $this->tableHeader($ws, ['Código', 'Cuenta', 'Débitos', 'Créditos', 'Saldo'], $row, '1E3A8A');
        $row++;

        foreach ($this->data['ingresos'] ?? [] as $r) {
            $this->tableRow($ws, [
                $r['codigo'], $r['nombre'],
                $this->money($r['debito']), $this->money($r['credito']), $this->money($r['saldo']),
            ], $row);
            $row++;
        }
        $this->totalRow($ws, ['', 'TOTAL INGRESOS', '', '', $this->money($this->data['total_ingresos'])], $row);
        $row += 2;

        // GASTOS
        $this->sectionTitle($ws, '📉  GASTOS OPERACIONALES', $row, 5, '7F1D1D');
        $row++;
        $this->tableHeader($ws, ['Código', 'Cuenta', 'Débitos', 'Créditos', 'Saldo'], $row, '991B1B');
        $row++;

        foreach ($this->data['gastos'] ?? [] as $r) {
            $this->tableRow($ws, [
                $r['codigo'], $r['nombre'],
                $this->money($r['debito']), $this->money($r['credito']), $this->money($r['saldo']),
            ], $row);
            $row++;
        }
        $this->totalRow($ws, ['', 'TOTAL GASTOS', '', '', $this->money($this->data['total_gastos'])], $row);
        $row += 2;

        // RESULTADO
        $utilidad   = $this->data['utilidad_operacional'] ?? 0;
        $colorRes   = $utilidad >= 0 ? self::VERDE_CLARO : self::ROJO_CLARO;
        $ws->mergeCells("A{$row}:D{$row}");
        $ws->setCellValue("A{$row}", 'UTILIDAD OPERACIONAL DEL PERÍODO');
        $ws->setCellValue("E{$row}", $this->money($utilidad));
        $this->style($ws, "A{$row}:E{$row}", [
            'font' => ['bold' => true, 'size' => 13, 'color' => ['argb' => 'FF' . ($utilidad >= 0 ? '14532D' : '7F1D1D')]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF' . $colorRes]],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $ws->getRowDimension($row)->setRowHeight(28);

        $this->setColWidths($ws, [12, 42, 16, 16, 18]);
        return $this->spreadsheet;
    }

    private function buildBalanceGeneral(): Spreadsheet
    {
        $ws  = $this->sheet();
        $ws->setTitle('Balance General');
        $row = 1;

        $this->header($ws, $this->data['titulo'], 'Corte al: ' . ($this->data['hasta_label'] ?? ''), $row);
        $this->kpis($ws, $this->data['kpis'] ?? [], $row);

        $cols    = ['Código', 'Cuenta', 'Saldo'];
        $numCols = count($cols);

        // ACTIVOS CORRIENTES
        $this->sectionTitle($ws, '💵  ACTIVOS CORRIENTES', $row, $numCols, '0C4A6E');
        $row++;
        $this->tableHeader($ws, $cols, $row, '0369A1');
        $row++;
        foreach ($this->data['activos_corrientes'] ?? [] as $r) {
            $this->tableRow($ws, [$r['codigo'], $r['nombre'], $this->money($r['saldo'])], $row++);
        }
        $this->totalRow($ws, ['', 'TOTAL ACTIVOS CORRIENTES', $this->money($this->data['total_activos_corrientes'])], $row++);
        $row++;

        // ACTIVOS NO CORRIENTES
        $this->sectionTitle($ws, '🏢  ACTIVOS NO CORRIENTES', $row, $numCols, '1E3A8A');
        $row++;
        $this->tableHeader($ws, $cols, $row, '1D4ED8');
        $row++;
        foreach ($this->data['activos_no_corrientes'] ?? [] as $r) {
            $this->tableRow($ws, [$r['codigo'], $r['nombre'], $this->money($r['saldo'])], $row++);
        }
        $totalNoCte = $this->data['total_activos'] - $this->data['total_activos_corrientes'];
        $this->totalRow($ws, ['', 'TOTAL ACTIVOS NO CORRIENTES', $this->money($totalNoCte)], $row++);

        // TOTAL ACTIVOS
        $ws->setCellValue("A{$row}", 'TOTAL ACTIVOS');
        $ws->setCellValue("C{$row}", $this->money($this->data['total_activos']));
        $this->style($ws, "A{$row}:C{$row}", [
            'font' => ['bold' => true, 'size' => 12, 'color' => ['argb' => 'FF' . self::BLANCO]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF' . self::AZUL_OSCURO]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $ws->getRowDimension($row)->setRowHeight(24);
        $row += 2;

        // PASIVOS
        $this->sectionTitle($ws, '📋  PASIVOS CORRIENTES', $row, $numCols, '7F1D1D');
        $row++;
        $this->tableHeader($ws, $cols, $row, '991B1B');
        $row++;
        foreach ($this->data['pasivos_corrientes'] ?? [] as $r) {
            $this->tableRow($ws, [$r['codigo'], $r['nombre'], $this->money($r['saldo'])], $row++);
        }
        $this->totalRow($ws, ['', 'TOTAL PASIVOS CORRIENTES', $this->money($this->data['total_pasivos_corrientes'])], $row++);
        $row++;

        foreach ($this->data['pasivos_largo_plazo'] ?? [] as $r) {
            $this->tableRow($ws, [$r['codigo'], $r['nombre'], $this->money($r['saldo'])], $row++);
        }
        $ws->setCellValue("A{$row}", 'TOTAL PASIVOS');
        $ws->setCellValue("C{$row}", $this->money($this->data['total_pasivos']));
        $this->style($ws, "A{$row}:C{$row}", [
            'font' => ['bold' => true, 'size' => 12, 'color' => ['argb' => 'FF' . self::BLANCO]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => '7F1D1D']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $ws->getRowDimension($row)->setRowHeight(24);
        $row += 2;

        // PATRIMONIO
        $this->sectionTitle($ws, '💎  PATRIMONIO', $row, $numCols, '14532D');
        $row++;
        $this->tableHeader($ws, $cols, $row, '15803D');
        $row++;
        foreach ($this->data['patrimonio'] ?? [] as $r) {
            $this->tableRow($ws, [$r['codigo'], $r['nombre'], $this->money($r['saldo'])], $row++);
        }
        $ws->setCellValue("A{$row}", 'TOTAL PATRIMONIO');
        $ws->setCellValue("C{$row}", $this->money($this->data['total_patrimonio']));
        $this->style($ws, "A{$row}:C{$row}", [
            'font' => ['bold' => true, 'size' => 12, 'color' => ['argb' => 'FF' . self::BLANCO]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF14532D']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $ws->getRowDimension($row)->setRowHeight(24);
        $row += 2;

        // Ecuación contable
        $cuadra = $this->data['ecuacion_cuadra'] ?? false;
        $ws->mergeCells("A{$row}:C{$row}");
        $ws->setCellValue("A{$row}", $cuadra ? '✅ Ecuación contable cuadra: Activos = Pasivos + Patrimonio' : '❌ DESCUADRE: $' . number_format($this->data['diferencia'] ?? 0, 0, ',', '.'));
        $this->style($ws, "A{$row}:C{$row}", [
            'font' => ['bold' => true, 'size' => 11, 'color' => ['argb' => 'FF' . ($cuadra ? '14532D' : '7F1D1D')]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF' . ($cuadra ? 'DCFCE7' : 'FEE2E2')]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $this->setColWidths($ws, [12, 45, 20]);
        return $this->spreadsheet;
    }

    private function buildFlujoEfectivo(): Spreadsheet
    {
        $ws  = $this->sheet();
        $ws->setTitle('Flujo de Caja');
        $row = 1;

        $this->header($ws, $this->data['titulo'], 'Período: ' . ($this->data['periodo_label'] ?? ''), $row);
        $this->kpis($ws, $this->data['kpis'] ?? [], $row);

        // Saldo inicial
        $ws->mergeCells("A{$row}:B{$row}");
        $ws->setCellValue("A{$row}", 'Saldo inicial en caja/bancos');
        $ws->setCellValue("C{$row}", $this->money($this->data['saldo_inicial'] ?? 0));
        $this->style($ws, "A{$row}:C{$row}", ['font' => ['bold' => true, 'size' => 11], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF1F5F9']]]);
        $row += 2;

        // Entradas
        $this->sectionTitle($ws, '⬆️  ENTRADAS DE EFECTIVO', $row, 3, '14532D');
        $row++;
        $this->tableHeader($ws, ['Concepto', '', 'Valor'], $row, '15803D');
        $row++;
        foreach ($this->data['detalle_entradas'] ?? [] as $r) {
            $this->tableRow($ws, [$r['concepto'], '', $this->money($r['valor'])], $row++);
        }
        $this->totalRow($ws, ['TOTAL ENTRADAS', '', $this->money($this->data['total_entradas'])], $row++);
        $row++;

        // Salidas
        $this->sectionTitle($ws, '⬇️  SALIDAS DE EFECTIVO', $row, 3, '7F1D1D');
        $row++;
        $this->tableHeader($ws, ['Concepto', '', 'Valor'], $row, '991B1B');
        $row++;
        foreach ($this->data['detalle_salidas'] ?? [] as $r) {
            $this->tableRow($ws, [$r['concepto'], '', $this->money($r['valor'])], $row++);
        }
        $this->totalRow($ws, ['TOTAL SALIDAS', '', $this->money($this->data['total_salidas'])], $row++);
        $row++;

        // Flujo neto y saldo final
        $flujoNeto  = $this->data['flujo_neto'] ?? 0;
        $saldoFinal = $this->data['saldo_final'] ?? 0;
        foreach ([
            ['Flujo neto del período', $flujoNeto, $flujoNeto >= 0 ? self::VERDE_CLARO : self::ROJO_CLARO],
            ['Saldo final en caja/bancos', $saldoFinal, $saldoFinal >= 0 ? 'DBEAFE' : self::ROJO_CLARO],
        ] as [$label, $valor, $bg]) {
            $ws->mergeCells("A{$row}:B{$row}");
            $ws->setCellValue("A{$row}", $label);
            $ws->setCellValue("C{$row}", $this->money($valor));
            $this->style($ws, "A{$row}:C{$row}", [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF' . $bg]],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ]);
            $ws->getRowDimension($row)->setRowHeight(26);
            $row++;
        }

        $this->setColWidths($ws, [45, 5, 20]);
        return $this->spreadsheet;
    }

    private function buildBalancePrueba(): Spreadsheet
    {
        $ws  = $this->sheet();
        $ws->setTitle('Balance de Prueba');
        $row = 1;

        $this->header($ws, $this->data['titulo'], 'Período: ' . ($this->data['periodo_label'] ?? ''), $row);
        $this->kpis($ws, $this->data['kpis'] ?? [], $row);

        $claseLabels = ['1'=>'ACTIVOS','2'=>'PASIVOS','3'=>'PATRIMONIO','4'=>'INGRESOS','5'=>'GASTOS','8'=>'CTA ORDEN DEB','9'=>'CTA ORDEN ACRE'];
        $cuentasPorClase = collect($this->data['cuentas'] ?? [])->groupBy('clase');

        foreach ($cuentasPorClase as $clase => $cuentas) {
            $this->sectionTitle($ws, ($claseLabels[$clase] ?? "CLASE {$clase}"), $row);
            $row++;
            $this->tableHeader($ws, ['Código', 'Cuenta', 'Mov. Débito', 'Mov. Crédito', 'Saldo Débito', 'Saldo Crédito'], $row);
            $row++;

            foreach ($cuentas as $r) {
                $this->tableRow($ws, [
                    $r['codigo'], $r['nombre'],
                    $this->money($r['debito']), $this->money($r['credito']),
                    $r['saldo_db'] > 0 ? $this->money($r['saldo_db']) : '',
                    $r['saldo_cr'] > 0 ? $this->money($r['saldo_cr']) : '',
                ], $row++);
            }
            $row++;
        }

        $cuadra = $this->data['cuadra'] ?? false;
        $this->totalRow($ws, [
            '', 'TOTALES',
            $this->money($this->data['total_debitos']),
            $this->money($this->data['total_creditos']),
            $cuadra ? '✅ CUADRA' : '❌ DESCUADRE $' . number_format($this->data['diferencia'] ?? 0, 0, ',', '.'),
            '',
        ], $row);

        $this->setColWidths($ws, [12, 40, 16, 16, 16, 16]);
        return $this->spreadsheet;
    }

    private function buildLibroMayor(): Spreadsheet
    {
        $ws  = $this->sheet();
        $ws->setTitle('Libro Mayor');
        $row = 1;

        $this->header($ws, $this->data['titulo'], 'Período: ' . ($this->data['periodo_label'] ?? ''), $row);
        $this->kpis($ws, $this->data['kpis'] ?? [], $row);

        $claseLabels = ['1'=>'ACTIVOS','2'=>'PASIVOS','3'=>'PATRIMONIO','4'=>'INGRESOS','5'=>'GASTOS','8'=>'CTA ORDEN DEB','9'=>'CTA ORDEN ACRE'];
        $cuentasPorClase = collect($this->data['cuentas'] ?? [])->groupBy('clase');

        foreach ($cuentasPorClase as $clase => $cuentas) {
            $this->sectionTitle($ws, ($claseLabels[$clase] ?? "CLASE {$clase}"), $row);
            $row++;
            $this->tableHeader($ws, ['Código', 'Cuenta', 'Saldo Inicial', 'Débito', 'Crédito', 'Saldo Final'], $row);
            $row++;

            foreach ($cuentas as $r) {
                $this->tableRow($ws, [
                    $r['codigo'], $r['nombre'],
                    $this->money($r['saldo_inicial']),
                    $this->money($r['debito']), $this->money($r['credito']),
                    $this->money($r['saldo_final']),
                ], $row++);
            }
            $row++;
        }

        $cuadra = $this->data['cuadra'] ?? false;
        $this->totalRow($ws, [
            '', 'TOTALES', '',
            $this->money($this->data['total_debitos']),
            $this->money($this->data['total_creditos']),
            $cuadra ? '✅ CUADRA' : '❌ DESCUADRE $' . number_format($this->data['diferencia'] ?? 0, 0, ',', '.'),
        ], $row);

        $this->setColWidths($ws, [12, 40, 16, 16, 16, 16]);
        return $this->spreadsheet;
    }

    private function buildAnalisisCartera(): Spreadsheet
    {
        $ws  = $this->sheet();
        $ws->setTitle('Análisis de Cartera');
        $row = 1;

        $this->header($ws, $this->data['titulo'], 'Corte al: ' . ($this->data['hasta_label'] ?? ''), $row);
        $this->kpis($ws, $this->data['kpis'] ?? [], $row);

        foreach ($this->data['por_rango'] ?? [] as $rango) {
            if (empty($rango['facturas'])) continue;
            $color = ltrim($rango['color'], '#');
            $this->sectionTitle($ws, "📊  {$rango['rango']}  —  Provisión: {$rango['pct_provision']}%", $row, 7, $color);
            $row++;
            $this->tableHeader($ws, ['N° Factura', 'Arrendatario', 'Inmueble', 'Días vencida', 'Saldo', 'Mora', 'Provisión'], $row);
            $row++;
            foreach ($rango['facturas'] as $f) {
                $this->tableRow($ws, [
                    $f['numero'], $f['arrendatario'], $f['inmueble'] ?? '', $f['dias'],
                    $this->money($f['saldo']), $this->money($f['mora']), $this->money($f['provision']),
                ], $row++);
            }
            $this->totalRow($ws, ['', 'SUBTOTAL', '', '', $this->money($rango['total_saldo']), '', $this->money($rango['total_prov'])], $row++);
            $row++;
        }

        $this->totalRow($ws, ['', 'TOTAL CARTERA', '', '', $this->money($this->data['total_saldo']), '', $this->money($this->data['total_provision'])], $row);
        $this->setColWidths($ws, [14, 32, 12, 14, 18, 18, 18]);
        return $this->spreadsheet;
    }

    private function buildRetenciones(): Spreadsheet
    {
        $ws  = $this->sheet();
        $ws->setTitle('Informe Retenciones');
        $row = 1;

        $this->header($ws, $this->data['titulo'], 'Período: ' . ($this->data['periodo_label'] ?? ''), $row);
        $this->kpis($ws, $this->data['kpis'] ?? [], $row);

        $this->sectionTitle($ws, '📤  RETENCIONES PRACTICADAS', $row, 5, '7F1D1D');
        $row++;
        $this->tableHeader($ws, ['Tercero', 'NIT', 'Cuenta', 'Descripción', 'Valor'], $row, '991B1B');
        $row++;
        foreach ($this->data['practicadas'] ?? [] as $r) {
            $this->tableRow($ws, [$r['tercero'], $r['nit'], $r['cuenta'], $r['cuenta_nom'], $this->money($r['valor'])], $row++);
        }
        $this->totalRow($ws, ['', '', '', 'TOTAL PRACTICADAS', $this->money($this->data['total_practicadas'])], $row++);
        $row++;

        $this->sectionTitle($ws, '📥  RETENCIONES A FAVOR', $row, 5, '14532D');
        $row++;
        $this->tableHeader($ws, ['Tercero', 'NIT', '', '', 'Valor'], $row, '15803D');
        $row++;
        foreach ($this->data['a_favor'] ?? [] as $r) {
            $this->tableRow($ws, [$r['tercero'], $r['nit'], '', '', $this->money($r['valor'])], $row++);
        }
        $this->totalRow($ws, ['', '', '', 'TOTAL A FAVOR', $this->money($this->data['total_a_favor'])], $row++);
        $row++;

        $this->totalRow($ws, ['', '', '', 'NETO A PAGAR DIAN', $this->money($this->data['neto'])], $row);
        $this->setColWidths($ws, [30, 14, 10, 32, 18]);
        return $this->spreadsheet;
    }

    private function buildComisiones(): Spreadsheet
    {
        $ws  = $this->sheet();
        $ws->setTitle('Comisiones e Ingresos');
        $row = 1;

        $this->header($ws, $this->data['titulo'], 'Período: ' . ($this->data['periodo_label'] ?? ''), $row);
        $this->kpis($ws, $this->data['kpis'] ?? [], $row);

        // Por cuenta
        $this->sectionTitle($ws, '📊  INGRESOS POR TIPO', $row, 4);
        $row++;
        $this->tableHeader($ws, ['Cuenta', 'Descripción', 'Cantidad', 'Total'], $row);
        $row++;
        foreach ($this->data['por_cuenta'] ?? [] as $r) {
            $this->tableRow($ws, [$r['cuenta'], $r['nombre'], $r['cantidad'], $this->money($r['total'])], $row++);
        }
        $this->totalRow($ws, ['', 'TOTAL INGRESOS', '', $this->money($this->data['total_ingresos'])], $row++);
        $row++;

        // Por mes
        $this->sectionTitle($ws, '📅  EVOLUCIÓN MENSUAL', $row, 4, '1E3A8A');
        $row++;
        $this->tableHeader($ws, ['Mes', '', '', 'Total'], $row, '1D4ED8');
        $row++;
        foreach ($this->data['por_mes'] ?? [] as $r) {
            $this->tableRow($ws, [$r['mes'], '', '', $this->money($r['total'])], $row++);
        }

        $this->setColWidths($ws, [10, 38, 14, 20]);
        return $this->spreadsheet;
    }

    private function buildConciliacionIVA(): Spreadsheet
    {
        $ws  = $this->sheet();
        $ws->setTitle('Conciliación IVA');
        $row = 1;

        $this->header($ws, $this->data['titulo'], 'Período: ' . ($this->data['periodo_label'] ?? ''), $row);
        $this->kpis($ws, $this->data['kpis'] ?? [], $row);

        $this->sectionTitle($ws, '📊  DETALLE POR PERÍODO', $row, 5);
        $row++;
        $this->tableHeader($ws, ['Período', 'Base comisiones', 'IVA generado 19%', 'IVA descontable', 'Saldo'], $row);
        $row++;
        foreach ($this->data['por_mes'] ?? [] as $r) {
            $this->tableRow($ws, [
                $r['mes'], '', $this->money($r['iva_generado']), $this->money($r['iva_descontable']), $this->money($r['saldo']),
            ], $row++);
        }
        $this->totalRow($ws, [
            'TOTAL', $this->money($this->data['base_comisiones']),
            $this->money($this->data['iva_generado']), $this->money($this->data['iva_descontable']),
            $this->money($this->data['saldo']),
        ], $row);

        $this->setColWidths($ws, [16, 22, 22, 22, 20]);
        return $this->spreadsheet;
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\OwnerLiquidation;
use App\Models\Property;
use App\Models\RentBill;
use App\Models\RentalContract;
use App\Models\Third;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReportesController extends Controller
{
    private array $headerStyle = [
        'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0E01A3']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]],
    ];

    private array $subHeaderStyle = [
        'font'      => ['bold' => true, 'size' => 10],
        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E8EDFF']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]],
    ];

    public function descargar(Request $request, string $tipo)
    {
        $mes  = (int) $request->get('mes', now()->month);
        $anio = (int) $request->get('anio', now()->year);
        $formato = $request->get('formato', 'excel');
        $propietarioId = $request->get('propietario_id');

        return match ($tipo) {
            'cartera'       => $this->carteraGeneral($formato),
            'recaudo'       => $this->recaudoMes($mes, $anio, $formato),
            'mora'          => $this->moraDetallada($formato),
            'portafolio'    => $this->estadoPortafolio($formato),
            'liquidaciones' => $this->liquidacionesPropietario($mes, $anio, $formato, $propietarioId),
            default         => abort(404),
        };
    }

    // ── 1. Cartera General ─────────────────────────────────────────────────
    private function carteraGeneral(string $formato)
    {
        $bills = RentBill::whereIn('estado', ['pendiente', 'parcial', 'en_mora', 'vencida'])
            ->with(['property', 'arrendatario', 'rentalContract'])
            ->orderByDesc('fecha_limite_pago')
            ->get();

        if ($formato === 'pdf') {
            $pdf = Pdf::loadView('reportes.cartera-pdf', compact('bills'))
                ->setPaper('legal', 'landscape');
            return $pdf->download('cartera-general-' . now()->format('Ymd') . '.pdf');
        }

        $ss   = $this->crearSpreadsheet('Cartera General');
        $sheet = $ss->getActiveSheet();

        $this->escribirTitulo($sheet, 'CARTERA GENERAL', 'A1:I1');
        $this->escribirSubtitulo($sheet, 'Generado: ' . now()->format('d/m/Y H:i') . ' | Facturas pendientes de cobro', 'A2:I2');

        $cols = ['A' => 'Factura', 'B' => 'Inmueble', 'C' => 'Dirección', 'D' => 'Arrendatario',
                 'E' => 'Período', 'F' => 'Total Factura', 'G' => 'Pagado', 'H' => 'Saldo',
                 'I' => 'Estado'];
        $this->escribirEncabezados($sheet, $cols, 4);

        $fila = 5;
        $totalFactura = $totalPagado = $totalSaldo = 0;
        foreach ($bills as $b) {
            $sheet->setCellValue("A{$fila}", $b->numero);
            $sheet->setCellValue("B{$fila}", $b->property?->codigo);
            $sheet->setCellValue("C{$fila}", $b->property?->direccion);
            $sheet->setCellValue("D{$fila}", $b->arrendatario?->nombre_completo ?? $b->arrendatario?->razon_social);
            $sheet->setCellValue("E{$fila}", $b->mes . '/' . $b->anio);
            $sheet->setCellValue("F{$fila}", (float) $b->total_factura);
            $sheet->setCellValue("G{$fila}", (float) $b->total_pagado);
            $sheet->setCellValue("H{$fila}", (float) $b->saldo_pendiente);
            $sheet->setCellValue("I{$fila}", strtoupper($b->estado));
            $this->formatearMoneda($sheet, ["F{$fila}", "G{$fila}", "H{$fila}"]);
            if ($fila % 2 === 0) $this->filaAlterna($sheet, "A{$fila}:I{$fila}");
            $totalFactura += $b->total_factura;
            $totalPagado  += $b->total_pagado;
            $totalSaldo   += $b->saldo_pendiente;
            $fila++;
        }

        $this->escribirTotales($sheet, $fila, ['F' => $totalFactura, 'G' => $totalPagado, 'H' => $totalSaldo], 'A', 'I');
        $this->ajustarColumnas($sheet, ['A' => 18, 'B' => 14, 'C' => 28, 'D' => 28, 'E' => 10, 'F' => 16, 'G' => 16, 'H' => 16, 'I' => 14]);

        return $this->descargarExcel($ss, 'cartera-general-' . now()->format('Ymd'));
    }

    // ── 2. Recaudo del mes ─────────────────────────────────────────────────
    private function recaudoMes(int $mes, int $anio, string $formato)
    {
        $bills = RentBill::where('mes', $mes)->where('anio', $anio)
            ->with(['property', 'arrendatario', 'payments'])
            ->orderBy('numero')
            ->get();

        $totalFacturado  = $bills->sum('total_factura');
        $totalRecaudado  = $bills->sum('total_pagado');
        $totalPendiente  = $bills->sum('saldo_pendiente');
        $efectividad     = $totalFacturado > 0 ? round(($totalRecaudado / $totalFacturado) * 100, 1) : 0;
        $nombreMes       = now()->setDate($anio, $mes, 1)->translatedFormat('F Y');

        if ($formato === 'pdf') {
            $pdf = Pdf::loadView('reportes.recaudo-pdf', compact('bills', 'totalFacturado', 'totalRecaudado', 'totalPendiente', 'efectividad', 'nombreMes'))
                ->setPaper('legal', 'landscape');
            return $pdf->download("recaudo-{$mes}-{$anio}.pdf");
        }

        $ss    = $this->crearSpreadsheet("Recaudo {$nombreMes}");
        $sheet = $ss->getActiveSheet();

        $this->escribirTitulo($sheet, "RECAUDO DEL MES — {$nombreMes}", 'A1:J1');
        $this->escribirSubtitulo($sheet, "Facturado: \${$this->fmt($totalFacturado)}  |  Recaudado: \${$this->fmt($totalRecaudado)}  |  Efectividad: {$efectividad}%", 'A2:J2');

        $cols = ['A' => 'Factura', 'B' => 'Inmueble', 'C' => 'Dirección', 'D' => 'Arrendatario',
                 'E' => 'Total Factura', 'F' => 'Pagado', 'G' => 'Saldo', 'H' => 'F. Límite',
                 'I' => 'F. Pago', 'J' => 'Estado'];
        $this->escribirEncabezados($sheet, $cols, 4);

        $fila = 5;
        foreach ($bills as $b) {
            $sheet->setCellValue("A{$fila}", $b->numero);
            $sheet->setCellValue("B{$fila}", $b->property?->codigo);
            $sheet->setCellValue("C{$fila}", $b->property?->direccion);
            $sheet->setCellValue("D{$fila}", $b->arrendatario?->nombre_completo ?? $b->arrendatario?->razon_social);
            $sheet->setCellValue("E{$fila}", (float) $b->total_factura);
            $sheet->setCellValue("F{$fila}", (float) $b->total_pagado);
            $sheet->setCellValue("G{$fila}", (float) $b->saldo_pendiente);
            $sheet->setCellValue("H{$fila}", $b->fecha_limite_pago?->format('d/m/Y'));
            $sheet->setCellValue("I{$fila}", $b->fecha_pago?->format('d/m/Y') ?? '—');
            $sheet->setCellValue("J{$fila}", strtoupper($b->estado));
            $this->formatearMoneda($sheet, ["E{$fila}", "F{$fila}", "G{$fila}"]);
            if ($fila % 2 === 0) $this->filaAlterna($sheet, "A{$fila}:J{$fila}");
            $fila++;
        }

        $this->escribirTotales($sheet, $fila, ['E' => $totalFacturado, 'F' => $totalRecaudado, 'G' => $totalPendiente], 'A', 'J');
        $this->ajustarColumnas($sheet, ['A' => 18, 'B' => 14, 'C' => 28, 'D' => 28, 'E' => 16, 'F' => 16, 'G' => 16, 'H' => 14, 'I' => 14, 'J' => 14]);

        return $this->descargarExcel($ss, "recaudo-{$mes}-{$anio}");
    }

    // ── 3. Mora Detallada ──────────────────────────────────────────────────
    private function moraDetallada(string $formato)
    {
        $bills = RentBill::whereIn('estado', ['en_mora', 'vencida', 'parcial'])
            ->where('saldo_pendiente', '>', 0)
            ->with(['property', 'arrendatario', 'rentalContract'])
            ->orderByDesc('dias_mora')
            ->get();

        $ss    = $this->crearSpreadsheet('Mora Detallada');
        $sheet = $ss->getActiveSheet();

        $this->escribirTitulo($sheet, 'MORA DETALLADA', 'A1:J1');
        $this->escribirSubtitulo($sheet, 'Generado: ' . now()->format('d/m/Y H:i') . ' | Facturas en mora ordenadas por días', 'A2:J2');

        $cols = ['A' => 'Factura', 'B' => 'Inmueble', 'C' => 'Dirección', 'D' => 'Arrendatario',
                 'E' => 'Período', 'F' => 'Saldo', 'G' => 'Días Mora', 'H' => 'Mora Acum.',
                 'I' => 'Total c/Mora', 'J' => 'Estado'];
        $this->escribirEncabezados($sheet, $cols, 4);

        $fila = 5;
        $totalSaldo = $totalMora = 0;
        foreach ($bills as $b) {
            $totalConMora = (float) $b->saldo_pendiente + (float) $b->mora_acumulada;
            $sheet->setCellValue("A{$fila}", $b->numero);
            $sheet->setCellValue("B{$fila}", $b->property?->codigo);
            $sheet->setCellValue("C{$fila}", $b->property?->direccion);
            $sheet->setCellValue("D{$fila}", $b->arrendatario?->nombre_completo ?? $b->arrendatario?->razon_social);
            $sheet->setCellValue("E{$fila}", $b->mes . '/' . $b->anio);
            $sheet->setCellValue("F{$fila}", (float) $b->saldo_pendiente);
            $sheet->setCellValue("G{$fila}", (int) $b->dias_mora);
            $sheet->setCellValue("H{$fila}", (float) $b->mora_acumulada);
            $sheet->setCellValue("I{$fila}", $totalConMora);
            $sheet->setCellValue("J{$fila}", strtoupper($b->estado));

            // Colorear por días mora
            $color = match (true) {
                $b->dias_mora > 90 => 'FEE2E2',
                $b->dias_mora > 60 => 'FEF3C7',
                $b->dias_mora > 30 => 'FFF7ED',
                default            => 'F0FDF4',
            };
            $sheet->getStyle("A{$fila}:J{$fila}")->getFill()
                ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($color);

            $this->formatearMoneda($sheet, ["F{$fila}", "H{$fila}", "I{$fila}"]);
            $totalSaldo += $b->saldo_pendiente;
            $totalMora  += $b->mora_acumulada;
            $fila++;
        }

        $this->escribirTotales($sheet, $fila, ['F' => $totalSaldo, 'H' => $totalMora, 'I' => $totalSaldo + $totalMora], 'A', 'J');
        $this->ajustarColumnas($sheet, ['A' => 18, 'B' => 14, 'C' => 28, 'D' => 28, 'E' => 10, 'F' => 16, 'G' => 12, 'H' => 16, 'I' => 18, 'J' => 14]);

        return $this->descargarExcel($ss, 'mora-detallada-' . now()->format('Ymd'));
    }

    // ── 4. Estado del Portafolio ───────────────────────────────────────────
    private function estadoPortafolio(string $formato)
    {
        $properties = Property::with(['rentalContracts' => fn ($q) => $q->where('estado', 'activo')->with('arrendatario'), 'propietario'])
            ->orderBy('codigo')
            ->get();

        if ($formato === 'pdf') {
            $pdf = Pdf::loadView('reportes.portafolio-pdf', compact('properties'))
                ->setPaper('legal', 'landscape');
            return $pdf->download('portafolio-' . now()->format('Ymd') . '.pdf');
        }

        $ss    = $this->crearSpreadsheet('Portafolio');
        $sheet = $ss->getActiveSheet();

        $this->escribirTitulo($sheet, 'ESTADO DEL PORTAFOLIO', 'A1:J1');
        $this->escribirSubtitulo($sheet, 'Generado: ' . now()->format('d/m/Y H:i') . ' | Todos los inmuebles con su estado actual', 'A2:J2');

        $cols = ['A' => 'Código', 'B' => 'Dirección', 'C' => 'Ciudad', 'D' => 'Tipo',
                 'E' => 'Propietario', 'F' => 'Estado', 'G' => 'Arrendatario', 'H' => 'Canon',
                 'I' => 'Inicio Contrato', 'J' => 'Venc. Contrato'];
        $this->escribirEncabezados($sheet, $cols, 4);

        $fila = 5;
        foreach ($properties as $p) {
            $contrato = $p->rentalContracts->first();
            $sheet->setCellValue("A{$fila}", $p->codigo);
            $sheet->setCellValue("B{$fila}", $p->direccion);
            $sheet->setCellValue("C{$fila}", $p->municipio?->nombre ?? $p->direccion ?? '—');
            $sheet->setCellValue("D{$fila}", $p->tipo?->nombre ?? '—');
            $sheet->setCellValue("E{$fila}", $p->propietario?->nombre_completo ?? $p->propietario?->razon_social ?? '—');
            $sheet->setCellValue("F{$fila}", $contrato ? 'ARRENDADO' : 'DISPONIBLE');
            $sheet->setCellValue("G{$fila}", $contrato?->arrendatario?->nombre_completo ?? $contrato?->arrendatario?->razon_social ?? '—');
            $sheet->setCellValue("H{$fila}", $contrato ? (float) $contrato->canon_mensual : 0);
            $sheet->setCellValue("I{$fila}", $contrato?->fecha_inicio?->format('d/m/Y') ?? '—');
            $sheet->setCellValue("J{$fila}", $contrato?->fecha_fin?->format('d/m/Y') ?? '—');

            $color = $contrato ? 'F0FDF4' : 'EFF6FF';
            $sheet->getStyle("A{$fila}:J{$fila}")->getFill()
                ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($color);

            if ($contrato) $this->formatearMoneda($sheet, ["H{$fila}"]);
            $fila++;
        }

        $this->ajustarColumnas($sheet, ['A' => 14, 'B' => 30, 'C' => 18, 'D' => 16, 'E' => 28, 'F' => 14, 'G' => 28, 'H' => 16, 'I' => 16, 'J' => 16]);

        return $this->descargarExcel($ss, 'portafolio-' . now()->format('Ymd'));
    }

    // ── 5. Liquidaciones por Propietario ───────────────────────────────────
    private function liquidacionesPropietario(int $mes, int $anio, string $formato, ?string $propietarioId)
    {
        $query = OwnerLiquidation::where('mes', $mes)->where('anio', $anio)
            ->with(['property', 'propietario', 'rentalContract']);
        if ($propietarioId) $query->where('propietario_id', $propietarioId);
        $liquidaciones = $query->orderBy('numero')->get();

        $totalCanon     = $liquidaciones->sum('canon_cobrado');
        $totalComision  = $liquidaciones->sum('comision_valor');
        $totalIva       = $liquidaciones->sum('iva_comision');
        $totalReteFuente = $liquidaciones->sum('retefuente_valor');
        $totalGiro      = $liquidaciones->sum('total_giro');
        $nombreMes      = now()->setDate($anio, $mes, 1)->translatedFormat('F Y');

        if ($formato === 'pdf') {
            $pdf = Pdf::loadView('reportes.liquidaciones-pdf', compact(
                'liquidaciones', 'totalCanon', 'totalComision', 'totalIva',
                'totalReteFuente', 'totalGiro', 'nombreMes'
            ))->setPaper('legal', 'landscape');
            return $pdf->download("liquidaciones-{$mes}-{$anio}.pdf");
        }

        $ss    = $this->crearSpreadsheet("Liquidaciones {$nombreMes}");
        $sheet = $ss->getActiveSheet();

        $this->escribirTitulo($sheet, "LIQUIDACIONES PROPIETARIOS — {$nombreMes}", 'A1:K1');
        $this->escribirSubtitulo($sheet, "Total a girar: \${$this->fmt($totalGiro)}", 'A2:K2');

        $cols = ['A' => 'N° Liquid.', 'B' => 'Inmueble', 'C' => 'Dirección', 'D' => 'Propietario',
                 'E' => 'Canon', 'F' => 'Comisión', 'G' => 'IVA Comis.', 'H' => 'ReteFuente',
                 'I' => 'Otros Desc.', 'J' => 'Total Giro', 'K' => 'Estado'];
        $this->escribirEncabezados($sheet, $cols, 4);

        $fila = 5;
        foreach ($liquidaciones as $l) {
            $sheet->setCellValue("A{$fila}", $l->numero);
            $sheet->setCellValue("B{$fila}", $l->property?->codigo);
            $sheet->setCellValue("C{$fila}", $l->property?->direccion);
            $sheet->setCellValue("D{$fila}", $l->propietario?->nombre_completo ?? $l->propietario?->razon_social);
            $sheet->setCellValue("E{$fila}", (float) $l->canon_cobrado);
            $sheet->setCellValue("F{$fila}", (float) $l->comision_valor);
            $sheet->setCellValue("G{$fila}", (float) $l->iva_comision);
            $sheet->setCellValue("H{$fila}", (float) $l->retefuente_valor);
            $sheet->setCellValue("I{$fila}", (float) $l->otros_descuentos);
            $sheet->setCellValue("J{$fila}", (float) $l->total_giro);
            $sheet->setCellValue("K{$fila}", strtoupper($l->estado));
            $this->formatearMoneda($sheet, ["E{$fila}", "F{$fila}", "G{$fila}", "H{$fila}", "I{$fila}", "J{$fila}"]);
            if ($fila % 2 === 0) $this->filaAlterna($sheet, "A{$fila}:K{$fila}");
            $fila++;
        }

        $this->escribirTotales($sheet, $fila, [
            'E' => $totalCanon, 'F' => $totalComision, 'G' => $totalIva,
            'H' => $totalReteFuente, 'J' => $totalGiro,
        ], 'A', 'K');
        $this->ajustarColumnas($sheet, ['A' => 16, 'B' => 14, 'C' => 28, 'D' => 28, 'E' => 16, 'F' => 16, 'G' => 14, 'H' => 14, 'I' => 14, 'J' => 16, 'K' => 14]);

        return $this->descargarExcel($ss, "liquidaciones-{$mes}-{$anio}");
    }

    // ── Helpers ────────────────────────────────────────────────────────────
    private function crearSpreadsheet(string $titulo): Spreadsheet
    {
        $ss = new Spreadsheet();
        $ss->getProperties()
            ->setCreator('YarOM ERP')
            ->setTitle($titulo)
            ->setCompany('Serviarrendar S.A.S');
        return $ss;
    }

    private function escribirTitulo($sheet, string $texto, string $rango): void
    {
        [$celda] = explode(':', $rango);
        $sheet->setCellValue($celda, $texto);
        $sheet->mergeCells($rango);
        $sheet->getStyle($rango)->applyFromArray([
            'font'      => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0E01A3']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(28);
    }

    private function escribirSubtitulo($sheet, string $texto, string $rango): void
    {
        [$celda] = explode(':', $rango);
        $sheet->setCellValue($celda, $texto);
        $sheet->mergeCells($rango);
        $sheet->getStyle($rango)->applyFromArray([
            'font'      => ['bold' => false, 'size' => 10, 'color' => ['rgb' => '475569']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F1F5F9']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getRowDimension(2)->setRowHeight(18);
    }

    private function escribirEncabezados($sheet, array $cols, int $fila): void
    {
        $letras = array_keys($cols);
        $rango  = $letras[0] . $fila . ':' . end($letras) . $fila;
        foreach ($cols as $col => $label) {
            $sheet->setCellValue("{$col}{$fila}", $label);
        }
        $sheet->getStyle($rango)->applyFromArray($this->subHeaderStyle);
        $sheet->getRowDimension($fila)->setRowHeight(20);
    }

    private function escribirTotales($sheet, int $fila, array $totales, string $desde, string $hasta): void
    {
        $sheet->setCellValue("{$desde}{$fila}", 'TOTALES');
        $sheet->getStyle("{$desde}{$fila}")->getFont()->setBold(true);
        foreach ($totales as $col => $val) {
            $sheet->setCellValue("{$col}{$fila}", $val);
            $this->formatearMoneda($sheet, ["{$col}{$fila}"]);
        }
        $sheet->getStyle("{$desde}{$fila}:{$hasta}{$fila}")->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E8EDFF']],
            'borders' => ['top' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '0E01A3']]],
        ]);
    }

    private function formatearMoneda($sheet, array $celdas): void
    {
        foreach ($celdas as $celda) {
            $sheet->getStyle($celda)->getNumberFormat()
                ->setFormatCode('_($* #,##0.00_);_($* (#,##0.00);_($* "-"??_);_(@_)');
            $sheet->getStyle($celda)->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        }
    }

    private function filaAlterna($sheet, string $rango): void
    {
        $sheet->getStyle($rango)->getFill()
            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F8FAFF');
    }

    private function ajustarColumnas($sheet, array $anchos): void
    {
        foreach ($anchos as $col => $ancho) {
            $sheet->getColumnDimension($col)->setWidth($ancho);
        }
    }

    private function descargarExcel(Spreadsheet $ss, string $nombre)
    {
        $writer = new Xlsx($ss);
        $archivo = tempnam(sys_get_temp_dir(), 'reporte_');
        $writer->save($archivo);
        return response()->download($archivo, "{$nombre}.xlsx", [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    private function fmt(float $val): string
    {
        return number_format($val, 0, ',', '.');
    }
}

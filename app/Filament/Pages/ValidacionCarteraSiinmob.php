<?php

namespace App\Filament\Pages;

use App\Models\SiinmobCarteraMovimiento;
use Filament\Pages\Page;

class ValidacionCarteraSiinmob extends Page
{
    protected string $view = 'filament.pages.validacion-cartera-siinmob';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';
    protected static ?string $navigationLabel = 'Validación Cartera (Siinmob)';
    protected static string|\UnitEnum|null $navigationGroup = 'Contabilidad';
    protected static ?int $navigationSort = 21;

    public function getTitle(): string { return 'Validación de Cartera — Arrendatarios (Siinmob)'; }

    public array $meses = [];
    public float $totalDebe = 0;
    public float $totalHaber = 0;
    public float $saldoInicialEstimado = 0;
    public float $saldoFinalCalculado = 0;
    public float $saldoRealCargado = 0;

    public function mount(): void
    {
        // Movimientos reales de cartera de arrendatarios (recibos de ingreso +
        // notas contables/automaticas que tocan la cuenta 1305xx), extraidos
        // directamente del reporte "Cartera > Arrendatarios" de Siinmob.
        $filas = SiinmobCarteraMovimiento::query()
            ->where('tipo_cartera', 'cxc')
            ->selectRaw("DATE_FORMAT(fecha, '%Y-%m') as mes, SUM(debito) as debe, SUM(credito) as haber")
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();

        $this->meses = $filas->map(fn ($f) => [
            'mes' => $f->mes,
            'debe' => (float) $f->debe,
            'haber' => (float) $f->haber,
            'diferencia' => (float) $f->debe - (float) $f->haber,
        ])->toArray();

        $this->totalDebe = $filas->sum('debe');
        $this->totalHaber = $filas->sum('haber');

        $this->saldoRealCargado = (float) \App\Models\CuentaPorCobrar::where('tipo', 'saldo_inicial_siinmob')->sum('valor_original');

        // Saldo final calculado = lo que queda si el saldo cargado (30-jun-2026) es correcto,
        // partiendo desde el inicio de los datos importados (ene-2025) hacia atras.
        $this->saldoFinalCalculado = $this->saldoRealCargado;
        $this->saldoInicialEstimado = $this->saldoRealCargado - ($this->totalDebe - $this->totalHaber);
    }

    public function getMesLabel(string $mes): string
    {
        $meses = [
            '01'=>'Enero','02'=>'Febrero','03'=>'Marzo','04'=>'Abril','05'=>'Mayo','06'=>'Junio',
            '07'=>'Julio','08'=>'Agosto','09'=>'Septiembre','10'=>'Octubre','11'=>'Noviembre','12'=>'Diciembre',
        ];
        [$anio, $m] = explode('-', $mes);
        return ($meses[$m] ?? $m) . ' ' . $anio;
    }
}

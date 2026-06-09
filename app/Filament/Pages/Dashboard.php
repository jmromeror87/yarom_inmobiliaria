<?php

namespace App\Filament\Pages;

use App\Models\OwnerLiquidation;
use App\Models\Property;
use App\Models\RentBill;
use App\Models\RentPayment;
use App\Models\RentalContract;
use App\Models\Request as Solicitud;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Facades\Auth;

class Dashboard extends BaseDashboard
{
    public function getView(): string
    {
        return 'filament.pages.dashboard';
    }

    public function getWidgets(): array
    {
        return [];
    }

    public function getViewData(): array
    {
        $mes     = now()->month;
        $anio    = now()->year;
        $mesAnt  = now()->subMonth()->month;
        $anioAnt = now()->subMonth()->year;
        $fmt     = fn ($v) => '$' . number_format((float) $v, 0, ',', '.');

        // ── KPIs principales ─────────────────────────────────────────────
        $totalInm    = Property::count();
        $arrendados  = Property::where('estado', 'arrendado')->count();
        $disponibles = Property::where('estado', 'disponible')->count();
        $ocupacion   = $totalInm > 0 ? round($arrendados / $totalInm * 100, 1) : 0;

        $contrActivos  = RentalContract::where('estado', 'activo')->count();
        $porVencer30   = RentalContract::where('estado', 'activo')
            ->whereBetween('fecha_fin', [now(), now()->addDays(30)])->count();

        $facturadoMes  = (float) RentBill::where('mes', $mes)->where('anio', $anio)->sum('total_factura');
        $facturadoAnt  = (float) RentBill::where('mes', $mesAnt)->where('anio', $anioAnt)->sum('total_factura');

        $recaudadoMes  = (float) RentPayment::whereYear('fecha_pago', $anio)->whereMonth('fecha_pago', $mes)->sum('total_pagado');

        $cartera       = (float) RentBill::whereIn('estado', ['pendiente', 'parcial', 'en_mora', 'vencida'])->sum('saldo_pendiente');
        $factsMora     = RentBill::where('estado', 'en_mora')->count();

        $efectividad   = $facturadoMes > 0 ? round($recaudadoMes / $facturadoMes * 100, 1) : 0;

        $solicitudes   = Solicitud::whereIn('estado', ['radicada', 'en_estudio'])->count();
        $liquidPend    = OwnerLiquidation::where('estado', 'pendiente')->count();

        // ── Tendencia recaudo 7 días ──────────────────────────────────────
        $recaudo7dias = collect(range(6, 0))->map(fn ($i) => [
            'dia'   => now()->subDays($i)->format('d/m'),
            'valor' => (float) RentPayment::whereDate('fecha_pago', now()->subDays($i))->sum('total_pagado'),
        ])->values();

        // ── Tendencia recaudo 6 meses ─────────────────────────────────────
        $recaudo6meses = collect(range(5, 0))->map(fn ($i) => [
            'mes'   => now()->subMonths($i)->translatedFormat('M'),
            'valor' => (float) RentPayment::whereYear('fecha_pago', now()->subMonths($i)->year)
                ->whereMonth('fecha_pago', now()->subMonths($i)->month)->sum('total_pagado'),
            'factu' => (float) RentBill::where('mes', now()->subMonths($i)->month)
                ->where('anio', now()->subMonths($i)->year)->sum('total_factura'),
        ])->values();

        // ── Últimas facturas ──────────────────────────────────────────────
        $ultimasFacturas = RentBill::with(['rentalContract.third', 'rentalContract.property'])
            ->orderByDesc('created_at')->limit(8)->get();

        // ── Contratos por vencer ──────────────────────────────────────────
        $contratosPorVencer = RentalContract::with(['third', 'property'])
            ->where('estado', 'activo')
            ->whereBetween('fecha_fin', [now(), now()->addDays(60)])
            ->orderBy('fecha_fin')->limit(5)->get();

        // ── Accesos rápidos ───────────────────────────────────────────────
        $accesos = [
            ['label' => 'Nuevo Contrato',   'icon' => 'heroicon-o-document-plus',    'url' => '/admin/rental-contracts/create', 'color' => '#2563EB'],
            ['label' => 'Registrar Pago',   'icon' => 'heroicon-o-banknotes',        'url' => '/admin/rent-bills',              'color' => '#10b981'],
            ['label' => 'Nuevo Inmueble',   'icon' => 'heroicon-o-building-office',  'url' => '/admin/properties/create',       'color' => '#E11D48'],
            ['label' => 'Nuevo Tercero',    'icon' => 'heroicon-o-user-plus',        'url' => '/admin/thirds/create',           'color' => '#f59e0b'],
            ['label' => 'Liquidar Propiet.','icon' => 'heroicon-o-calculator',       'url' => '/admin/owner-liquidations',      'color' => '#8b5cf6'],
            ['label' => 'Informes',         'icon' => 'heroicon-o-chart-bar',        'url' => '/admin/informes-contables',      'color' => '#06b6d4'],
        ];

        return [
            'user'              => Auth::user(),
            'totalInm'          => $totalInm,
            'arrendados'        => $arrendados,
            'disponibles'       => $disponibles,
            'ocupacion'         => $ocupacion,
            'contrActivos'      => $contrActivos,
            'porVencer30'       => $porVencer30,
            'facturadoMes'      => $fmt($facturadoMes),
            'facturadoAnt'      => $fmt($facturadoAnt),
            'recaudadoMes'      => $fmt($recaudadoMes),
            'cartera'           => $fmt($cartera),
            'factsMora'         => $factsMora,
            'efectividad'       => $efectividad,
            'solicitudes'       => $solicitudes,
            'liquidPend'        => $liquidPend,
            'recaudo7dias'      => $recaudo7dias,
            'recaudo6meses'     => $recaudo6meses,
            'ultimasFacturas'   => $ultimasFacturas,
            'contratosPorVencer'=> $contratosPorVencer,
            'accesos'           => $accesos,
            'mesLabel'          => now()->translatedFormat('F Y'),
        ];
    }
}

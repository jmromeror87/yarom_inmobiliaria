<?php

namespace App\Filament\Pages;

use App\Models\CuentaPorCobrar;
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
        $ultimasFacturas = RentBill::with(['rentalContract.arrendatario', 'rentalContract.property'])
            ->orderByDesc('created_at')->limit(8)->get();

        // ── Contratos por vencer ──────────────────────────────────────────
        $contratosPorVencer = RentalContract::with(['arrendatario', 'property'])
            ->where('estado', 'activo')
            ->whereBetween('fecha_fin', [now(), now()->addDays(60)])
            ->orderBy('fecha_fin')->limit(5)->get();

        // ── Cartera por antigüedad de mora (activa + heredada Siinmob) ────
        $bucketLabels = ['Al día', '1-30', '31-60', '61-90', '91-180', '+180'];
        $bucket = function (int $dias) {
            if ($dias >= 0) return 0;
            if ($dias >= -30) return 1;
            if ($dias >= -60) return 2;
            if ($dias >= -90) return 3;
            if ($dias >= -180) return 4;
            return 5;
        };
        $carteraBuckets = [0, 0, 0, 0, 0, 0];
        foreach (RentBill::whereIn('estado', ['pendiente', 'parcial', 'en_mora', 'vencida'])->where('saldo_pendiente', '>', 0)->get(['saldo_pendiente', 'fecha_limite_pago']) as $b) {
            if (!$b->fecha_limite_pago) continue;
            $dias = (int) now()->startOfDay()->diffInDays($b->fecha_limite_pago->startOfDay(), false);
            $carteraBuckets[$bucket($dias)] += (float) $b->saldo_pendiente;
        }
        foreach (CuentaPorCobrar::whereIn('estado', ['pendiente', 'parcial'])->where('saldo', '>', 0)->get(['saldo', 'fecha_vencimiento']) as $c) {
            if (!$c->fecha_vencimiento) continue;
            $dias = (int) now()->startOfDay()->diffInDays($c->fecha_vencimiento->startOfDay(), false);
            $carteraBuckets[$bucket($dias)] += (float) $c->saldo;
        }

        // ── Ocupación por estado del inmueble ──────────────────────────────
        $ocupacionEstados = Property::selectRaw('estado, count(*) c')->groupBy('estado')->pluck('c', 'estado');

        // ── Salud del cierre mensual ────────────────────────────────────────
        $contratosSinFacturar = RentalContract::where('estado', 'activo')
            ->whereNotNull('fecha_entrega_efectiva')
            ->whereDoesntHave('rentBills', fn ($q) => $q->where('mes', $mes)->where('anio', $anio))
            ->count();

        $vencidasSinMoraVerificada = RentBill::whereIn('estado', ['pendiente', 'parcial'])
            ->whereRaw('DATE_ADD(fecha_limite_pago, INTERVAL dias_gracia DAY) < ?', [now()->toDateString()])
            ->whereDoesntHave('property.businessOrigin', fn ($q) => $q->where('nombre', 'Victoria'))
            ->count();

        $liquidacionesPendientesAprobar = OwnerLiquidation::where('estado', 'pendiente')->count();
        $girosPendientes = OwnerLiquidation::whereIn('estado', ['pendiente', 'aprobada'])->count();
        $girosPendientesMonto = (float) OwnerLiquidation::whereIn('estado', ['pendiente', 'aprobada'])->sum('total_giro');

        $mesLabelStr = now()->translatedFormat('F Y');
        $saludCierre = [
            [
                'label' => 'Facturación del mes',
                'ok'    => $contratosSinFacturar === 0,
                'texto' => $contratosSinFacturar === 0 ? 'Todos los contratos activos tienen factura de ' . $mesLabelStr : "{$contratosSinFacturar} contrato(s) activo(s) sin factura de {$mesLabelStr}",
            ],
            [
                'label' => 'Verificación de mora',
                'ok'    => $vencidasSinMoraVerificada === 0,
                'texto' => $vencidasSinMoraVerificada === 0 ? 'Al día — sin facturas vencidas por verificar' : "{$vencidasSinMoraVerificada} factura(s) vencida(s) sin marcar en mora",
            ],
            [
                'label' => 'Liquidaciones por aprobar',
                'ok'    => $liquidacionesPendientesAprobar === 0,
                'texto' => $liquidacionesPendientesAprobar === 0 ? 'Sin liquidaciones pendientes de aprobar' : "{$liquidacionesPendientesAprobar} liquidación(es) esperando aprobación",
            ],
            [
                'label' => 'Giros a propietarios',
                'ok'    => $girosPendientes === 0,
                'texto' => $girosPendientes === 0 ? 'Todos los propietarios están al día' : "{$girosPendientes} giro(s) pendientes — " . $fmt($girosPendientesMonto),
            ],
        ];

        // ── Accesos rápidos ───────────────────────────────────────────────
        $accesos = [
            ['label' => 'Nuevo Contrato',   'icon' => 'heroicon-o-document-plus',    'url' => '/admin/contratos-arriendo/create', 'color' => '#2563EB'],
            ['label' => 'Facturas Arriendo','icon' => 'heroicon-o-banknotes',        'url' => '/admin/facturacion',               'color' => '#10b981'],
            ['label' => 'Nuevo Inmueble',   'icon' => 'heroicon-o-building-office',  'url' => '/admin/properties/create',         'color' => '#E11D48'],
            ['label' => 'Nuevo Tercero',    'icon' => 'heroicon-o-user-plus',        'url' => '/admin/thirds/create',             'color' => '#f59e0b'],
            ['label' => 'Liquidar Propiet.','icon' => 'heroicon-o-calculator',       'url' => '/admin/owner-liquidations',        'color' => '#8b5cf6'],
            ['label' => 'Informes',         'icon' => 'heroicon-o-chart-bar',        'url' => '/admin/informes-contables',        'color' => '#06b6d4'],
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
            'bucketLabels'      => $bucketLabels,
            'carteraBuckets'    => $carteraBuckets,
            'ocupacionEstados'  => $ocupacionEstados,
            'saludCierre'       => $saludCierre,
        ];
    }
}

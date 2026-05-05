<?php

namespace App\Filament\Widgets;

use App\Models\Property;
use App\Models\Third;
use App\Models\AdministrationContract;
use App\Models\Request as SolicitudEstudio;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Carbon\Carbon;

class ResumenOperativoWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 12;

    // Recibe filtros del Dashboard
    public ?array $filters = null;

    protected function getStats(): array
    {
        $inicio = isset($this->filters['startDate'])
            ? Carbon::parse($this->filters['startDate'])
            : now()->startOfMonth();

        $fin = isset($this->filters['endDate'])
            ? Carbon::parse($this->filters['endDate'])
            : now()->endOfMonth();

        // ── PROPIEDADES ──────────────────────────────
        $totalPropiedades   = Property::whereNull('deleted_at')->count();
        $propArrendadas     = Property::whereNull('deleted_at')->where('estado', 'arrendado')->count();
        $propDisponibles    = Property::whereNull('deleted_at')->where('estado', 'disponible')->count();

        // ── CONTRATOS ADMINISTRACIÓN ─────────────────
        $contratosActivos   = AdministrationContract::whereNull('deleted_at')
                                ->where('estado', 'activo')->count();

        // Contratos que vencen en los próximos 30 días
        $porVencer = AdministrationContract::whereNull('deleted_at')
                        ->where('estado', 'activo')
                        ->whereBetween('fecha_fin', [now(), now()->addDays(30)])
                        ->count();

        // ── TERCEROS ─────────────────────────────────
        $totalTerceros      = Third::whereNull('deleted_at')->where('is_active', true)->count();
        $propietarios       = Third::whereNull('deleted_at')->where('es_propietario', true)->count();
        $arrendatarios      = Third::whereNull('deleted_at')->where('es_arrendatario', true)->count();

        // ── SOLICITUDES (en el período) ──────────────
        $solicitudesPeriodo = SolicitudEstudio::whereNull('deleted_at')
                                ->whereBetween('created_at', [$inicio->startOfDay(), $fin->endOfDay()])
                                ->count();

        $solicitudesPendientes = SolicitudEstudio::whereNull('deleted_at')
                                    ->whereIn('estado', ['radicada', 'en_estudio'])
                                    ->count();

        // ── CANON TOTAL ADMINISTRADO ─────────────────
        $canonTotal = AdministrationContract::whereNull('deleted_at')
                        ->where('estado', 'activo')
                        ->sum('canon_pactado');

        $canonFormatted = '$ ' . number_format($canonTotal, 0, ',', '.');

        // Ocupación
        $ocupacion = $totalPropiedades > 0
            ? round(($propArrendadas / $totalPropiedades) * 100, 1)
            : 0;

        return [
            Stat::make('Propiedades Totales', $totalPropiedades)
                ->description("{$propArrendadas} arrendadas · {$propDisponibles} disponibles")
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('info')
                ->chart([
                    $propDisponibles,
                    $propArrendadas,
                    Property::whereNull('deleted_at')->where('estado', 'en_captacion')->count(),
                ]),

            Stat::make('Ocupación', $ocupacion . '%')
                ->description($porVencer > 0 ? "{$porVencer} contratos por vencer (30 días)" : 'Sin vencimientos próximos')
                ->descriptionIcon($porVencer > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->color($porVencer > 0 ? 'warning' : 'success'),

            Stat::make('Canon Total Administrado', $canonFormatted)
                ->description("{$contratosActivos} contratos activos")
                ->descriptionIcon('heroicon-m-document-text')
                ->color('success'),

            Stat::make('Solicitudes Pendientes', $solicitudesPendientes)
                ->description("{$solicitudesPeriodo} radicadas en el período")
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color($solicitudesPendientes > 0 ? 'warning' : 'gray'),

            Stat::make('Propietarios', $propietarios)
                ->description("{$arrendatarios} arrendatarios · {$totalTerceros} total terceros")
                ->descriptionIcon('heroicon-m-users')
                ->color('gray'),
        ];
    }
}
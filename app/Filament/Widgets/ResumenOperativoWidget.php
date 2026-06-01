<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Properties\PropertyResource;
use App\Filament\Resources\RentalContracts\RentalContractResource;
use App\Filament\Resources\Requests\RequestResource;
use App\Filament\Resources\Thirds\ThirdResource;
use App\Models\AdministrationContract;
use App\Models\Property;
use App\Models\RentalContract;
use App\Models\Third;
use App\Models\Request as Solicitud;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ResumenOperativoWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        // ── Inmuebles ─────────────────────────────────────────────────────
        $totalInm   = Property::count();
        $arrendados = Property::where('estado', 'arrendado')->count();
        $disponibles= Property::where('estado', 'disponible')->count();
        $captacion  = Property::where('estado', 'en_captacion')->count();
        $ocupacion  = $totalInm > 0 ? round($arrendados / $totalInm * 100, 1) : 0;

        // Ocupación mes anterior
        $arrAnt = RentalContract::where('estado', 'activo')
            ->where('fecha_inicio', '<=', now()->subMonth()->endOfMonth())
            ->where(fn ($q) => $q->whereNull('fecha_fin')->orWhere('fecha_fin', '>=', now()->subMonth()->startOfMonth()))
            ->count();
        $ocupAnt = $totalInm > 0 ? round($arrAnt / $totalInm * 100, 1) : 0;
        [$ocupIcon, $ocupColor, $ocupDiff] = self::trend($ocupacion, $ocupAnt, true);

        // ── Contratos ─────────────────────────────────────────────────────
        $contrActivos = RentalContract::where('estado', 'activo')->count();
        $contrAnt     = RentalContract::where('estado', 'activo')
            ->where('fecha_inicio', '<=', now()->subMonth()->endOfMonth())->count();
        [$cIcon, $cColor, $cDiff] = self::trend($contrActivos, $contrAnt, true);

        $porVencer30 = RentalContract::where('estado', 'activo')
            ->whereBetween('fecha_fin', [now(), now()->addDays(30)])->count();
        $porVencer60 = RentalContract::where('estado', 'activo')
            ->whereBetween('fecha_fin', [now(), now()->addDays(60)])->count();

        // ── Terceros ─────────────────────────────────────────────────────
        $propietarios  = Third::where('es_propietario', true)->where('is_active', true)->count();
        $arrendatarios = Third::where('es_arrendatario', true)->where('is_active', true)->count();
        $tercAnt       = Third::where('is_active', true)->whereDate('created_at', '<=', now()->subMonth()->endOfMonth())->count();
        $tercHoy       = Third::where('is_active', true)->count();
        [$tIcon, $tColor, $tDiff] = self::trend($tercHoy, $tercAnt, true);

        // ── Solicitudes ──────────────────────────────────────────────────
        $solPend  = Solicitud::whereIn('estado', ['radicada', 'en_estudio'])->count();
        $solMes   = Solicitud::whereIn('estado', ['radicada', 'en_estudio'])
            ->whereMonth('created_at', now()->month)->count();
        $solMesAnt= Solicitud::whereIn('estado', ['radicada', 'en_estudio'])
            ->whereMonth('created_at', now()->subMonth()->month)->count();
        [$sIcon, $sColor, $sDiff] = self::trend($solMes, $solMesAnt, true);

        // ── Contratos adm activos ─────────────────────────────────────────
        $admActivos = AdministrationContract::where('estado', 'activo')->count();

        // Chart ocupación 6 meses
        $chartOcup = collect(range(5, 0))->map(function ($i) use ($totalInm) {
            $d = now()->subMonths($i);
            $a = RentalContract::where('estado', 'activo')
                ->where('fecha_inicio', '<=', $d->copy()->endOfMonth())
                ->where(fn ($q) => $q->whereNull('fecha_fin')->orWhere('fecha_fin', '>=', $d->copy()->startOfMonth()))
                ->count();
            return $totalInm > 0 ? round($a / $totalInm * 100, 0) : 0;
        })->values()->toArray();

        $baseContr = RentalContractResource::getUrl('index');
        $baseProps = PropertyResource::getUrl('index');
        $baseSol   = RequestResource::getUrl('index');
        $baseTer   = ThirdResource::getUrl('index');

        return [
            Stat::make('Inmuebles en portafolio', $totalInm)
                ->description("{$arrendados} arrendados · {$disponibles} disponibles · {$captacion} en captación")
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('primary')
                ->url($baseProps),

            Stat::make('Ocupación del portafolio', $ocupacion . '%')
                ->description($ocupDiff)
                ->descriptionIcon($ocupIcon)
                ->color($ocupColor)
                ->chart($chartOcup)
                ->url($baseProps . '?tableFilters[estado][value]=arrendado'),

            Stat::make('Contratos arriendo activos', $contrActivos)
                ->description($cDiff . ' · ' . $admActivos . ' adm. activos')
                ->descriptionIcon($cIcon)
                ->color($cColor)
                ->url($baseContr . '?tableFilters[estado][value]=activo'),

            Stat::make('Por vencer (60 días)', $porVencer60)
                ->description($porVencer30 > 0 ? "{$porVencer30} vencen en los próximos 30 días" : 'Sin vencimientos en 30 días')
                ->descriptionIcon($porVencer60 > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-badge')
                ->color($porVencer30 > 0 ? 'danger' : ($porVencer60 > 0 ? 'warning' : 'success'))
                ->url($baseContr . '?tableFilters[estado][value]=activo'),

            Stat::make('Terceros activos', $tercHoy)
                ->description($tDiff . ' · ' . $propietarios . ' prop. / ' . $arrendatarios . ' arrend.')
                ->descriptionIcon($tIcon)
                ->color($tColor)
                ->url($baseTer),

            Stat::make('Solicitudes en proceso', $solPend)
                ->description($sDiff . ' radicadas este mes')
                ->descriptionIcon($sIcon)
                ->color($solPend > 0 ? 'warning' : 'gray')
                ->url($baseSol . '?tableFilters[estado][value]=en_estudio'),
        ];
    }

    /**
     * Calcula trend: [icon, color, descripción]
     * $positive = true cuando subir es bueno (ocupación, recaudo)
     * $positive = false cuando subir es malo (mora, cartera vencida)
     */
    private static function trend(float $actual, float $anterior, bool $positivoEsBueno): array
    {
        if ($anterior == 0) {
            return ['heroicon-m-minus', 'gray', 'Sin datos del mes anterior'];
        }
        $diff = round(($actual - $anterior) / $anterior * 100, 1);
        $sube = $diff > 0;
        $igual = abs($diff) < 0.1;

        if ($igual) {
            return ['heroicon-m-minus', 'gray', 'Sin cambios vs mes anterior'];
        }

        $bueno = $positivoEsBueno ? $sube : !$sube;
        $icon  = $sube ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down';
        $color = $bueno ? 'success' : 'danger';
        $signo = $sube ? '+' : '';
        $desc  = "{$signo}{$diff}% vs mes anterior";

        return [$icon, $color, $desc];
    }
}

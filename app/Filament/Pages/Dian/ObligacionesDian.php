<?php

namespace App\Filament\Pages\Dian;

use App\Models\DianDeclaration;
use App\Services\DianObligationService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ObligacionesDian extends Page
{
    protected string $view = 'filament.dian.obligaciones-dashboard';
    protected static ?string $title = 'Calendario Tributario DIAN';
    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-calendar';
    protected static string|\UnitEnum|null $navigationGroup = 'Contabilidad';
    protected static ?string $navigationLabel = 'Calendario Tributario';
    protected static ?int    $navigationSort  = 9;

    public int $anio;

    public function mount(): void
    {
        $this->anio = now()->year;
    }

    public function getObligacionesVencidas(): \Illuminate\Support\Collection
    {
        return DianDeclaration::vencidas()->with('obligationType')
            ->orderBy('fecha_vencimiento')->get();
    }

    public function getObligacionesProximas(): \Illuminate\Support\Collection
    {
        return DianDeclaration::proximas(60)->with('obligationType')
            ->orderBy('fecha_vencimiento')->get();
    }

    public function getObligacionesAnio(): \Illuminate\Support\Collection
    {
        return DianDeclaration::delAnio($this->anio)
            ->with('obligationType')
            ->orderBy('fecha_vencimiento')
            ->get()
            ->groupBy('obligationType.codigo');
    }

    public function getResumen(): array
    {
        $all = DianDeclaration::delAnio($this->anio)->get();
        return [
            'total'      => $all->count(),
            'pendientes' => $all->whereIn('estado', ['pendiente', 'en_proceso'])->count(),
            'vencidas'   => $all->filter(fn($d) => $d->esta_vencida)->count(),
            'urgentes'   => $all->filter(fn($d) => $d->es_urgenta)->count(),
            'presentadas'=> $all->where('estado', 'presentada')->count(),
            'pagadas'    => $all->where('estado', 'pagada')->count(),
            'valor_pendiente' => $all->whereIn('estado', ['pendiente','en_proceso','presentada'])
                ->sum('valor_a_pagar'),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generar_' . now()->year)
                ->label('Generar períodos ' . now()->year)
                ->icon('heroicon-o-plus-circle')->color('primary')
                ->requiresConfirmation()
                ->action(function () {
                    $n = DianObligationService::generarPeriodosAnio($this->anio);
                    Notification::make()->title("{$n} períodos DIAN generados para {$this->anio}")->success()->send();
                }),

            Action::make('generar_anterior')
                ->label('Generar ' . (now()->year - 1))
                ->icon('heroicon-o-clock')->color('gray')
                ->requiresConfirmation()
                ->action(function () {
                    $n = DianObligationService::generarPeriodosAnio($this->anio - 1);
                    Notification::make()->title("{$n} períodos DIAN generados para " . ($this->anio - 1))->success()->send();
                }),
        ];
    }
}

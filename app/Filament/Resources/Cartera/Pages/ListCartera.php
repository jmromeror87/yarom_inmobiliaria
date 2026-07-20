<?php

namespace App\Filament\Resources\Cartera\Pages;

use App\Filament\Resources\Cartera\CarteraResource;
use App\Models\CuentaPorCobrar;
use App\Models\RentBill;
use Filament\Actions\Action;
use Filament\Resources\Pages\Page;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

class ListCartera extends Page
{
    use WithPagination;

    protected static string $resource = CarteraResource::class;

    protected string $view = 'filament.resources.cartera.pages.list-cartera';

    #[Url]
    public string $tab = 'resumen';

    public function setTab(string $tab): void
    {
        $this->tab = $tab;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('crear')
                ->label('Nueva cuenta por cobrar')
                ->icon('heroicon-o-plus')
                ->url(CarteraResource::getUrl('create'))
                ->extraAttributes([
                    'style' => 'background:linear-gradient(135deg,#1e3a8a,#E11D48)!important;color:#fff!important;border:none!important;box-shadow:0 4px 14px rgba(30,58,138,.35)!important;font-weight:700!important;',
                ]),
        ];
    }

    public function getKpisProperty(): array
    {
        $activa = (float) RentBill::whereIn('estado', ['pendiente', 'parcial', 'en_mora', 'vencida'])->sum('saldo_pendiente');
        $activaCount = RentBill::whereIn('estado', ['pendiente', 'parcial', 'en_mora', 'vencida'])->count();

        $heredado = (float) CuentaPorCobrar::whereIn('estado', ['pendiente', 'parcial'])->sum('saldo');
        $heredadoCount = CuentaPorCobrar::whereIn('estado', ['pendiente', 'parcial'])->count();

        $vencidaActiva = (float) RentBill::whereIn('estado', ['en_mora', 'vencida'])->sum('saldo_pendiente');
        $vencidaHeredado = (float) CuentaPorCobrar::whereIn('estado', ['pendiente', 'parcial'])
            ->where('fecha_vencimiento', '<', now())->sum('saldo');

        $recaudadoMes = (float) \App\Models\RentPayment::whereYear('fecha_pago', now()->year)
            ->whereMonth('fecha_pago', now()->month)->sum('total_pagado');

        return [
            'total_pendiente'   => $activa + $heredado,
            'total_count'       => $activaCount + $heredadoCount,
            'activa'            => $activa,
            'activa_count'      => $activaCount,
            'heredado'          => $heredado,
            'heredado_count'    => $heredadoCount,
            'vencida'           => $vencidaActiva + $vencidaHeredado,
            'recaudado_mes'     => $recaudadoMes,
        ];
    }

    public function getCarteraActivaProperty()
    {
        return RentBill::whereIn('estado', ['pendiente', 'parcial', 'en_mora', 'vencida'])
            ->where('saldo_pendiente', '>', 0)
            ->with('arrendatario')
            ->orderBy('fecha_limite_pago')
            ->paginate(15, ['*'], 'activa_page');
    }

    public function getHeredadoProperty()
    {
        return CuentaPorCobrar::with('third')
            ->orderByDesc('created_at')
            ->paginate(15, ['*'], 'heredado_page');
    }
}

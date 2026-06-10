<?php

namespace App\Filament\Widgets;

use App\Models\AccountingAccount;
use Filament\Widgets\Widget;
use Livewire\Attributes\On;

class PlanCuentasStatsWidget extends Widget
{
    protected string $view = 'filament.widgets.plan-cuentas-stats';
    protected int|string|array $columnSpan = 'full';
    protected static ?int $sort = 1;
    public static function canView(): bool { return true; }

    public ?string $claseActiva = null;

    public function filterClase(string $clase): void
    {
        $this->claseActiva = $clase;
        $this->dispatch('puc-filter', clase: $clase);
    }

    public function clearFilter(): void
    {
        $this->claseActiva = null;
        $this->dispatch('puc-filter-clear');
    }

    public function getViewData(): array
    {
        $clases = [
            '1' => ['label' => 'Activo',     'color' => '#16a34a', 'bg' => '#f0fdf4', 'bdr' => '#bbf7d0', 'icon' => 'M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z'],
            '2' => ['label' => 'Pasivo',     'color' => '#E11D48', 'bg' => '#fff1f2', 'bdr' => '#fecdd3', 'icon' => 'M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
            '3' => ['label' => 'Patrimonio', 'color' => '#7c3aed', 'bg' => '#f5f3ff', 'bdr' => '#ddd6fe', 'icon' => 'M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21'],
            '4' => ['label' => 'Ingreso',    'color' => '#0284c7', 'bg' => '#f0f9ff', 'bdr' => '#bae6fd', 'icon' => 'M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941'],
            '5' => ['label' => 'Gasto',      'color' => '#d97706', 'bg' => '#fffbeb', 'bdr' => '#fde68a', 'icon' => 'M2.25 6L9 12.75l4.286-4.286a11.948 11.948 0 014.306 6.43l.776 2.898m0 0l3.182-5.511m-3.182 5.51l-5.511-3.181'],
            '6' => ['label' => 'Costo',      'color' => '#64748b', 'bg' => '#f8fafc', 'bdr' => '#e2e8f0', 'icon' => 'M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 2.625c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125m16.5 5.625c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125'],
        ];

        $total = AccountingAccount::count();

        foreach ($clases as $k => &$c) {
            $c['count'] = AccountingAccount::where('clase', $k)->count();
        }

        return ['clases' => $clases, 'total' => $total, 'claseActiva' => $this->claseActiva];
    }
}

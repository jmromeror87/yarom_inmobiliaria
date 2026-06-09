<?php

namespace App\Filament\Pages\Accounting;

use App\Models\AccountingAccount;
use App\Models\AccountingEntryLine;
use App\Models\AccountingPeriod;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class BalancePrueba extends Page
{
    protected string $view = 'filament.accounting.balance-prueba';
    protected static ?string $title = 'Balance de Prueba';
    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-scale';
    protected static string|\UnitEnum|null $navigationGroup = 'Contabilidad';
    protected static ?string $navigationLabel = 'Balance de Prueba';
    protected static ?int    $navigationSort  = 7;

    public ?int  $periodo_id         = null;
    public bool  $solo_con_movimiento = true;

    public function mount(): void
    {
        $this->periodo_id = AccountingPeriod::actual()?->id;
    }

    public function getBalanceData(): Collection
    {
        $lineas = AccountingEntryLine::selectRaw('account_id, SUM(debito) as total_debito, SUM(credito) as total_credito')
            ->whereHas('entry', function ($q) {
                $q->where('estado', 'contabilizado');
                if ($this->periodo_id) $q->where('period_id', $this->periodo_id);
            })
            ->groupBy('account_id')->get()->keyBy('account_id');

        return AccountingAccount::where('acepta_movimiento', true)
            ->where('estado', 'activo')->orderBy('codigo')->get()
            ->map(function ($cuenta) use ($lineas) {
                $mov = $lineas[$cuenta->id] ?? null;
                $deb = (float)($mov?->total_debito  ?? 0);
                $cre = (float)($mov?->total_credito ?? 0);
                return [
                    'codigo'    => $cuenta->codigo,
                    'nombre'    => $cuenta->nombre,
                    'naturaleza'=> $cuenta->naturaleza,
                    'debito'    => $deb,
                    'credito'   => $cre,
                    'saldo_deb' => max(0, $deb - $cre),
                    'saldo_cre' => max(0, $cre - $deb),
                ];
            })
            ->when($this->solo_con_movimiento, fn($c) => $c->filter(fn($r) => $r['debito'] > 0 || $r['credito'] > 0))
            ->values();
    }

    public function getPeriodos(): array
    {
        return AccountingPeriod::orderByDesc('anio')->orderByDesc('mes')
            ->get()->mapWithKeys(fn($p) => [$p->id => $p->nombre])->toArray();
    }
}

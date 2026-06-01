<?php

namespace App\Filament\Pages\Accounting;

use App\Models\AccountingAccount;
use App\Models\AccountingEntryLine;
use App\Models\AccountingPeriod;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class LibroMayor extends Page
{
    protected string $view = 'filament.accounting.libro-mayor';
    protected static ?string $title = 'Libro Mayor';
    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-table-cells';
    protected static string|\UnitEnum|null $navigationGroup = 'Contabilidad';
    protected static ?string $navigationLabel = 'Libro Mayor';
    protected static ?int    $navigationSort  = 5;

    public ?int $account_id = null;
    public ?int $periodo_id = null;

    public function mount(): void
    {
        $this->periodo_id = AccountingPeriod::actual()?->id;
    }

    public function getMovimientos(): Collection
    {
        if (!$this->account_id) return collect();

        return AccountingEntryLine::with(['entry', 'third'])
            ->where('account_id', $this->account_id)
            ->whereHas('entry', function ($q) {
                $q->where('estado', 'contabilizado');
                if ($this->periodo_id) $q->where('period_id', $this->periodo_id);
            })
            ->join('accounting_entries', 'accounting_entry_lines.entry_id', '=', 'accounting_entries.id')
            ->select('accounting_entry_lines.*')
            ->orderBy('accounting_entries.fecha')
            ->orderBy('accounting_entries.numero')
            ->get();
    }

    public function getCuentas(): array
    {
        return AccountingAccount::where('acepta_movimiento', true)
            ->where('estado', 'activo')->orderBy('codigo')
            ->get()->mapWithKeys(fn($a) => [$a->id => $a->codigo . ' — ' . $a->nombre])->toArray();
    }

    public function getPeriodos(): array
    {
        return AccountingPeriod::orderByDesc('anio')->orderByDesc('mes')
            ->get()->mapWithKeys(fn($p) => [$p->id => $p->nombre])->toArray();
    }

    public function getCuentaActual(): ?AccountingAccount
    {
        return $this->account_id ? AccountingAccount::find($this->account_id) : null;
    }
}

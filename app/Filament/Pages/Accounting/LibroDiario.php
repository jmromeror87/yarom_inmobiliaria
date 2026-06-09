<?php

namespace App\Filament\Pages\Accounting;

use App\Models\AccountingEntry;
use App\Models\AccountingPeriod;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class LibroDiario extends Page
{
    protected string $view = 'filament.accounting.libro-diario';
    protected static ?string $title = 'Libro Diario';
    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-book-open';
    protected static string|\UnitEnum|null $navigationGroup = 'Contabilidad';
    protected static ?string $navigationLabel = 'Libro Diario';
    protected static ?int    $navigationSort  = 5;

    public ?int $periodo_id = null;

    public function mount(): void
    {
        $this->periodo_id = AccountingPeriod::actual()?->id;
    }

    public function getEntries(): Collection
    {
        return AccountingEntry::with(['lines.account', 'lines.third', 'period', 'third'])
            ->where('estado', 'contabilizado')
            ->when($this->periodo_id, fn($q) => $q->where('period_id', $this->periodo_id))
            ->orderBy('fecha')->orderBy('numero')
            ->get();
    }

    public function getPeriodos(): array
    {
        return AccountingPeriod::orderByDesc('anio')->orderByDesc('mes')
            ->get()->mapWithKeys(fn($p) => [$p->id => $p->nombre])->toArray();
    }
}

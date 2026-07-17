<?php

namespace App\Filament\Pages\Accounting;

use App\Models\AccountingEntry;
use App\Models\AccountingPeriod;
use Filament\Pages\Page;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\WithPagination;

class LibroDiario extends Page
{
    use WithPagination;

    protected string $view = 'filament.accounting.libro-diario';
    protected static ?string $title = 'Libro Diario';
    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-book-open';
    protected static string|\UnitEnum|null $navigationGroup = 'Contabilidad';
    protected static ?string $navigationLabel = 'Libro Diario';
    protected static ?int    $navigationSort  = 5;
    protected string $paginationTheme = 'tailwind';

    public ?int $periodo_id = null;
    public int $perPage = 25;

    public function mount(): void
    {
        $this->periodo_id = AccountingPeriod::actual()?->id;
    }

    public function updatedPeriodoId(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    protected function baseQuery()
    {
        return AccountingEntry::query()
            ->where('estado', 'contabilizado')
            ->when($this->periodo_id, fn($q) => $q->where('period_id', $this->periodo_id));
    }

    public function getEntries(): LengthAwarePaginator
    {
        return $this->baseQuery()
            ->with(['lines.account', 'lines.third', 'period', 'third'])
            ->orderBy('fecha')->orderBy('numero')
            ->paginate($this->perPage);
    }

    public function getTotales(): array
    {
        return [
            'count'   => $this->baseQuery()->count(),
            'debitos' => $this->baseQuery()->sum('total_debitos'),
            'creditos'=> $this->baseQuery()->sum('total_creditos'),
        ];
    }

    public function getPeriodos(): array
    {
        return AccountingPeriod::orderByDesc('anio')->orderByDesc('mes')
            ->get()->mapWithKeys(fn($p) => [$p->id => $p->nombre])->toArray();
    }
}

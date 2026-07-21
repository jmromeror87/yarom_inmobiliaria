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
    protected static ?string $title = 'Libro Auxiliar';
    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-table-cells';
    protected static string|\UnitEnum|null $navigationGroup = 'Contabilidad';
    protected static ?string $navigationLabel = 'Libro Auxiliar';
    protected static ?int    $navigationSort  = 6;

    public ?int $account_id = null;
    public ?int $periodo_id = null;
    public ?string $fecha_inicio = null;
    public ?string $fecha_fin = null;

    public function mount(): void
    {
        $this->periodo_id = AccountingPeriod::actual()?->id;
    }

    public function updatedFechaInicio(): void
    {
        if ($this->fecha_inicio || $this->fecha_fin) $this->periodo_id = null;
    }

    public function updatedFechaFin(): void
    {
        if ($this->fecha_inicio || $this->fecha_fin) $this->periodo_id = null;
    }

    public function updatedPeriodoId(): void
    {
        if ($this->periodo_id) {
            $this->fecha_inicio = null;
            $this->fecha_fin = null;
        }
    }

    public function limpiarFechas(): void
    {
        $this->fecha_inicio = null;
        $this->fecha_fin = null;
    }

    public function getMovimientos(): Collection
    {
        if (!$this->account_id) return collect();

        return AccountingEntryLine::with(['entry', 'third'])
            ->where('account_id', $this->account_id)
            ->whereHas('entry', function ($q) {
                $q->where('estado', 'contabilizado');
                if ($this->fecha_inicio || $this->fecha_fin) {
                    if ($this->fecha_inicio) $q->whereDate('fecha', '>=', $this->fecha_inicio);
                    if ($this->fecha_fin) $q->whereDate('fecha', '<=', $this->fecha_fin);
                } elseif ($this->periodo_id) {
                    $q->where('period_id', $this->periodo_id);
                }
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

    public function getSaldoInicial(): float
    {
        if (!$this->account_id) return 0;

        $cuenta = $this->getCuentaActual();
        if (!$cuenta) return 0;

        $fechaCorte = null;
        if ($this->fecha_inicio) {
            $fechaCorte = $this->fecha_inicio;
        } elseif ($this->periodo_id) {
            $periodo = AccountingPeriod::find($this->periodo_id);
            if ($periodo) $fechaCorte = sprintf('%04d-%02d-01', $periodo->anio, $periodo->mes);
        }

        if (!$fechaCorte) return 0;

        $totales = AccountingEntryLine::where('account_id', $this->account_id)
            ->whereHas('entry', function ($q) use ($fechaCorte) {
                $q->where('estado', 'contabilizado')->whereDate('fecha', '<', $fechaCorte);
            })
            ->selectRaw('SUM(debito) d, SUM(credito) c')
            ->first();

        $deb = (float) ($totales->d ?? 0);
        $cre = (float) ($totales->c ?? 0);

        return $cuenta->naturaleza === 'debito' ? ($deb - $cre) : ($cre - $deb);
    }
}

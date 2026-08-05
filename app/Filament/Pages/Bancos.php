<?php

namespace App\Filament\Pages;

use App\Models\AccountingAccount;
use App\Models\AccountingEntryLine;
use App\Models\AccountingPeriod;
use App\Models\Bank;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class Bancos extends Page
{
    protected string $view = 'filament.pages.bancos';
    protected static ?string $title = 'Bancos y Caja';
    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-banknotes';
    protected static string|\UnitEnum|null $navigationGroup = 'Gerencia';
    protected static ?string $navigationLabel = 'Bancos';
    protected static ?int    $navigationSort  = 2;

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['gerente', 'super_admin', 'admin']) ?? false;
    }

    public ?int $bank_id = null;
    public ?int $periodo_id = null;
    public ?string $fecha_inicio = null;
    public ?string $fecha_fin = null;

    public function mount(): void
    {
        $this->periodo_id = AccountingPeriod::actual()?->id;
        $this->bank_id = Bank::where('is_active', true)->orderByRaw("tipo_cuenta = 'caja' desc")->orderBy('nombre')->value('id');
    }

    public function setBank(int $bankId): void
    {
        $this->bank_id = $bankId;
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

    public function getBancos(): Collection
    {
        return Bank::where('is_active', true)
            ->orderByRaw("tipo_cuenta = 'caja' desc")
            ->orderBy('nombre')
            ->get();
    }

    public function getBancoActual(): ?Bank
    {
        return $this->bank_id ? Bank::find($this->bank_id) : null;
    }

    public function getCuentaActual(): ?AccountingAccount
    {
        return $this->getBancoActual()?->accountingAccount;
    }

    public function getMovimientos(): Collection
    {
        $cuenta = $this->getCuentaActual();
        if (!$cuenta) return collect();

        return AccountingEntryLine::with(['entry.lines.account', 'entry.lines.third', 'third'])
            ->where('account_id', $cuenta->id)
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

    public function getPeriodos(): array
    {
        return AccountingPeriod::orderByDesc('anio')->orderByDesc('mes')
            ->get()->mapWithKeys(fn ($p) => [$p->id => $p->nombre])->toArray();
    }

    public function getSaldoInicial(): float
    {
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

        $totales = AccountingEntryLine::where('account_id', $cuenta->id)
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

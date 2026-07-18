<?php

namespace App\Filament\Resources\Thirds\Pages;

use App\Filament\Resources\Thirds\ThirdResource;
use App\Models\AccountingEntryLine;
use App\Models\CuentaPorCobrar;
use App\Models\CuentaPorPagarPropietario;
use App\Models\OwnerLiquidation;
use App\Models\RentBill;
use App\Models\Third;
use Filament\Actions\Action;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

class ThirdExpediente extends Page
{
    use WithPagination;

    protected static string $resource = ThirdResource::class;
    protected string $view = 'filament.thirds.expediente';

    public Third $record;

    #[Url]
    public string $tab = 'resumen';

    public ?string $mesDetalleMes = null;
    public ?string $mesDetalleTipo = null;

    public function mount(Third $record): void
    {
        $this->record = $record->load(['municipio.departamento', 'asesor']);
    }

    public function getTitle(): string
    {
        return 'Expediente — ' . $this->record->nombre_completo;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('editar')
                ->label('Editar tercero')
                ->icon('heroicon-o-pencil')
                ->url(ThirdResource::getUrl('edit', ['record' => $this->record])),
        ];
    }

    public function setTab(string $tab): void
    {
        $this->tab = $tab;
    }

    public function verDetalleMes(string $mes, string $tipo): void
    {
        $this->mesDetalleMes = $mes;
        $this->mesDetalleTipo = $tipo;
        $this->dispatch('open-modal', id: 'modal-detalle-mes');
    }

    /**
     * Detalle día a día de un mes puntual (siempre acotado a un mes, nunca
     * a todo el historial) — aquí sí traemos las líneas completas del asiento
     * para saber la fecha exacta, la cuenta destino (proxy de la forma de
     * pago: caja = efectivo, banco = transferencia) y la referencia.
     */
    public function getDetalleMesLineasProperty(): \Illuminate\Support\Collection
    {
        if (!$this->mesDetalleMes) {
            return collect();
        }

        $prefijo = $this->mesDetalleTipo === 'cxp' ? '23354' : '1305';

        $entryIds = AccountingEntryLine::where('accounting_entry_lines.third_id', $this->record->id)
            ->whereHas('account', fn ($q) => $q->where('codigo', 'like', "{$prefijo}%"))
            ->whereHas('entry', function ($q) {
                $q->where('estado', 'contabilizado')
                    ->whereRaw("DATE_FORMAT(fecha, '%Y-%m') = ?", [$this->mesDetalleMes]);
            })
            ->pluck('entry_id')
            ->unique();

        $lineasCartera = AccountingEntryLine::whereIn('entry_id', $entryIds)
            ->whereHas('account', fn ($q) => $q->where('codigo', 'like', "{$prefijo}%"))
            ->with(['entry', 'account'])
            ->get();

        // Para cada linea de cartera, buscar en el mismo asiento la contrapartida
        // en una cuenta de disponible (clase 11) para inferir la forma de pago.
        $lineasDisponiblePorEntry = AccountingEntryLine::whereIn('entry_id', $entryIds)
            ->whereHas('account', fn ($q) => $q->where('codigo', 'like', '11%'))
            ->with('account')
            ->get()
            ->groupBy('entry_id');

        return $lineasCartera->map(function ($linea) use ($lineasDisponiblePorEntry) {
            $disponible = $lineasDisponiblePorEntry->get($linea->entry_id)?->first();
            $formaPago = match (true) {
                !$disponible => null,
                str_contains(strtolower($disponible->account?->nombre ?? ''), 'caja') => 'Efectivo (Caja)',
                default => 'Transferencia — ' . $disponible->account?->nombre,
            };

            return [
                'fecha' => $linea->entry?->fecha,
                'comprobante' => $linea->entry?->numero,
                'concepto' => $linea->descripcion,
                'forma_pago' => $formaPago,
                'debito' => $linea->debito,
                'credito' => $linea->credito,
            ];
        })->sortBy('fecha')->values();
    }

    // ── Conteos e indicadores (agregados vía SQL, nunca cargando colecciones completas) ──

    public function getKpisProperty(): array
    {
        $r = $this->record;

        $facturado = RentBill::where('arrendatario_id', $r->id)->sum('total_factura');
        $pagado    = RentBill::where('arrendatario_id', $r->id)->sum('total_pagado');
        $pendienteFacturas = RentBill::where('arrendatario_id', $r->id)->sum('saldo_pendiente');
        $pendienteCartera  = $r->cuentasPorCobrar()->whereIn('estado', ['pendiente', 'parcial'])->sum('saldo');

        $liquidado = OwnerLiquidation::where('propietario_id', $r->id)->sum('canon_cobrado');
        $girado    = OwnerLiquidation::where('propietario_id', $r->id)->where('estado', 'pagada')->sum('total_giro');
        $comision  = OwnerLiquidation::where('propietario_id', $r->id)->sum('comision_valor');
        $porGirarLiq = OwnerLiquidation::where('propietario_id', $r->id)->whereIn('estado', ['pendiente', 'aprobada'])->sum('total_giro');
        $porGirarCxp = $r->cuentasPorPagar()->where('estado', '!=', 'pagado')->sum('saldo');

        return [
            'facturas_count'      => RentBill::where('arrendatario_id', $r->id)->count(),
            'liquidaciones_count' => OwnerLiquidation::where('propietario_id', $r->id)->count(),
            'contratos_count'     => $r->rentalContracts()->count() + $r->properties()->count(),
            'total_facturado'     => $facturado,
            'total_pagado'        => $pagado,
            'total_pendiente'     => $pendienteFacturas + $pendienteCartera,
            'total_liquidado'     => $liquidado,
            'total_girado'        => $girado,
            'total_comision'      => $comision,
            'total_por_girar_hoy' => $porGirarLiq + $porGirarCxp,
        ];
    }

    public function getContratosProperty()
    {
        return $this->record->rentalContracts()->with('property')->orderByDesc('fecha_inicio')->get();
    }

    public function getPropiedadesProperty()
    {
        return $this->record->properties()->get();
    }

    public function getCarteraHeredadaProperty()
    {
        return $this->record->cuentasPorCobrar()->where('tipo', 'saldo_inicial_siinmob')->get();
    }

    public function getCxpHeredadaProperty()
    {
        return $this->record->cuentasPorPagar()->where('tipo', 'saldo_inicial_siinmob')->get();
    }

    public function getFacturasProperty()
    {
        return RentBill::where('arrendatario_id', $this->record->id)
            ->with(['rentalContract.property', 'payments'])
            ->orderByDesc('periodo_inicio')
            ->paginate(15, ['*'], 'facturas_page');
    }

    public function getLiquidacionesProperty()
    {
        return OwnerLiquidation::where('propietario_id', $this->record->id)
            ->with('property')
            ->orderByDesc('anio')->orderByDesc('mes')
            ->paginate(15, ['*'], 'liquidaciones_page');
    }

    public function getMovimientosProperty()
    {
        return AccountingEntryLine::where('accounting_entry_lines.third_id', $this->record->id)
            ->whereHas('entry', fn ($q) => $q->where('estado', 'contabilizado'))
            ->with(['entry', 'account'])
            ->join('accounting_entries as e', 'e.id', '=', 'accounting_entry_lines.entry_id')
            ->orderByDesc('e.fecha')
            ->select('accounting_entry_lines.*')
            ->paginate(25, ['*'], 'movimientos_page');
    }

    /** Histórico mensual agregado por SQL (nunca carga todas las líneas a memoria) */
    public function getHistoricoMensualCarteraProperty()
    {
        return AccountingEntryLine::where('accounting_entry_lines.third_id', $this->record->id)
            ->whereHas('account', fn ($q) => $q->where('codigo', 'like', '1305%'))
            ->whereHas('entry', fn ($q) => $q->where('estado', 'contabilizado'))
            ->join('accounting_entries as e', 'e.id', '=', 'accounting_entry_lines.entry_id')
            ->selectRaw("DATE_FORMAT(e.fecha, '%Y-%m') as mes, SUM(debito) as cargo, SUM(credito) as pago")
            ->groupBy('mes')
            ->orderByDesc('mes')
            ->paginate(12, ['*'], 'historico_cartera_page');
    }

    public function getHistoricoMensualCxpProperty()
    {
        return AccountingEntryLine::where('accounting_entry_lines.third_id', $this->record->id)
            ->whereHas('account', fn ($q) => $q->where('codigo', 'like', '23354%'))
            ->whereHas('entry', fn ($q) => $q->where('estado', 'contabilizado'))
            ->join('accounting_entries as e', 'e.id', '=', 'accounting_entry_lines.entry_id')
            ->selectRaw("DATE_FORMAT(e.fecha, '%Y-%m') as mes, SUM(credito) as liquidado, SUM(debito) as girado")
            ->groupBy('mes')
            ->orderByDesc('mes')
            ->paginate(12, ['*'], 'historico_cxp_page');
    }
}

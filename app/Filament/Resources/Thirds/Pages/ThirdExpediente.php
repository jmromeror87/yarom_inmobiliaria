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

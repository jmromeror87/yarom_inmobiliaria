<?php

namespace App\Filament\Pages\Accounting;

use App\Models\AccountingAccount;
use App\Models\Bank;
use App\Models\CuentaPorCobrar;
use App\Models\CuentaPorPagarPropietario;
use App\Models\OwnerLiquidation;
use App\Models\RentBill;
use App\Models\RentPayment;
use App\Models\Third;
use App\Services\ContabilidadService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

abstract class ComprobanteRapidoBase extends Page
{
    protected string $view = 'filament.pages.accounting.comprobante-rapido';

    abstract public function tipo(): string; // 'CI' | 'CE'

    public ?int $bank_id = null;
    public ?int $third_id = null;
    public string $aplicacion = 'otro';
    public ?string $obligacion = null; // "modelo:id"
    public ?float $monto = null;
    public string $fecha = '';
    public ?string $concepto = null;
    public ?string $referencia = null;
    public ?int $account_id = null;

    public function mount(): void
    {
        $this->fecha = now()->toDateString();
    }

    public function getEsIngresoProperty(): bool
    {
        return $this->tipo() === 'CI';
    }

    public function getOpcionesAplicacionProperty(): array
    {
        return $this->esIngreso
            ? [
                'factura_pendiente' => '🧾 Cancelar factura pendiente (sistema nuevo)',
                'cxc_heredada'      => '🗓️ Abonar cartera heredada de Siinmob',
                'otro'              => '💰 Ingreso vario / otro concepto',
            ]
            : [
                'liquidacion_propietario' => '🏠 Girar liquidación pendiente a propietario',
                'cxp_heredada'            => '🗓️ Pagar cuenta heredada de Siinmob a propietario',
                'otro'                    => '📤 Gasto vario / otro concepto',
            ];
    }

    public function getBancosProperty()
    {
        return Bank::where('is_active', true)->orderBy('id')->get();
    }

    public function getCuentasManualesProperty()
    {
        return AccountingAccount::where('acepta_movimiento', true)->where('estado', 'activo')
            ->orderBy('codigo')->get();
    }

    public function updatedThirdId(): void
    {
        $this->obligacion = null;
        $this->monto = null;
    }

    public function updatedAplicacion(): void
    {
        $this->obligacion = null;
        $this->monto = null;
    }

    public function updatedObligacion(): void
    {
        if (!$this->obligacion) {
            return;
        }
        [$modelo, $id] = explode(':', $this->obligacion);
        $this->monto = match ($modelo) {
            'factura'     => (float) RentBill::find($id)?->saldo_pendiente,
            'cxc'         => (float) CuentaPorCobrar::find($id)?->saldo,
            'liquidacion' => (float) OwnerLiquidation::find($id)?->total_giro,
            'cxp'         => (float) CuentaPorPagarPropietario::find($id)?->saldo,
            default       => null,
        };
    }

    public function getPendientesProperty()
    {
        if (!$this->third_id) {
            return collect();
        }

        return match ($this->aplicacion) {
            'factura_pendiente' => RentBill::where('arrendatario_id', $this->third_id)
                ->whereIn('estado', ['pendiente', 'parcial', 'en_mora', 'vencida'])
                ->orderBy('fecha_limite_pago')->get()
                ->map(fn ($b) => ['key' => "factura:{$b->id}", 'label' => "{$b->numero} — vence " . $b->fecha_limite_pago->format('d/m/Y') . " — saldo \$" . number_format($b->saldo_pendiente, 0, ',', '.')]),

            'cxc_heredada' => CuentaPorCobrar::where('third_id', $this->third_id)
                ->whereIn('estado', ['pendiente', 'parcial'])->get()
                ->map(fn ($c) => ['key' => "cxc:{$c->id}", 'label' => "{$c->numero} — {$c->concepto} — saldo \$" . number_format($c->saldo, 0, ',', '.')]),

            'liquidacion_propietario' => OwnerLiquidation::where('propietario_id', $this->third_id)
                ->whereIn('estado', ['pendiente', 'aprobada'])->get()
                ->map(fn ($l) => ['key' => "liquidacion:{$l->id}", 'label' => "{$l->numero} — " . \Carbon\Carbon::create($l->anio, $l->mes, 1)->translatedFormat('F Y') . " — \$" . number_format($l->total_giro, 0, ',', '.')]),

            'cxp_heredada' => CuentaPorPagarPropietario::where('third_id', $this->third_id)
                ->where('estado', '!=', 'pagado')->get()
                ->map(fn ($c) => ['key' => "cxp:{$c->id}", 'label' => "{$c->numero} — {$c->concepto} — saldo \$" . number_format($c->saldo, 0, ',', '.')]),

            default => collect(),
        };
    }

    public function guardar(): void
    {
        $this->validate([
            'bank_id' => 'required',
            'monto' => 'required|numeric|min:1',
            'fecha' => 'required|date',
        ]);

        $bank = Bank::find($this->bank_id);
        $formaPago = $bank->tipo_cuenta === 'caja' ? 'efectivo' : 'transferencia';

        try {
            DB::transaction(function () use ($bank, $formaPago) {
                match ($this->aplicacion) {
                    'factura_pendiente' => $this->aplicarFactura($bank, $formaPago),
                    'cxc_heredada'      => $this->aplicarCxcHeredada($bank, $formaPago),
                    'liquidacion_propietario' => $this->aplicarLiquidacion($bank, $formaPago),
                    'cxp_heredada'      => $this->aplicarCxpHeredada($bank),
                    default             => $this->aplicarOtro($bank),
                };
            });

            Notification::make()->title('Comprobante registrado y contabilizado')->success()->send();
            $this->redirect(\App\Filament\Resources\Accounting\AccountingEntryResource::getUrl('index'));
        } catch (\Throwable $e) {
            Notification::make()->title('No se pudo registrar: ' . $e->getMessage())->danger()->send();
        }
    }

    private function idObligacion(): ?int
    {
        if (!$this->obligacion) {
            return null;
        }
        return (int) explode(':', $this->obligacion)[1];
    }

    private function aplicarFactura(Bank $bank, string $formaPago): void
    {
        $bill = RentBill::findOrFail($this->idObligacion());

        RentPayment::create([
            'rent_bill_id'       => $bill->id,
            'rental_contract_id' => $bill->rental_contract_id,
            'arrendatario_id'    => $bill->arrendatario_id,
            'registrado_por'     => Auth::id(),
            'valor_canon'        => max(0, $this->monto - $bill->mora_acumulada),
            'valor_mora'         => $bill->mora_acumulada,
            'valor_administracion' => $bill->cuota_administracion,
            'total_pagado'       => $this->monto,
            'forma_pago'         => $formaPago,
            'fecha_pago'         => $this->fecha,
            'referencia_pago'    => $this->referencia,
            'bank_id'            => $bank->tipo_cuenta === 'caja' ? null : $bank->id,
            'notas'              => $this->concepto,
        ]);
    }

    private function aplicarCxcHeredada(Bank $bank, string $formaPago): void
    {
        $cpc = CuentaPorCobrar::findOrFail($this->idObligacion());
        $cpc->registrarAbono($this->monto, $formaPago, $this->referencia ?? '', Auth::id(), $this->concepto ?? '');
        ContabilidadService::generarParaAbonoCarteraHeredada($cpc, $this->monto, $bank, $this->fecha);
    }

    private function aplicarLiquidacion(Bank $bank, string $formaPago): void
    {
        $liq = OwnerLiquidation::findOrFail($this->idObligacion());

        if (abs($this->monto - (float) $liq->total_giro) > 1) {
            throw new \RuntimeException('El giro a propietario debe ser por el valor total de la liquidación ($' . number_format($liq->total_giro, 0, ',', '.') . ')');
        }

        $liq->update([
            'estado'          => 'pagada',
            'fecha_giro'      => $this->fecha,
            'forma_giro'      => $formaPago,
            'banco_giro_id'   => $bank->id,
            'referencia_giro' => $this->referencia,
        ]);
    }

    private function aplicarCxpHeredada(Bank $bank): void
    {
        $cpp = CuentaPorPagarPropietario::findOrFail($this->idObligacion());
        $cpp->registrarPago($this->monto, $this->referencia ?? '', $this->concepto ?? '');
        ContabilidadService::generarParaPagoCxpHeredada($cpp, $this->monto, $bank, $this->fecha);
    }

    private function aplicarOtro(Bank $bank): void
    {
        $this->validate([
            'account_id' => 'required',
            'concepto' => 'required',
        ]);

        ContabilidadService::generarComprobanteRapido(
            tipo: $this->tipo(),
            bank: $bank,
            cuentaContrariaId: $this->account_id,
            monto: $this->monto,
            concepto: $this->concepto,
            thirdId: $this->third_id,
            fecha: $this->fecha,
            referencia: $this->referencia,
        );
    }

    public string $tercero_search = '';

    public function getTercerosProperty()
    {
        if (mb_strlen($this->tercero_search) < 2) {
            return collect();
        }
        return Third::query()
            ->where('nombre_completo', 'like', "%{$this->tercero_search}%")
            ->orWhere('numero_documento', 'like', "%{$this->tercero_search}%")
            ->orderBy('nombre_completo')->limit(20)->get();
    }

    public function seleccionarTercero(int $id): void
    {
        $this->third_id = $id;
        $this->tercero_search = Third::find($id)?->nombre_completo ?? '';
    }
}

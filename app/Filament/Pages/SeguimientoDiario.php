<?php

namespace App\Filament\Pages;

use App\Models\AccountingEntry;
use App\Models\DailyReviewCheck;
use App\Models\OwnerLiquidation;
use App\Models\RentBill;
use App\Models\RentPayment;
use App\Services\SeguimientoDiarioService;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class SeguimientoDiario extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.pages.seguimiento-diario';

    public static function getNavigationLabel(): string { return 'Seguimiento Diario'; }
    public static function getNavigationGroup(): ?string { return 'Cobros'; }
    public function getTitle(): string { return 'Seguimiento Diario — Gerencia'; }

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['gerente', 'super_admin', 'admin', 'asesor']) ?? false;
    }

    public string $tab = 'inquilinos';
    public string $vista = 'dia'; // 'dia' | 'mes'
    public string $fecha;
    public string $mes; // formato Y-m
    public string $busqueda = '';

    public function mount(): void
    {
        $this->fecha = now()->toDateString();
        $this->mes   = now()->format('Y-m');
        $this->sincronizarSiHoy();
    }

    public function setTab(string $tab): void
    {
        $this->tab = $tab;
    }

    public function setVista(string $vista): void
    {
        $this->vista = $vista;
    }

    public function diaAnterior(): void
    {
        $this->fecha = Carbon::parse($this->fecha)->subDay()->toDateString();
        $this->sincronizarSiHoy();
    }

    public function diaSiguiente(): void
    {
        $siguiente = Carbon::parse($this->fecha)->addDay();
        if ($siguiente->gt(now())) return;
        $this->fecha = $siguiente->toDateString();
        $this->sincronizarSiHoy();
    }

    public function irAHoy(): void
    {
        $this->fecha = now()->toDateString();
        $this->sincronizarSiHoy();
    }

    public function updatedFecha(): void
    {
        if (Carbon::parse($this->fecha)->gt(now())) {
            $this->fecha = now()->toDateString();
        }
        $this->sincronizarSiHoy();
    }

    public function esHoy(): bool
    {
        return $this->fecha === now()->toDateString();
    }

    public function mesAnterior(): void
    {
        $this->mes = Carbon::parse($this->mes . '-01')->subMonthNoOverflow()->format('Y-m');
    }

    public function mesSiguiente(): void
    {
        $siguiente = Carbon::parse($this->mes . '-01')->addMonthNoOverflow();
        if ($siguiente->gt(now())) return;
        $this->mes = $siguiente->format('Y-m');
    }

    public function irAMesActual(): void
    {
        $this->mes = now()->format('Y-m');
    }

    public function updatedMes(): void
    {
        if (Carbon::parse($this->mes . '-01')->gt(now())) {
            $this->mes = now()->format('Y-m');
        }
    }

    public function esMesActual(): bool
    {
        return $this->mes === now()->format('Y-m');
    }

    private function rangoMes(): array
    {
        $inicio = Carbon::parse($this->mes . '-01')->startOfMonth();
        $fin    = $inicio->copy()->endOfMonth();
        if ($fin->gt(now())) $fin = now();

        return [$inicio->toDateString(), $fin->toDateString()];
    }

    private function sincronizarSiHoy(): void
    {
        if (!$this->esHoy()) return;

        SeguimientoDiarioService::sincronizar('inquilino', SeguimientoDiarioService::calcularInquilinos(), $this->fecha);
        SeguimientoDiarioService::sincronizar('propietario', SeguimientoDiarioService::calcularPropietarios(), $this->fecha);
    }

    private const TIPOS_COMPROBANTE = ['comprobante_ingreso', 'comprobante_egreso'];
    private const TIPOS_MES         = ['inquilino_mes', 'propietario_mes'];

    public function toggleRevisado(string $tipo, int $id): void
    {
        if (in_array($tipo, self::TIPOS_COMPROBANTE, true)) {
            // El comprobante se marca revisado con SU PROPIA fecha contable,
            // no la fecha de la vista donde se hizo clic — así "revisado" es
            // un estado del comprobante, válido tanto en día como en mes.
            $fecha   = AccountingEntry::whereKey($id)->value('fecha')?->toDateString() ?? $this->fecha;
            $columna = 'entry_id';
        } elseif (in_array($tipo, self::TIPOS_MES, true)) {
            $fecha   = $this->mes . '-01';
            $columna = 'third_id';
        } else {
            $fecha   = $this->fecha;
            $columna = 'third_id';
        }

        $yaRevisado = DailyReviewCheck::where('fecha', $fecha)
            ->where('tipo', $tipo)->where($columna, $id)
            ->where('revisado', true)->exists();

        if ($yaRevisado) {
            DailyReviewCheck::where('fecha', $fecha)->where('tipo', $tipo)->where($columna, $id)
                ->update(['revisado' => false, 'revisado_por_id' => null, 'revisado_en' => null]);
        } else {
            DailyReviewCheck::updateOrCreate(
                ['fecha' => $fecha, 'tipo' => $tipo, $columna => $id],
                ['revisado' => true, 'revisado_por_id' => Auth::id(), 'revisado_en' => now()]
            );
        }
    }

    public function updatedTab(): void
    {
        $this->busqueda = '';
    }

    private function filtrarPorBusqueda(array $filas): array
    {
        $q = trim(mb_strtolower($this->busqueda));
        if ($q === '') return $filas;

        return array_values(array_filter($filas, function ($f) use ($q) {
            return str_contains(mb_strtolower($f['nombre'] ?? ''), $q)
                || str_contains(mb_strtolower((string) ($f['documento'] ?? '')), $q);
        }));
    }

    public function getInquilinosProperty(): array
    {
        $esMes = $this->vista === 'mes';

        $filas = $esMes
            ? SeguimientoDiarioService::adjuntarRevisadoMes(SeguimientoDiarioService::calcularInquilinos(), 'inquilino', $this->mes)
            : SeguimientoDiarioService::cargarSnapshot('inquilino', $this->fecha);

        $filas = $this->filtrarPorBusqueda($filas);

        // Estado ACTUAL (en vivo) de cada factura del snapshot — el
        // snapshot congela mora/saldo del día, pero si esa factura puntual
        // ya se pagó después, hay que reflejarlo aquí.
        $numeros = collect($filas)->flatMap(fn ($f) => array_column($f['inmuebles'] ?? [], 'numero'))->unique();
        $billsPorNumero = RentBill::whereIn('numero', $numeros)->get()->keyBy('numero');

        // En vista de mes comparamos pagos desde el 1° del mes; en vista de
        // día, desde el día puntual de la planilla.
        $desde = $esMes ? ($this->mes . '-01') : $this->fecha;

        return array_map(function ($fila) use ($billsPorNumero, $desde) {
            // Último pago del inquilino DESDE la fecha base en adelante —
            // así si lo revisaron hoy y pagó anoche, o si se mira un día/mes
            // pasado y ya pagó después, se ve reflejado en la misma tarjeta.
            $pago = RentPayment::where('arrendatario_id', $fila['id'])
                ->where('fecha_pago', '>=', $desde)
                ->orderByDesc('fecha_pago')
                ->first();

            $fila['ya_pago'] = $pago ? [
                'monto' => (float) $pago->total_pagado,
                'fecha' => $pago->fecha_pago,
            ] : null;

            // Si está "al día" (sin nada vencido), igual mostrar el período
            // actual que le corresponde pagar y cuándo fue su último pago,
            // para no dejar la tarjeta vacía sin contexto.
            if (empty($fila['inmuebles'])) {
                $billActual = RentBill::where('arrendatario_id', $fila['id'])
                    ->whereNotIn('estado', ['anulada'])
                    ->orderByDesc('periodo_inicio')
                    ->first();

                $fila['periodo_actual'] = $billActual ? [
                    'inicio'         => $billActual->periodo_inicio->format('d/m/Y'),
                    'fin'            => $billActual->periodo_fin->format('d/m/Y'),
                    'fecha_limite'   => $billActual->fecha_limite_pago->format('d/m/Y'),
                    'estado'         => $billActual->estado,
                ] : null;

                $ultimoPago = RentPayment::where('arrendatario_id', $fila['id'])
                    ->orderByDesc('fecha_pago')
                    ->first();

                $fila['ultimo_pago_historico'] = $ultimoPago ? [
                    'monto' => (float) $ultimoPago->total_pagado,
                    'fecha' => $ultimoPago->fecha_pago,
                ] : null;
            }

            $fila['inmuebles'] = array_map(function ($im) use ($billsPorNumero) {
                $bill = $billsPorNumero->get($im['numero']);
                $im['periodo_inicio'] = $bill?->periodo_inicio?->format('d/m/Y');
                $im['periodo_fin']    = $bill?->periodo_fin?->format('d/m/Y');
                $im['pagada']         = $bill?->estado === 'pagada';
                $im['fecha_limite']   = $bill?->fecha_limite_pago?->format('d/m/Y');

                if ($bill) {
                    $finGracia = $bill->fecha_limite_pago->copy()->addDays($bill->dias_gracia)->endOfDay();
                    $im['en_gracia'] = !$im['pagada'] && now()->lte($finGracia);
                } else {
                    $im['en_gracia'] = null;
                }

                return $im;
            }, $fila['inmuebles'] ?? []);

            return $fila;
        }, $filas);
    }

    public function getPropietariosProperty(): array
    {
        $esMes = $this->vista === 'mes';

        $filas = $esMes
            ? SeguimientoDiarioService::adjuntarRevisadoMes(SeguimientoDiarioService::calcularPropietarios(), 'propietario', $this->mes)
            : SeguimientoDiarioService::cargarSnapshot('propietario', $this->fecha);

        $filas = $this->filtrarPorBusqueda($filas);

        $desde = $esMes ? ($this->mes . '-01') : $this->fecha;

        return array_map(function ($fila) use ($desde) {
            $giro = OwnerLiquidation::where('propietario_id', $fila['id'])
                ->where('estado', 'pagada')
                ->where('fecha_giro', '>=', $desde)
                ->orderByDesc('fecha_giro')
                ->first();

            $fila['ya_giro'] = $giro ? [
                'monto' => (float) $giro->total_giro,
                'fecha' => $giro->fecha_giro,
            ] : null;

            // Si está "sin giros pendientes", igual mostrar el período
            // actual esperado y cuándo fue el último giro pagado.
            if (empty($fila['inmuebles'])) {
                $liqActual = OwnerLiquidation::where('propietario_id', $fila['id'])
                    ->orderByDesc('periodo_inicio')
                    ->first();

                $fila['periodo_actual'] = $liqActual ? [
                    'inicio' => Carbon::parse($liqActual->periodo_inicio)->format('d/m/Y'),
                    'fin'    => Carbon::parse($liqActual->periodo_fin)->format('d/m/Y'),
                    'estado' => $liqActual->estado,
                ] : null;

                $ultimoGiro = OwnerLiquidation::where('propietario_id', $fila['id'])
                    ->where('estado', 'pagada')
                    ->orderByDesc('fecha_giro')
                    ->first();

                $fila['ultimo_giro_historico'] = $ultimoGiro ? [
                    'monto' => (float) $ultimoGiro->total_giro,
                    'fecha' => $ultimoGiro->fecha_giro,
                ] : null;
            }

            return $fila;
        }, $filas);
    }

    private function filtrarComprobantesPorBusqueda(array $filas): array
    {
        $q = trim(mb_strtolower($this->busqueda));
        if ($q === '') return $filas;

        return array_values(array_filter($filas, function ($f) use ($q) {
            return str_contains(mb_strtolower($f['numero'] ?? ''), $q)
                || str_contains(mb_strtolower($f['descripcion'] ?? ''), $q)
                || str_contains(mb_strtolower($f['tercero'] ?? ''), $q);
        }));
    }

    public function getIngresosProperty(): array
    {
        if ($this->vista === 'mes') {
            [$desde, $hasta] = $this->rangoMes();
            return $this->filtrarComprobantesPorBusqueda(
                SeguimientoDiarioService::cargarMovimientosCaja('ingreso', $desde, $hasta)
            );
        }

        return $this->filtrarComprobantesPorBusqueda(
            SeguimientoDiarioService::cargarMovimientosCaja('ingreso', $this->fecha)
        );
    }

    public function getEgresosProperty(): array
    {
        if ($this->vista === 'mes') {
            [$desde, $hasta] = $this->rangoMes();
            return $this->filtrarComprobantesPorBusqueda(
                SeguimientoDiarioService::cargarMovimientosCaja('egreso', $desde, $hasta)
            );
        }

        return $this->filtrarComprobantesPorBusqueda(
            SeguimientoDiarioService::cargarMovimientosCaja('egreso', $this->fecha)
        );
    }

    public function getFechaLabelProperty(): string
    {
        $f = Carbon::parse($this->fecha);
        return ucfirst($f->translatedFormat('l d \\d\\e F \\d\\e Y'));
    }

    public function getMesLabelProperty(): string
    {
        return ucfirst(Carbon::parse($this->mes . '-01')->translatedFormat('F \\d\\e Y'));
    }
}

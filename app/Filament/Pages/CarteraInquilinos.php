<?php

namespace App\Filament\Pages;

use App\Models\RentBill;
use Filament\Pages\Page;

class CarteraInquilinos extends Page
{
    protected string $view = 'filament.pages.cartera-inquilinos';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-exclamation-triangle';
    protected static ?int $navigationSort = 4;

    public static function getNavigationLabel(): string { return 'Cartera por Inquilino'; }
    public static function getNavigationGroup(): ?string { return 'Cobros'; }
    public function getTitle(): string { return 'Cartera por Inquilino'; }

    public string $busqueda = '';
    public string $ordenarPor = 'total';
    public array $filas = [];

    public function mount(): void
    {
        $this->cargar();
    }

    public function updatedBusqueda(): void { $this->cargar(); }
    public function updatedOrdenarPor(): void { $this->cargar(); }

    private function bucket(int $dias): string
    {
        if ($dias <= 0) return 'al_dia';
        if ($dias <= 30) return 'b_0_30';
        if ($dias <= 60) return 'b_31_60';
        if ($dias <= 90) return 'b_61_90';
        return 'b_90_mas';
    }

    public function cargar(): void
    {
        $bills = RentBill::whereIn('estado', ['pendiente', 'parcial', 'en_mora', 'vencida'])
            ->with(['arrendatario', 'property'])
            ->get();

        $porInquilino = [];

        foreach ($bills as $bill) {
            $arr = $bill->arrendatario;
            if (!$arr) continue;

            if ($this->busqueda !== '' && !str_contains(mb_strtolower($arr->nombre_completo ?? ''), mb_strtolower($this->busqueda))) {
                continue;
            }

            $id = $arr->id;
            if (!isset($porInquilino[$id])) {
                $porInquilino[$id] = [
                    'id' => $id,
                    'nombre' => $arr->nombre_completo,
                    'celular' => $arr->celular,
                    'cantidad' => 0,
                    'total' => 0.0,
                    'al_dia' => 0.0,
                    'b_0_30' => 0.0,
                    'b_31_60' => 0.0,
                    'b_61_90' => 0.0,
                    'b_90_mas' => 0.0,
                    'max_dias' => 0,
                    'facturas' => [],
                ];
            }

            $valor = (float) ($bill->saldo_pendiente + $bill->mora_acumulada + $bill->saldo_anterior_arrastrado);
            $dias = (int) $bill->dias_mora;

            $porInquilino[$id]['cantidad']++;
            $porInquilino[$id]['total'] += $valor;
            $porInquilino[$id][$this->bucket($dias)] += $valor;
            $porInquilino[$id]['max_dias'] = max($porInquilino[$id]['max_dias'], $dias);
            $porInquilino[$id]['facturas'][] = [
                'numero' => $bill->numero,
                'periodo' => \Carbon\Carbon::create($bill->anio, $bill->mes, 1)->translatedFormat('M Y'),
                'valor' => $valor,
                'dias_mora' => $dias,
                'direccion' => $bill->property?->direccion,
            ];
        }

        $filas = array_values($porInquilino);

        usort($filas, function ($a, $b) {
            return match ($this->ordenarPor) {
                'dias' => $b['max_dias'] <=> $a['max_dias'],
                'facturas' => $b['cantidad'] <=> $a['cantidad'],
                default => $b['total'] <=> $a['total'],
            };
        });

        $this->filas = $filas;
    }

    public function getTotalesGenerales(): array
    {
        return [
            'total' => array_sum(array_column($this->filas, 'total')),
            'al_dia' => array_sum(array_column($this->filas, 'al_dia')),
            'b_0_30' => array_sum(array_column($this->filas, 'b_0_30')),
            'b_31_60' => array_sum(array_column($this->filas, 'b_31_60')),
            'b_61_90' => array_sum(array_column($this->filas, 'b_61_90')),
            'b_90_mas' => array_sum(array_column($this->filas, 'b_90_mas')),
            'inquilinos' => count($this->filas),
        ];
    }
}

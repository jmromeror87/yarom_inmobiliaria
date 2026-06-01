<?php

namespace App\Filament\Pages;

use App\Models\Third;
use Filament\Pages\Page;

class Reportes extends Page
{
    public static function canAccess(): bool
    {
        return auth()->user()?->can('ver_reportes') ?? false;
    }

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrow-down-tray';
    public function getHeaderActions(): array { return []; }
    public function hasLogo(): bool { return false; }
    protected static ?string                 $navigationLabel = 'Reportes';
    protected static string|\UnitEnum|null   $navigationGroup = 'Cobros';
    protected static ?int                    $navigationSort  = 4;
    protected static ?string                 $title           = 'Reportes Exportables';
    protected string                         $view            = 'filament.pages.reportes';

    public int $mes;
    public int $anio;
    public ?string $propietario_id = null;

    public function mount(): void
    {
        $this->mes  = now()->month;
        $this->anio = now()->year;
    }

    public function getMeses(): array
    {
        return [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
        ];
    }

    public function getAnios(): array
    {
        $base = now()->year;
        return array_combine(range($base - 3, $base + 1), range($base - 3, $base + 1));
    }

    public function getPropietarios(): array
    {
        return Third::where('es_propietario', true)
            ->orderBy('razon_social')
            ->orderBy('primer_nombre')
            ->get()
            ->mapWithKeys(fn ($t) => [$t->id => $t->nombre_completo ?? $t->razon_social])
            ->toArray();
    }

    public function urlReporte(string $tipo, string $formato = 'excel'): string
    {
        $params = "mes={$this->mes}&anio={$this->anio}&formato={$formato}";
        if ($this->propietario_id && $tipo === 'liquidaciones') {
            $params .= "&propietario_id={$this->propietario_id}";
        }
        return url("/admin/reportes/{$tipo}?{$params}");
    }
}

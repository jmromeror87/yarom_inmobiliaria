<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Spatie\Activitylog\Models\Activity;

class AuditoriaSistema extends Page
{
    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-shield-check';
    protected static ?string                 $navigationLabel = 'Auditoría';
    protected static string|\UnitEnum|null   $navigationGroup = 'Sistema';
    protected static ?int                    $navigationSort  = 2;
    protected static ?string                 $title           = 'Auditoría del Sistema';
    protected string                         $view            = 'filament.pages.auditoria-sistema';

    public string $buscar       = '';
    public string $modulo       = '';
    public string $usuario_id   = '';
    public string $fecha_desde  = '';
    public string $fecha_hasta  = '';
    public int    $perPage      = 50;

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'admin']) ?? false;
    }

    public function getRegistros(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = Activity::with('causer')
            ->latest();

        if ($this->buscar) {
            $query->where(function ($q) {
                $q->where('description', 'like', "%{$this->buscar}%")
                  ->orWhere('subject_type', 'like', "%{$this->buscar}%")
                  ->orWhereJsonContains('properties->old', $this->buscar)
                  ->orWhereJsonContains('properties->attributes', $this->buscar);
            });
        }

        if ($this->modulo) {
            $query->where('log_name', $this->modulo);
        }

        if ($this->usuario_id) {
            $query->where('causer_id', $this->usuario_id)
                  ->where('causer_type', \App\Models\User::class);
        }

        if ($this->fecha_desde) {
            $query->whereDate('created_at', '>=', $this->fecha_desde);
        }

        if ($this->fecha_hasta) {
            $query->whereDate('created_at', '<=', $this->fecha_hasta);
        }

        return $query->paginate($this->perPage);
    }

    public function getUsuarios(): Collection
    {
        return \App\Models\User::orderBy('name')->get(['id', 'name']);
    }

    public function getModulos(): array
    {
        return [
            'default'                      => 'Todos los módulos',
            'Inmueble creado'              => 'Inmuebles',
            'Contrato arriendo creado'     => 'Contratos Arriendo',
            'Contrato administración creado' => 'Contratos Administración',
            'Factura generada'             => 'Facturación',
            'Liquidación creada'           => 'Liquidaciones',
            'Tercero creado'               => 'Terceros',
        ];
    }

    public static function moduloLabel(string $subjectType): string
    {
        return match(true) {
            str_contains($subjectType, 'Property')              => 'Inmueble',
            str_contains($subjectType, 'RentalContract')        => 'Contrato Arriendo',
            str_contains($subjectType, 'AdministrationContract') => 'Contrato Admón.',
            str_contains($subjectType, 'RentBill')              => 'Factura',
            str_contains($subjectType, 'OwnerLiquidation')      => 'Liquidación',
            str_contains($subjectType, 'Third')                 => 'Tercero',
            str_contains($subjectType, 'User')                  => 'Usuario',
            default                                             => class_basename($subjectType),
        };
    }
}

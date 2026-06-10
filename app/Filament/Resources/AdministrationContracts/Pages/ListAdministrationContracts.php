<?php
/*
|--------------------------------------------------------------------------
| YarOM ERP - Soluciones de Gestión
|--------------------------------------------------------------------------
| Proyecto privado desarrollado por:
| Ingeniero Jhoan Romero Rivera
| LinkedIn: https://linkedin.com/in/jmromeror87
|
| Módulo: \1
| Archivo: ListAdministrationContracts.php
| Fecha: CURRENT_DAY/05/2026
| Versión: v1.0
|--------------------------------------------------------------------------
*/
    

namespace App\Filament\Resources\AdministrationContracts\Pages;

use App\Filament\Resources\AdministrationContracts\AdministrationContractResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAdministrationContracts extends ListRecords
{
    protected static string $resource = AdministrationContractResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Crear Contrato')
                ->icon('heroicon-o-plus')
                ->extraAttributes([
                    'style' => 'background:linear-gradient(135deg,#1e3a8a,#E11D48)!important;color:#fff!important;border:none!important;box-shadow:0 4px 14px rgba(30,58,138,.35)!important;font-weight:700!important;',
                ]),
        ];
    }
}

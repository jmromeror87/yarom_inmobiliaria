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
| Archivo: CreateAdministrationContract.php
| Fecha: CURRENT_DAY/05/2026
| Versión: v1.0
|--------------------------------------------------------------------------
*/
            

namespace App\Filament\Resources\AdministrationContracts\Pages;

use App\Filament\Resources\AdministrationContracts\AdministrationContractResource;
use App\Filament\Resources\AdministrationContracts\Schemas\AdministrationContractForm;
use Filament\Resources\Pages\CreateRecord;

class CreateAdministrationContract extends CreateRecord
{
    protected static string $resource = AdministrationContractResource::class;

    protected function afterCreate(): void
    {
        AdministrationContractForm::copyClausesFromTemplate($this->record);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->record]);
    }
}

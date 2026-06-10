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
| Archivo: AdministrationContractResource.php
| Fecha: CURRENT_DAY/05/2026
| Versión: v1.0
|--------------------------------------------------------------------------
*/
            

namespace App\Filament\Resources\AdministrationContracts;

use App\Filament\Resources\AdministrationContracts\Pages\CreateAdministrationContract;
use App\Filament\Resources\AdministrationContracts\Pages\EditAdministrationContract;
use App\Filament\Resources\AdministrationContracts\Pages\ListAdministrationContracts;
use App\Filament\Resources\AdministrationContracts\Schemas\AdministrationContractForm;
use App\Filament\Resources\AdministrationContracts\Tables\AdministrationContractsTable;
use App\Filament\Traits\HasResourcePermissions;
use App\Models\AdministrationContract;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class AdministrationContractResource extends Resource
{
    use HasResourcePermissions;

    protected static string $permissionPrefix = 'contratos_administracion';
    protected static ?string $model = AdministrationContract::class;
    protected static ?string $navigationLabel = 'Contratos Administración';
    protected static ?string $modelLabel = 'Contrato';
    protected static ?string $pluralModelLabel = 'Contratos de Administración';
    protected static ?int $navigationSort = 2;
    protected static ?string $recordTitleAttribute = 'numero_contrato';

    public static function getNavigationIcon(): string { return 'heroicon-o-document-text'; }

    public static function getNavigationGroup(): ?string { return 'Contratación'; }

    public static function form(Schema $schema): Schema
    {
        return AdministrationContractForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AdministrationContractsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\AdministrationContracts\RelationManagers\AmendmentsRelationManager::class,
            \App\Filament\Resources\AdministrationContracts\RelationManagers\ClausesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListAdministrationContracts::route('/'),
            'create' => CreateAdministrationContract::route('/create'),
            'edit'   => EditAdministrationContract::route('/{record}/edit'),
        ];
    }
}

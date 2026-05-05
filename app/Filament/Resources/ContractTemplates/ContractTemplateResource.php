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
| Archivo: ContractTemplateResource.php
| Fecha: CURRENT_DAY/05/2026
| Versión: v1.0
|--------------------------------------------------------------------------
*/

namespace App\Filament\Resources\ContractTemplates;

use App\Filament\Resources\ContractTemplates\Pages\CreateContractTemplate;
use App\Filament\Resources\ContractTemplates\Pages\EditContractTemplate;
use App\Filament\Resources\ContractTemplates\Pages\ListContractTemplates;
use App\Filament\Resources\ContractTemplates\Schemas\ContractTemplateForm;
use App\Filament\Resources\ContractTemplates\Tables\ContractTemplatesTable;
use App\Models\ContractTemplate;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ContractTemplateResource extends Resource
{
    protected static ?string $model = ContractTemplate::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'nombre'; protected static ?string $navigationLabel = 'Plantillas de Contrato'; protected static ?string $modelLabel = 'Plantilla de Contrato'; protected static ?string $pluralModelLabel = 'Plantillas de Contrato'; protected static ?string $slug = 'plantillas-contrato';

    protected static ?int $navigationSort = 1;
    public static function getNavigationGroup(): ?string { return 'Contratación'; } public static function form(Schema $schema): Schema
    {
        return ContractTemplateForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ContractTemplatesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListContractTemplates::route('/'),
            'create' => CreateContractTemplate::route('/create'),
            'edit' => EditContractTemplate::route('/{record}/edit'),
        ];
    }
}

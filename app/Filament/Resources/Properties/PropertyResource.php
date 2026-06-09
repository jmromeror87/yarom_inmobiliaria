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
| Archivo: PropertyResource.php
| Fecha: CURRENT_DAY/05/2026
| Versión: v1.0
|--------------------------------------------------------------------------
*/
                

namespace App\Filament\Resources\Properties;

use App\Filament\Resources\Properties\Pages\CreateProperty;
use App\Filament\Resources\Properties\Pages\EditProperty; use App\Filament\Resources\Properties\Pages\ViewPropertyGallery; use App\Filament\Resources\Properties\Pages\PropertyDashboard;
use App\Filament\Resources\Properties\Pages\ListProperties;
use App\Filament\Resources\Properties\Schemas\PropertyForm;
use App\Filament\Resources\Properties\Tables\PropertiesTable;
use App\Filament\Traits\HasResourcePermissions;
use App\Models\Property;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class PropertyResource extends Resource
{
    use HasResourcePermissions;

    protected static string $permissionPrefix = 'inmuebles';
    protected static ?string $model = Property::class;
    protected static ?string $navigationLabel = 'Inmuebles';
    protected static ?string $modelLabel = 'Inmueble';
    protected static ?string $pluralModelLabel = 'Inmuebles';
    protected static ?int $navigationSort = 1;
    protected static ?string $recordTitleAttribute = 'codigo';

    public static function getNavigationIcon(): string { return 'heroicon-o-home-modern'; }

    public static function getNavigationGroup(): ?string { return 'Operativo'; }

    public static function form(Schema $schema): Schema
    {
        return PropertyForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PropertiesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListProperties::route('/'),
            'create' => CreateProperty::route('/create'),
            'edit'     => EditProperty::route('/{record}/edit'), 'gallery'   => ViewPropertyGallery::route('/{record}/galeria'), 'dashboard' => PropertyDashboard::route('/{record}/expediente'),
        ];
    }
}

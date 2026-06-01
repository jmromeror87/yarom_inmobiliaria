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
| Archivo: ThirdResource.php
| Fecha: CURRENT_DAY/05/2026
| Versión: v1.0
|--------------------------------------------------------------------------
*/


namespace App\Filament\Resources\Thirds;

use App\Filament\Resources\Thirds\Pages\CreateThird;
use App\Filament\Resources\Thirds\Pages\EditThird;
use App\Filament\Resources\Thirds\Pages\ListThirds;
use App\Filament\Resources\Thirds\Pages\ThirdExpediente;
use App\Filament\Resources\Thirds\Schemas\ThirdForm;
use App\Filament\Resources\Thirds\Tables\ThirdsTable;
use App\Filament\Traits\HasResourcePermissions;
use App\Models\Third;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ThirdResource extends Resource
{
    use HasResourcePermissions;

    protected static string $permissionPrefix = 'terceros';
    protected static ?string $model = Third::class;
    protected static ?string $navigationLabel = 'Terceros';
    protected static ?string $modelLabel = 'Tercero';
    protected static ?string $pluralModelLabel = 'Terceros';
    protected static ?int $navigationSort = 2;
    protected static ?string $recordTitleAttribute = 'nombre_completo';

    public static function getNavigationIcon(): string { return 'heroicon-o-users'; }

    public static function getNavigationGroup(): ?string { return 'Portafolio'; }

    public static function form(Schema $schema): Schema
    {
        return ThirdForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ThirdsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'      => ListThirds::route('/'),
            'create'     => CreateThird::route('/create'),
            'edit'       => EditThird::route('/{record}/edit'),
            'expediente' => ThirdExpediente::route('/{record}/expediente'),
        ];
    }
}

<?php

namespace App\Filament\Resources\RentalContracts;

use App\Filament\Resources\RentalContracts\Pages\CreateRentalContract;
use App\Filament\Resources\RentalContracts\Pages\EditRentalContract;
use App\Filament\Resources\RentalContracts\Pages\ListRentalContracts;
use App\Filament\Resources\RentalContracts\Schemas\RentalContractForm;
use App\Filament\Resources\RentalContracts\Tables\RentalContractsTable;
use App\Models\RentalContract;
use App\Filament\Traits\HasResourcePermissions;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class RentalContractResource extends Resource
{
    use HasResourcePermissions;

    protected static string $permissionPrefix = 'contratos_arriendo';
    protected static ?string $model = RentalContract::class;
    protected static ?string $navigationLabel = 'Contratos Arriendo';
    protected static ?string $modelLabel = 'Contrato de Arriendo';
    protected static ?string $pluralModelLabel = 'Contratos de Arriendo';
    protected static ?string $slug = 'contratos-arriendo';
    protected static ?int $navigationSort = 3;
    protected static ?string $recordTitleAttribute = 'numero_contrato';

    public static function getNavigationIcon(): string { return 'heroicon-o-key'; }

    public static function getNavigationGroup(): ?string { return 'Contratación'; }

    public static function getNavigationBadge(): ?string
    {
        $count = \App\Models\RentalContract::where('estado', 'activo')
            ->whereDate('fecha_fin', '<=', now()->addDays(30))
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Schema $schema): Schema
    {
        return RentalContractForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RentalContractsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\RentalContracts\RelationManagers\AmendmentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListRentalContracts::route('/'),
            'create' => CreateRentalContract::route('/create'),
            'edit'   => EditRentalContract::route('/{record}/edit'),
        ];
    }
}

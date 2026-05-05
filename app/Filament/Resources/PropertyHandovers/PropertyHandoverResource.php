<?php

namespace App\Filament\Resources\PropertyHandovers;

use App\Filament\Resources\PropertyHandovers\Pages\CreatePropertyHandover;
use App\Filament\Resources\PropertyHandovers\Pages\EditPropertyHandover; use App\Filament\Resources\PropertyHandovers\Pages\SignHandover;
use App\Filament\Resources\PropertyHandovers\Pages\ListPropertyHandovers;
use App\Filament\Resources\PropertyHandovers\Schemas\PropertyHandoverForm;
use App\Filament\Resources\PropertyHandovers\Tables\PropertyHandoversTable;
use App\Models\PropertyHandover;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class PropertyHandoverResource extends Resource
{
    protected static ?string $model = PropertyHandover::class;
    protected static ?string $navigationLabel = 'Actas de Entrega';
    protected static ?string $modelLabel = 'Acta de Entrega';
    protected static ?string $pluralModelLabel = 'Actas de Entrega';
    protected static ?string $slug = 'actas-entrega';
    protected static ?int $navigationSort = 3;
    protected static ?string $recordTitleAttribute = 'numero';

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-clipboard-document-check';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Operativo';
    }

    public static function form(Schema $schema): Schema
    {
        return PropertyHandoverForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PropertyHandoversTable::configure($table);
    }

    public static function getRelations(): array { return []; }

    public static function getPages(): array
    {
        return [
            'index'  => ListPropertyHandovers::route('/'),
            'create' => CreatePropertyHandover::route('/create'),
            'edit'   => EditPropertyHandover::route('/{record}/edit'), 'sign'   => SignHandover::route('/{record}/firmar'),
        ];
    }
}

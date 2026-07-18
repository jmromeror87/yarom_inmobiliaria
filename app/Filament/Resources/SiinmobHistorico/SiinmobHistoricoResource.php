<?php

namespace App\Filament\Resources\SiinmobHistorico;

use App\Filament\Resources\SiinmobHistorico\Pages\ListSiinmobHistorico;
use App\Models\SiinmobHistoricoNota;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class SiinmobHistoricoResource extends Resource
{
    protected static ?string $model = SiinmobHistoricoNota::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationLabel = 'Histórico Siinmob';
    protected static string|\UnitEnum|null $navigationGroup = 'Contabilidad';
    protected static ?int $navigationSort = 20;
    protected static ?string $modelLabel = 'Nota contable histórica';
    protected static ?string $pluralModelLabel = 'Histórico Siinmob';
    protected static ?string $slug = 'historico-siinmob';

    public static function canCreate(): bool { return false; }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([]);
    }

    public static function table(Table $table): Table
    {
        return \App\Filament\Resources\SiinmobHistorico\Tables\SiinmobHistoricoTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSiinmobHistorico::route('/'),
        ];
    }
}

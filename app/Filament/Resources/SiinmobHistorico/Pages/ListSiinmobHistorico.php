<?php

namespace App\Filament\Resources\SiinmobHistorico\Pages;

use App\Filament\Resources\SiinmobHistorico\SiinmobHistoricoResource;
use Filament\Resources\Pages\ListRecords;

class ListSiinmobHistorico extends ListRecords
{
    protected static string $resource = SiinmobHistoricoResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}

<?php

namespace App\Filament\Resources\PropertyServices\Pages;

use App\Filament\Resources\PropertyServices\PropertyServiceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPropertyServices extends ListRecords
{
    protected static string $resource = PropertyServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nuevo servicio')
                ->icon('heroicon-o-plus')
                ->extraAttributes([
                    'style' => 'background:linear-gradient(135deg,#1e3a8a,#E11D48)!important;color:#fff!important;border:none!important;box-shadow:0 4px 14px rgba(30,58,138,.35)!important;font-weight:700!important;',
                ]),
        ];
    }
}

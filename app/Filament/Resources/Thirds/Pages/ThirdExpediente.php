<?php

namespace App\Filament\Resources\Thirds\Pages;

use App\Filament\Resources\Thirds\ThirdResource;
use App\Models\Third;
use Filament\Actions\Action;
use Filament\Resources\Pages\Page;

class ThirdExpediente extends Page
{
    protected static string $resource = ThirdResource::class;
    protected string $view = 'filament.thirds.expediente';

    public Third $record;

    public function mount(Third $record): void
    {
        $this->record = $record->load([
            'municipio.departamento',
            'asesor',
            'rentBills.rentalContract.property',
            'rentBills.payments',
            'ownerLiquidations.property',
            'ownerLiquidations.rentalContract',
            'rentalContracts.property',
            'properties',
            'accountingLines.entry.period',
            'accountingLines.account',
            'cuentasPorCobrar',
            'cuentasPorPagar',
        ]);
    }

    public function getTitle(): string
    {
        return 'Expediente — ' . $this->record->nombre_completo;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('editar')
                ->label('Editar tercero')
                ->icon('heroicon-o-pencil')
                ->url(ThirdResource::getUrl('edit', ['record' => $this->record])),
        ];
    }
}

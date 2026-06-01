<?php

namespace App\Filament\Resources\Accounting\Pages;

use App\Filament\Resources\Accounting\AccountingEntryResource;
use App\Models\AccountingEntry;
use Filament\Actions\Action;
use Filament\Resources\Pages\Page;

class ViewEntry extends Page
{
    protected static string $resource = AccountingEntryResource::class;
    protected string $view = 'filament.accounting.comprobante-view';

    public AccountingEntry $record;

    public function mount(AccountingEntry $record): void
    {
        $this->record = $record->load([
            'lines.account',
            'lines.third',
            'lines.costCenter',
            'third',
            'period',
            'creadoPor',
            'contabilizadoPor',
            'anuladoPor',
        ]);
    }

    public function getTitle(): string
    {
        return $this->record->tipo_label . ' — ' . $this->record->numero;
    }

    protected function getHeaderActions(): array
    {
        $actions = [];

        if ($this->record->estado === 'borrador') {
            $actions[] = Action::make('editar')
                ->label('Editar')
                ->icon('heroicon-o-pencil')
                ->url(AccountingEntryResource::getUrl('edit', ['record' => $this->record]));
        }

        $actions[] = Action::make('volver')
            ->label('Volver a comprobantes')
            ->icon('heroicon-o-arrow-left')
            ->color('gray')
            ->url(AccountingEntryResource::getUrl('index'));

        return $actions;
    }
}

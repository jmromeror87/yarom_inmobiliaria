<?php
namespace App\Filament\Resources\RentalContracts\Pages;

use App\Filament\Resources\RentalContracts\RentalContractResource;
use App\Filament\Resources\RentalContracts\Schemas\RentalContractForm;
use App\Models\Request as Solicitud;
use App\Models\RentalContractThird;
use Filament\Resources\Pages\CreateRecord;

class CreateRentalContract extends CreateRecord
{
    protected static string $resource = RentalContractResource::class;

    protected function afterCreate(): void
    {
        // 1. Copiar cláusulas de la plantilla
        RentalContractForm::copyClausesFromTemplate($this->record);

        // 2. Copiar deudores solidarios desde la solicitud aprobada
        if ($this->record->request_id) {
            $solicitud = Solicitud::with(['thirds.third'])->find($this->record->request_id);
            if ($solicitud) {
                $orden = 0;
                foreach ($solicitud->thirds->where('rol', '!=', 'titular') as $t) {
                    RentalContractThird::create([
                        'rental_contract_id'   => $this->record->id,
                        'third_id'             => $t->third_id,
                        'rol'                  => 'deudor_solidario',
                        'ciudad_expedicion_doc'=> $t->third?->lugar_expedicion ?? null,
                        'direccion_notificacion'=> $t->third?->direccion_residencia ?? null,
                        'email_notificacion'   => $t->third?->email ?? null,
                        'celular_notificacion' => $t->third?->celular ?? null,
                        'orden'                => $orden++,
                    ]);
                }
            }
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->record]);
    }
}

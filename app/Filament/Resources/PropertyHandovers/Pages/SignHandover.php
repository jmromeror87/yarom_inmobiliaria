<?php
namespace App\Filament\Resources\PropertyHandovers\Pages;

use App\Filament\Resources\PropertyHandovers\PropertyHandoverResource;
use App\Models\PropertyHandover;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Storage;

class SignHandover extends Page
{
    protected static string $resource = PropertyHandoverResource::class;
    protected string $view = 'filament.property-handovers.sign';

    public PropertyHandover $record;
    public string $firmanteActual = 'arrendatario';

    public function mount(PropertyHandover $record): void
    {
        $this->record = $record->load(['arrendatario','asesor','property','rentalContract']);
    }

    public function getTitle(): string
    {
        return 'Firma Digital — ' . $this->record->numero;
    }

    public function guardarFirma(string $firmante, string $firmaBase64): void
    {
        $campo = $firmante === 'asesor' ? 'firma_digital_asesor' : 'firma_digital_arrendatario';

        $filename = 'actas/firmas/' . $this->record->numero . '-' . $firmante . '.png';
        $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $firmaBase64));
        Storage::disk('public')->put($filename, $imageData);

        $this->record->update([$campo => $firmaBase64]);
        $this->record->refresh();

        // Si ambos firmaron → estado firmada
        if ($this->record->firma_digital_arrendatario && $this->record->firma_digital_asesor) {
            $this->record->update(['estado' => 'firmada']);
            Notification::make()->title('✅ Ambas firmas registradas — Acta lista para cerrar')->success()->send();
        } else {
            $this->record->update(['estado' => 'en_proceso']);
            Notification::make()->title('✍️ Firma ' . ucfirst($firmante) . ' registrada')->success()->send();
        }

        $this->redirect(static::getResource()::getUrl('sign', ['record' => $this->record]));
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('volver')
                ->label('← Volver al acta')
                ->color('gray')
                ->url(PropertyHandoverResource::getUrl('edit', ['record' => $this->record])),
        ];
    }
}

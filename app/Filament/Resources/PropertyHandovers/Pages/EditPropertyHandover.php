<?php
namespace App\Filament\Resources\PropertyHandovers\Pages;

use App\Filament\Resources\PropertyHandovers\PropertyHandoverResource;
use App\Models\PropertyHandoverHistory;
use App\Helpers\WhatsApp;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditPropertyHandover extends EditRecord
{
    protected static string $resource = PropertyHandoverResource::class;

    public function getView(): string
    {
        if ($this->record->estado === 'cerrada') {
            return 'filament.property-handovers.edit-readonly';
        }
        return parent::getView();
    }

    protected function getHeaderActions(): array
    {
        $record   = $this->record->load(['arrendatario','property','history.changedBy','asesor']);
        $estado   = $record->estado;
        $acciones = [];

        // ── Badge estado final ─────────────────────────────
        if ($estado === 'cerrada') {
            $acciones[] = Action::make('badge')
                ->label('✅ ENTREGADO')->color('success')->disabled();
        }

        // ── PASO 1: Enviar al asesor (borrador) ───────────
        if ($estado === 'borrador') {
            $acciones[] = Action::make('enviar_asesor')
                ->label('📱 Enviar al asesor')
                ->color('info')
                ->icon('heroicon-o-paper-airplane')
                ->form([
                    TextInput::make('telefono')
                        ->label('Teléfono del asesor')
                        ->default($record->asesor?->phone ?? '')
                        ->required(),
                    Textarea::make('mensaje')
                        ->label('Mensaje')
                        ->default(
                            "Estimado asesor,\n\n" .
                            "Se ha asignado el acta de entrega {$record->numero} para el inmueble {$record->property?->codigo} — {$record->property?->direccion}.\n\n" .
                            "Arrendatario: {$record->arrendatario?->nombre_completo}\n" .
                            "Fecha: {$record->fecha_acta?->format('d/m/Y')} {$record->hora_acta}\n\n" .
                            "Por favor proceda con la entrega del inmueble.\n\n" .
                            "Serviarrendar S.A.S"
                        )
                        ->rows(5)->required(),
                ])
                ->action(function (array $data) {
                    $enviado = WhatsApp::enviar($data['telefono'], $data['mensaje']);

                    $this->record->update(['estado' => 'en_proceso']);
                    PropertyHandoverHistory::create([
                        'property_handover_id' => $this->record->id,
                        'changed_by'      => Auth::id(),
                        'estado_anterior' => 'borrador',
                        'estado_nuevo'    => 'en_proceso',
                        'canal'           => 'whatsapp',
                        'razon_cambio'    => 'Acta enviada al asesor para realizar la entrega',
                        'ip_address'      => request()->ip(),
                        'cambiado_en'     => now(),
                    ]);

                    if (!$enviado) {
                        $url = WhatsApp::urlFallback($data['telefono'], $data['mensaje']);
                        $this->redirect($url);
                    } else {
                        $this->redirect(static::getResource()::getUrl('edit', ['record' => $this->record]));
                    }
                });
        }

        // ── PASO 2: Firmar digitalmente (en_proceso) ──────
        if (in_array($estado, ['en_proceso', 'firmada'])) {
            $acciones[] = Action::make('firmar')
                ->label($estado === 'firmada' ? '✍️ Ver firmas' : '✍️ Firmar digitalmente')
                ->color('primary')
                ->icon('heroicon-o-pencil-square')
                ->url(PropertyHandoverResource::getUrl('sign', ['record' => $record]));
        }

        // ── PASO 3: Cerrar acta (firmada) ─────────────────
        if ($estado === 'firmada') {
            $acciones[] = Action::make('cerrar_acta')
                ->label('✅ Cerrar acta')
                ->color('success')
                ->icon('heroicon-o-check-badge')
                ->requiresConfirmation()
                ->modalHeading('¿Cerrar el acta de entrega?')
                ->modalDescription('Al cerrar queda como documento oficial. El inmueble queda registrado como entregado.')
                ->modalSubmitActionLabel('Sí, cerrar acta')
                ->action(function () {
                    $this->record->update(['estado' => 'cerrada']);
                    PropertyHandoverHistory::create([
                        'property_handover_id' => $this->record->id,
                        'changed_by'      => Auth::id(),
                        'estado_anterior' => 'firmada',
                        'estado_nuevo'    => 'cerrada',
                        'canal'           => 'sistema',
                        'razon_cambio'    => 'Acta cerrada oficialmente — inmueble entregado',
                        'ip_address'      => request()->ip(),
                        'cambiado_en'     => now(),
                    ]);
                    Notification::make()->title('✅ Acta cerrada — Inmueble entregado')->success()->send();
                    $this->redirect(static::getResource()::getUrl('edit', ['record' => $this->record]));
                });
        }

        // ── PASO 4: Enviar acta cerrada al inquilino ──────
        if ($estado === 'cerrada') {
            $acciones[] = Action::make('enviar_inquilino')
                ->label('📱 Enviar acta al inquilino')
                ->color('success')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->form([
                    TextInput::make('telefono')
                        ->label('WhatsApp del arrendatario')
                        ->default($record->arrendatario?->celular)->required(),
                    Textarea::make('mensaje')
                        ->label('Mensaje')
                        ->default(
                            "Estimad@ {$record->arrendatario?->nombre_completo},\n\n" .
                            "Le informamos que el acta de entrega del inmueble ha sido cerrada exitosamente:\n\n" .
                            "📋 N° Acta: {$record->numero}\n" .
                            "🏠 Inmueble: {$record->property?->codigo} — {$record->property?->direccion}\n" .
                            "📅 Fecha de entrega: {$record->fecha_acta?->format('d/m/Y')}\n" .
                            "🔑 Llaves entregadas: {$record->llaves_entregadas}\n\n" .
                            "Bienvenido a su nuevo hogar. Ante cualquier novedad comuníquese con nosotros.\n\n" .
                            "Serviarrendar S.A.S\n" .
                            "☎️ 3186934710"
                        )
                        ->rows(6)->required(),
                ])
                ->action(function (array $data) {
                    $enviado = WhatsApp::enviar($data['telefono'], $data['mensaje']);
                    $this->record->update([
                        'whatsapp_enviado'       => true,
                        'fecha_whatsapp_enviado' => now(),
                    ]);
                    PropertyHandoverHistory::create([
                        'property_handover_id' => $this->record->id,
                        'changed_by'      => Auth::id(),
                        'estado_anterior' => 'cerrada',
                        'estado_nuevo'    => 'cerrada',
                        'canal'           => 'whatsapp',
                        'razon_cambio'    => 'Acta enviada al arrendatario por WhatsApp',
                        'ip_address'      => request()->ip(),
                        'cambiado_en'     => now(),
                    ]);
                    if (!$enviado) {
                        $url = WhatsApp::urlFallback($data['telefono'], $data['mensaje']);
                        $this->redirect($url);
                    } else {
                        $this->redirect(static::getResource()::getUrl('edit', ['record' => $this->record]));
                    }
                });
        }

        // ── PDF ───────────────────────────────────────────
        $acciones[] = Action::make('pdf_acta')
            ->label('📄 PDF')
            ->color('gray')
            ->icon('heroicon-o-arrow-down-tray')
            ->url(fn () => route('acta.entrega.pdf', $record))
            ->openUrlInNewTab();

        // ── Historial ─────────────────────────────────────
        if ($record->history->isNotEmpty()) {
            $acciones[] = Action::make('historial')
                ->label('📋 Historial (' . $record->history->count() . ')')
                ->color('gray')
                ->icon('heroicon-o-clock')
                ->modalHeading('Historial del acta — ' . $record->numero)
                ->modalWidth('3xl')
                ->modalContent(fn () => view('filament.property-handovers.historial', [
                    'historial' => $record->history()->with('changedBy')->get(),
                    'record'    => $record,
                ]))
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Cerrar');
        }

        if (!in_array($estado, ['cerrada'])) {
            $acciones[] = DeleteAction::make()->label('Borrar');
        }

        return $acciones;
    }
}

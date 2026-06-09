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

        // ── PASO 1: Enviar a dos bandas (asesor + inquilino) ──
        if ($estado === 'borrador') {
            $acciones[] = Action::make('enviar_dos_bandas')
                ->label('📲 Enviar a asesor e inquilino')
                ->color('info')
                ->icon('heroicon-o-paper-airplane')
                ->modalHeading('Enviar notificaciones de entrega')
                ->modalDescription('Se enviará el enlace del acta al asesor y la notificación de cita al arrendatario.')
                ->modalWidth('lg')
                ->schema([
                    \Filament\Schemas\Components\Section::make('📱 Asesor')
                        ->schema([
                            TextInput::make('telefono_asesor')
                                ->label('Celular del asesor')
                                ->placeholder('Ej: 3201234567')
                                ->helperText('Recibirá el enlace para llenar el acta desde su celular.')
                                ->required(),
                        ]),
                    \Filament\Schemas\Components\Section::make('👤 Arrendatario / Inquilino')
                        ->schema([
                            TextInput::make('telefono_inquilino')
                                ->label('Celular del arrendatario')
                                ->default($record->arrendatario?->celular)
                                ->placeholder('Ej: 3209876543')
                                ->helperText('Recibirá notificación de la cita de entrega.')
                                ->required(),
                        ]),
                ])
                ->action(function (array $data) {
                    // Generar token público
                    $token = $this->record->generarToken();
                    $url   = route('acta.publica', ['token' => $token]);

                    $wap = app(\App\Services\WhatsAppService::class);

                    // ── Mensaje al ASESOR con enlace del acta ──
                    $r = $this->record;
                    $msgAsesor  = "📋 *ACTA DE ENTREGA — SERVIARRENDAR*\n\n";
                    $msgAsesor .= "Estimado(a) {$r->asesor?->name},\n\n";
                    $msgAsesor .= "Se le ha asignado la entrega del inmueble:\n\n";
                    $msgAsesor .= "🏠 *{$r->property?->codigo}* — {$r->property?->direccion}\n";
                    $msgAsesor .= "👤 Arrendatario: {$r->arrendatario?->nombre_completo}\n";
                    $msgAsesor .= "📞 Celular: {$r->arrendatario?->celular}\n";
                    $msgAsesor .= "📅 Fecha: {$r->fecha_acta?->format('d/m/Y')}";
                    if ($r->hora_acta) $msgAsesor .= " a las {$r->hora_acta}";
                    $msgAsesor .= "\n\n";
                    $msgAsesor .= "👇 *Ingrese aquí para registrar el inventario, medidores y firmas:*\n";
                    $msgAsesor .= $url . "\n\n";
                    $msgAsesor .= "⚠️ Este enlace es único y personal. No lo comparta.\n";
                    $msgAsesor .= "Serviarrendar S.A.S — +57 318 693 4710";

                    // ── Mensaje al INQUILINO — notificación de cita ──
                    $msgInquilino  = "🏠 *CITA DE ENTREGA DE INMUEBLE*\n\n";
                    $msgInquilino .= "Estimado(a) {$r->arrendatario?->nombre_completo},\n\n";
                    $msgInquilino .= "Le informamos que su entrega del inmueble ha sido programada:\n\n";
                    $msgInquilino .= "📍 *Dirección:* {$r->property?->direccion}\n";
                    $msgInquilino .= "📅 *Fecha:* {$r->fecha_acta?->format('d/m/Y')}";
                    if ($r->hora_acta) $msgInquilino .= "\n⏰ *Hora:* {$r->hora_acta}";
                    $msgInquilino .= "\n\n";
                    $msgInquilino .= "Por favor preséntese puntualmente con su documento de identidad.\n";
                    $msgInquilino .= "Nuestro asesor lo estará atendiendo.\n\n";
                    $msgInquilino .= "Ante cualquier duda:\n📞 +57 318 693 4710\n";
                    $msgInquilino .= "Serviarrendar S.A.S — Ocaña";

                    // Enviar mensajes
                    $enviadoAsesor    = false;
                    $enviadoInquilino = false;

                    if ($wap->isConnected()) {
                        $resA = $wap->enviar($data['telefono_asesor'], $msgAsesor);
                        $enviadoAsesor = $resA['ok'] ?? false;

                        $resI = $wap->enviar($data['telefono_inquilino'], $msgInquilino);
                        $enviadoInquilino = $resI['ok'] ?? false;
                    }

                    $this->record->update([
                        'estado'              => 'en_proceso',
                        'notificado_asesor'   => $enviadoAsesor,
                        'notificado_inquilino'=> $enviadoInquilino,
                    ]);

                    PropertyHandoverHistory::create([
                        'property_handover_id' => $this->record->id,
                        'changed_by'      => Auth::id(),
                        'estado_anterior' => 'borrador',
                        'estado_nuevo'    => 'en_proceso',
                        'canal'           => 'whatsapp',
                        'razon_cambio'    => 'Notificaciones enviadas — Asesor: ' . ($enviadoAsesor ? '✅' : '⚠️ manual') . ' | Inquilino: ' . ($enviadoInquilino ? '✅' : '⚠️ manual'),
                        'ip_address'      => request()->ip(),
                        'cambiado_en'     => now(),
                    ]);

                    if (!$enviadoAsesor || !$enviadoInquilino) {
                        // Abrir WhatsApp manual para el que falló
                        $fallback = !$enviadoAsesor
                            ? WhatsApp::urlFallback($data['telefono_asesor'], $msgAsesor)
                            : WhatsApp::urlFallback($data['telefono_inquilino'], $msgInquilino);

                        Notification::make()
                            ->title('⚠️ WhatsApp parcial — abra el enlace manualmente')
                            ->warning()->send();
                        $this->redirect($fallback);
                        return;
                    }

                    Notification::make()
                        ->title('✅ Notificaciones enviadas a asesor e inquilino')
                        ->success()->send();

                    $this->redirect(static::getResource()::getUrl('edit', ['record' => $this->record]));
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

        // ── PASO 3: Cerrar + enviar al inquilino (firmada) ───
        if ($estado === 'firmada') {
            $acciones[] = Action::make('cerrar_y_enviar')
                ->label('✅ Cerrar y enviar al inquilino')
                ->color('success')
                ->icon('heroicon-o-check-badge')
                ->modalHeading('Cerrar acta y notificar al arrendatario')
                ->modalDescription('El acta quedará cerrada oficialmente. El token público se desactivará y se enviará el PDF al inquilino por WhatsApp.')
                ->modalSubmitActionLabel('Cerrar y enviar')
                ->schema([
                    TextInput::make('telefono')
                        ->label('WhatsApp del arrendatario')
                        ->default($record->arrendatario?->celular)
                        ->required(),
                    \Filament\Forms\Components\Textarea::make('mensaje')
                        ->label('Mensaje')
                        ->default(
                            "Estimad@ {$record->arrendatario?->nombre_completo},\n\n" .
                            "Le informamos que el acta de entrega del inmueble ha sido cerrada exitosamente:\n\n" .
                            "📋 N° Acta: {$record->numero}\n" .
                            "🏠 Inmueble: {$record->property?->codigo} — {$record->property?->direccion}\n" .
                            "📅 Fecha de entrega: {$record->fecha_acta?->format('d/m/Y')}\n" .
                            "🔑 Llaves entregadas: {$record->llaves_entregadas}\n\n" .
                            "Adjunto encontrará el acta firmada como soporte.\n" .
                            "Bienvenido a su nuevo hogar. Ante cualquier novedad comuníquese con nosotros.\n\n" .
                            "Serviarrendar S.A.S — ☎️ +57 318 693 4710"
                        )
                        ->rows(7)->required(),
                ])
                ->action(function (array $data) {
                    // 1. Cerrar acta (booted() invalida el token automáticamente)
                    $this->record->update(['estado' => 'cerrada']);

                    PropertyHandoverHistory::create([
                        'property_handover_id' => $this->record->id,
                        'changed_by'      => Auth::id(),
                        'estado_anterior' => 'firmada',
                        'estado_nuevo'    => 'cerrada',
                        'canal'           => 'sistema',
                        'razon_cambio'    => 'Acta cerrada — token invalidado — enviando PDF al arrendatario',
                        'ip_address'      => request()->ip(),
                        'cambiado_en'     => now(),
                    ]);

                    // 2. Enviar PDF al inquilino
                    $numero = preg_replace('/[^0-9]/', '', $data['telefono']);
                    if (!str_starts_with($numero, '57')) $numero = '57' . $numero;

                    $wap     = app(\App\Services\WhatsAppService::class);
                    $enviado = false;

                    if ($wap->isConnected()) {
                        $pdfPath = $this->generarPdfTemporal();
                        if ($pdfPath) {
                            $res     = $wap->enviarConArchivo($numero, $data['mensaje'], $pdfPath, 'Acta-Entrega-' . $this->record->numero . '.pdf');
                            $enviado = $res['ok'] ?? false;
                            if (file_exists($pdfPath)) @unlink($pdfPath);
                        } else {
                            $res     = $wap->enviar($numero, $data['mensaje']);
                            $enviado = $res['ok'] ?? false;
                        }
                    }

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
                        'razon_cambio'    => 'Acta enviada al arrendatario' . ($enviado ? ' con PDF adjunto ✅' : ' — fallback manual ⚠️'),
                        'ip_address'      => request()->ip(),
                        'cambiado_en'     => now(),
                    ]);

                    if (!$enviado) {
                        Notification::make()->title('Acta cerrada ✅ — WhatsApp no disponible, use enlace manual')->warning()->send();
                        $this->redirect(\App\Helpers\WhatsApp::urlFallback($data['telefono'], $data['mensaje']));
                        return;
                    }

                    Notification::make()->title('✅ Acta cerrada y PDF enviado al arrendatario')->success()->send();
                    $this->redirect(static::getResource()::getUrl('edit', ['record' => $this->record]));
                });
        }

        // ── PASO 4: Reenviar acta (cerrada) ───────────────
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
                    $numero = preg_replace('/[^0-9]/', '', $data['telefono']);
                    if (!str_starts_with($numero, '57')) $numero = '57' . $numero;

                    $wap     = app(\App\Services\WhatsAppService::class);
                    $enviado = false;

                    if ($wap->isConnected()) {
                        // Generar PDF temporal del acta
                        $pdfPath = $this->generarPdfTemporal();

                        if ($pdfPath) {
                            $res     = $wap->enviarConArchivo(
                                $numero,
                                $data['mensaje'],
                                $pdfPath,
                                'Acta-Entrega-' . $this->record->numero . '.pdf'
                            );
                            $enviado = $res['ok'] ?? false;
                            if (file_exists($pdfPath)) @unlink($pdfPath);
                        } else {
                            $res     = $wap->enviar($numero, $data['mensaje']);
                            $enviado = $res['ok'] ?? false;
                        }
                    }

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
                        'razon_cambio'    => 'Acta enviada al arrendatario por WhatsApp' . ($enviado ? ' con PDF adjunto' : ' — enlace manual'),
                        'ip_address'      => request()->ip(),
                        'cambiado_en'     => now(),
                    ]);

                    if (!$enviado) {
                        Notification::make()->title('WhatsApp no disponible — use enlace manual')->warning()->send();
                        $this->redirect(WhatsApp::urlFallback($data['telefono'], $data['mensaje']));
                    } else {
                        Notification::make()->title('✅ Acta enviada con PDF adjunto')->success()->send();
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

    protected function generarPdfTemporal(): ?string
    {
        try {
            $handover = $this->record->load([
                'property.tipo','property.municipio',
                'arrendatario','asesor','items',
            ]);
            $company    = \App\Models\Company::with(['municipio'])->first();
            $logoBase64 = null;

            if ($company?->logo_path) {
                $path = storage_path('app/public/' . $company->logo_path);
                if (file_exists($path)) {
                    $logoBase64 = 'data:' . mime_content_type($path) . ';base64,' . base64_encode(file_get_contents($path));
                }
            }

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.acta-entrega',
                compact('handover', 'company', 'logoBase64')
            )->setPaper('letter', 'portrait');

            $tmpPath = storage_path('app/tmp/acta-' . $handover->numero . '-' . time() . '.pdf');
            if (!is_dir(dirname($tmpPath))) mkdir(dirname($tmpPath), 0755, true);
            file_put_contents($tmpPath, $pdf->output());
            return $tmpPath;

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Acta PDF error', ['error' => $e->getMessage()]);
            return null;
        }
    }
}

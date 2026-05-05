<?php

namespace App\Filament\Resources\RentalContracts\Pages;

use App\Filament\Resources\RentalContracts\RentalContractResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditRentalContract extends EditRecord
{
    protected static string $resource = RentalContractResource::class;

    public function getView(): string
    {
        if ($this->record->isReadOnly()) {
            return 'filament.rental-contracts.edit-readonly';
        }
        return parent::getView();
    }

    protected function getHeaderActions(): array
    {
        $record  = $this->record->load(['arrendatario','property','thirds.third','statusHistory','clauses']);
        $estado  = $record->estado;
        $acciones = [];

        // ── Badge estado final ─────────────────────────────
        if ($record->isReadOnly()) {
            $acciones[] = Action::make('badge_estado')
                ->label(match($estado) {
                    'activo'    => '🟢 ACTIVO',
                    'terminado' => '🔴 TERMINADO',
                    'cancelado' => '❌ CANCELADO',
                    default     => strtoupper($estado),
                })
                ->color(match($estado) {
                    'activo'    => 'success',
                    'terminado' => 'gray',
                    'cancelado' => 'danger',
                    default     => 'gray',
                })->disabled();
        }

        // ── 1. Enviar al arrendatario por WhatsApp ─────────
        if ($estado === 'borrador') {
            $acciones[] = Action::make('enviar_wap')
                ->label('📱 Enviar por WhatsApp')
                ->color('success')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->form([
                    TextInput::make('telefono')
                        ->label('WhatsApp del arrendatario')
                        ->default($record->arrendatario?->celular)
                        ->required(),
                    Textarea::make('mensaje')
                        ->label('Mensaje')
                        ->default($this->generarMensaje())
                        ->rows(6)->required(),
                ])
                ->action(function (array $data) {
                    $this->record->update(['estado' => 'enviado_arrendatario']);
                    $numero  = preg_replace('/[^0-9]/', '', $data['telefono']);
                    $mensaje = urlencode($data['mensaje']);
                    Notification::make()->title('Enviado — abriendo WhatsApp')->success()->send();
                    $enviado = \App\Helpers\WhatsApp::enviar($data['telefono'] ?? $data['telefono_sura'] ?? '', $data['mensaje'] ?? $data['mensaje_enviado'] ?? '');
                    if (!$enviado) {
                        $numero  = preg_replace('/[^0-9]/', '', $data['telefono'] ?? $data['telefono_sura'] ?? '');
                        if (!str_starts_with($numero, '57')) $numero = '57' . $numero;
                        $this->redirect("https://wa.me/{$numero}?text=" . urlencode($data['mensaje'] ?? $data['mensaje_enviado'] ?? ''));
                    } else {
                        $this->redirect(static::getResource()::getUrl('edit', ['record' => $this->record]));
                    }
                });

            $acciones[] = Action::make('enviar_email')
                ->label('📧 Enviar por correo')
                ->color('info')
                ->icon('heroicon-o-envelope')
                ->form([
                    TextInput::make('email')
                        ->label('Correo del arrendatario')
                        ->email()->default($record->arrendatario?->email)->required(),
                    Textarea::make('mensaje')
                        ->label('Mensaje')->default($this->generarMensaje())
                        ->rows(6)->required(),
                ])
                ->action(function (array $data) {
                    $this->record->update(['estado' => 'enviado_arrendatario']);
                    $asunto = urlencode('Contrato de arrendamiento ' . $this->record->numero_contrato);
                    $cuerpo = urlencode($data['mensaje']);
                    Notification::make()->title('Registrado — abriendo correo')->success()->send();
                    $this->redirect("mailto:{$data['email']}?subject={$asunto}&body={$cuerpo}");
                });
        }

        // ── 2. Arrendatario aprueba ────────────────────────
        if ($estado === 'enviado_arrendatario') {
            $acciones[] = Action::make('arrendatario_aprueba')
                ->label('✅ Arrendatario aprueba')
                ->color('success')
                ->icon('heroicon-o-check-circle')
                ->form([
                    Textarea::make('notas')->label('Observaciones')->rows(2),
                ])
                ->action(function (array $data) {
                    $this->record->update(['estado' => 'aprobado']);
                    Notification::make()->title('✅ Aprobado — listo para firma y notaría')->success()->send();
                    $this->redirect(static::getResource()::getUrl('edit', ['record' => $this->record]));
                });

            $acciones[] = Action::make('arrendatario_objeta')
                ->label('⚠️ Arrendatario objeta')
                ->color('warning')
                ->icon('heroicon-o-exclamation-triangle')
                ->form([
                    Textarea::make('notas')->label('¿Qué objeta?')->rows(2)->required(),
                ])
                ->action(function (array $data) {
                    $this->record->update(['estado' => 'borrador', 'notas' => $data['notas']]);
                    Notification::make()->title('Vuelve a borrador — edite las cláusulas')->warning()->send();
                    $this->redirect(static::getResource()::getUrl('edit', ['record' => $this->record]));
                });
        }

        // ── 3. Registrar firma y notaría ──────────────────
        if ($estado === 'aprobado') {
            $acciones[] = Action::make('registrar_firma')
                ->label('✍️ Registrar firma — Notaría')
                ->color('primary')
                ->icon('heroicon-o-building-library')
                ->form([
                    DatePicker::make('fecha_firma')
                        ->label('Fecha de firma')->default(now())->required(),
                    TextInput::make('firmado_por')
                        ->label('Firmado por')
                        ->default($record->arrendatario?->nombre_completo),
                    TextInput::make('notaria')
                        ->label('Notaría donde se autentica')
                        ->placeholder('Notaría Primera de Ocaña'),
                    TextInput::make('numero_escritura')
                        ->label('N° escritura / autenticación'),
                    DatePicker::make('fecha_autenticacion')
                        ->label('Fecha de autenticación')->default(now()),
                    FileUpload::make('path_contrato_firmado')
                        ->label('PDF contrato firmado y autenticado')
                        ->disk('public')->directory('contratos/arriendo/firmados')
                        ->acceptedFileTypes(['application/pdf'])->maxSize(20480),
                ])
                ->action(function (array $data) {
                    $this->record->update([
                        'estado'               => 'firmado',
                        'fecha_firma'          => $data['fecha_firma'],
                        'firmado_por'          => $data['firmado_por'],
                        'path_contrato_firmado'=> $data['path_contrato_firmado'] ?? null,
                    ]);
                    Notification::make()->title('✍️ Firmado y autenticado en notaría')->success()->send();
                    $this->redirect(static::getResource()::getUrl('edit', ['record' => $this->record]));
                });
        }

        // ── 4. Activar contrato ───────────────────────────
        if ($estado === 'firmado') {
            $acciones[] = Action::make('activar')
                ->label('🟢 Activar contrato')
                ->color('success')
                ->icon('heroicon-o-check-badge')
                ->requiresConfirmation()
                ->modalHeading('¿Activar el contrato?')
                ->modalDescription('El inmueble quedará en estado ARRENDADO y el contrato pasará a solo lectura.')
                ->modalSubmitActionLabel('Sí, activar')
                ->action(function () {
                    $this->record->update(['estado' => 'activo']);
                    Notification::make()->title('🟢 Contrato ACTIVO — Inmueble ARRENDADO')->success()->send();
                    $this->redirect(static::getResource()::getUrl('edit', ['record' => $this->record]));
                });
        }

        // ── PDF ────────────────────────────────────────────
        $acciones[] = Action::make('pdf')
            ->label('📄 PDF')
            ->color('gray')
            ->icon('heroicon-o-arrow-down-tray')
            ->url(fn () => route('contrato.arriendo.pdf', $record))
            ->openUrlInNewTab();

        // ── Historial de estados ──────────────────────────
        if ($record->statusHistory->isNotEmpty()) {
            $acciones[] = Action::make('ver_historial_estados')
                ->label('📋 Historial (' . $record->statusHistory->count() . ')')
                ->color('gray')
                ->icon('heroicon-o-clock')
                ->modalHeading('Historial de estados — ' . $record->numero_contrato)
                ->modalWidth('3xl')
                ->modalContent(fn () => view('filament.rental-contracts.historial-estados', [
                    'historial' => $record->statusHistory()->with('changedBy')->get(),
                    'record'    => $record,
                ]))
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Cerrar');
        }

        // ── Cláusulas editadas ────────────────────────────
        $clausulasEditadas = $record->clauses->where('fue_editada', true)->count();
        if ($clausulasEditadas > 0) {
            $acciones[] = Action::make('ver_clausulas_editadas')
                ->label('⚠️ Cláusulas editadas (' . $clausulasEditadas . ')')
                ->color('warning')
                ->icon('heroicon-o-document-text')
                ->modalHeading('Cláusulas modificadas')
                ->modalWidth('4xl')
                ->modalContent(fn () => view('filament.rental-contracts.clausulas-editadas', [
                    'clausulas' => $record->clauses->where('fue_editada', true),
                    'record'    => $record,
                ]))
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Cerrar');
        }

        if (!$record->isReadOnly()) {
            $acciones[] = DeleteAction::make()->label('Borrar');
        }

        return $acciones;
    }

    protected function generarMensaje(): string
    {
        $r = $this->record->load(['property','arrendatario']);
        $tipo = $r->tipo === 'comercial' ? 'COMERCIAL' : 'VIVIENDA';
        $msg  = "Estimado(a) {$r->arrendatario?->nombre_completo},\n\n";
        $msg .= "Le informamos que su contrato de arrendamiento ha sido preparado:\n\n";
        $msg .= "📋 N° Contrato: {$r->numero_contrato}\n";
        $msg .= "🏠 Inmueble: {$r->property?->codigo} — {$r->property?->direccion}\n";
        $msg .= "💰 Canon: $" . number_format($r->canon_mensual, 0, ',', '.') . " COP\n";
        $msg .= "📅 Inicio: {$r->fecha_inicio?->format('d/m/Y')} · Fin: {$r->fecha_fin?->format('d/m/Y')}\n";
        $msg .= "📄 Tipo: {$tipo}\n\n";
        $msg .= "Por favor revise el contrato y contáctenos para proceder con la firma y autenticación en notaría.\n\n";
        $msg .= "Serviarrendar S.A.S — Ocaña, Norte de Santander";
        return $msg;
    }
}

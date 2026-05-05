<?php

namespace App\Filament\Resources\AdministrationContracts\Pages;

use App\Filament\Resources\AdministrationContracts\AdministrationContractResource;
use App\Models\ContractNotaryTracking;
use App\Models\ContractStatusHistory;
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

class EditAdministrationContract extends EditRecord
{
    protected static string $resource = AdministrationContractResource::class;

    public function getView(): string
    {
        if ($this->record->isReadOnly()) {
            return 'filament.contracts.edit-readonly';
        }
        return parent::getView();
    }

    protected function getHeaderActions(): array
    {
        $record  = $this->record->load(['statusHistory.changedBy', 'notaryTracking', 'propietario', 'property', 'clauses']);
        $estado  = $record->estado;
        $acciones = [];

        // ── Badge estado final ────────────────────────────────
        if ($record->isReadOnly()) {
            $acciones[] = Action::make('badge_estado')
                ->label(match($estado) {
                    'firmado'   => '✍️ FIRMADO',
                    'activo'    => '🟢 ACTIVO',
                    'terminado' => '🔴 TERMINADO',
                    'cancelado' => '❌ CANCELADO',
                    default     => strtoupper($estado),
                })
                ->color(match($estado) {
                    'activo'    => 'success',
                    'firmado'   => 'primary',
                    'terminado' => 'gray',
                    'cancelado' => 'danger',
                    default     => 'gray',
                })
                ->disabled();
        }

        // ── 1. Enviar al propietario ──────────────────────────
        if ($estado === 'borrador') {
            $acciones[] = Action::make('enviar_propietario')
                ->label('📤 Enviar al propietario')
                ->color('info')
                ->icon('heroicon-o-paper-airplane')
                ->form([
                    Select::make('canal')
                        ->label('Canal de envío')
                        ->options(['whatsapp' => '📱 WhatsApp', 'email' => '📧 Correo', 'presencial' => '🤝 Presencial'])
                        ->default('whatsapp')->required()->live(),
                    TextInput::make('telefono')
                        ->label('Teléfono WhatsApp')
                        ->default($record->propietario?->celular)
                        ->visible(fn ($get) => $get('canal') === 'whatsapp'),
                    TextInput::make('email')
                        ->label('Correo del propietario')
                        ->default($record->propietario?->email)
                        ->visible(fn ($get) => $get('canal') === 'email'),
                    Textarea::make('mensaje')
                        ->label('Mensaje')
                        ->default("Estimado(a) {$record->propietario?->nombre_completo},\n\nAdjunto encontrará el contrato de administración {$record->numero_contrato} para su revisión y aprobación.\n\nQuedamos atentos a sus comentarios.\n\nServiarrendar S.A.S")
                        ->rows(5)->required(),
                    \Filament\Forms\Components\Placeholder::make('pdf_link')->label('PDF del contrato')->content(fn () => new \Illuminate\Support\HtmlString('<a href="' . route("contrato.pdf", $record) . '" target="_blank" style="color:#2563eb;font-weight:700;font-size:14px;">📄 Descargar PDF para adjuntar en WhatsApp</a>')), Textarea::make('razon_cambio')->label('Notas internas')->rows(2),
                ])
                ->action(function (array $data) {
                    $this->record->update(['estado' => 'enviado_propietario']);
                    ContractStatusHistory::create([
                        'administration_contract_id' => $this->record->id,
                        'changed_by'      => Auth::id(),
                        'estado_anterior' => 'borrador',
                        'estado_nuevo'    => 'enviado_propietario',
                        'canal'           => $data['canal'],
                        'razon_cambio'    => $data['razon_cambio'] ?? 'Enviado al propietario para revisión',
                        'ip_address'      => request()->ip(),
                        'cambiado_en'     => now(),
                    ]);
                    Notification::make()->title('Contrato enviado al propietario')->success()->send();
                    $this->redirect(static::getResource()::getUrl('edit', ['record' => $this->record]));
                });
        }

        // ── 2. Propietario aprueba ────────────────────────────
        if (in_array($estado, ['enviado_propietario', 'en_revision'])) {
            $acciones[] = Action::make('propietario_aprueba')
                ->label('✅ Propietario aprueba')
                ->color('success')
                ->icon('heroicon-o-check-circle')
                ->form([
                    Textarea::make('razon_cambio')
                        ->label('Observaciones del propietario')->rows(3),
                ])
                ->action(function (array $data) use ($estado) {
                    $this->record->update(['estado' => 'aprobado_gerencia']);
                    ContractStatusHistory::create([
                        'administration_contract_id' => $this->record->id,
                        'changed_by'      => Auth::id(),
                        'estado_anterior' => $estado,
                        'estado_nuevo'    => 'aprobado_gerencia',
                        'canal'           => 'presencial',
                        'razon_cambio'    => $data['razon_cambio'] ?? 'Propietario aprueba el contrato',
                        'ip_address'      => request()->ip(),
                        'cambiado_en'     => now(),
                    ]);
                    Notification::make()->title('✅ Aprobado por propietario — listo para notaría')->success()->send();
                    $this->redirect(static::getResource()::getUrl('edit', ['record' => $this->record]));
                });

            $acciones[] = Action::make('propietario_objeta')
                ->label('⚠️ Propietario objeta')
                ->color('warning')
                ->icon('heroicon-o-exclamation-triangle')
                ->form([
                    Textarea::make('razon_cambio')
                        ->label('¿Qué objeta el propietario?')->rows(3)->required(),
                ])
                ->action(function (array $data) use ($estado) {
                    $this->record->update(['estado' => 'en_revision']);
                    ContractStatusHistory::create([
                        'administration_contract_id' => $this->record->id,
                        'changed_by'      => Auth::id(),
                        'estado_anterior' => $estado,
                        'estado_nuevo'    => 'en_revision',
                        'canal'           => 'presencial',
                        'razon_cambio'    => $data['razon_cambio'],
                        'ip_address'      => request()->ip(),
                        'cambiado_en'     => now(),
                    ]);
                    Notification::make()->title('Contrato en revisión — edite las cláusulas')->warning()->send();
                    $this->redirect(static::getResource()::getUrl('edit', ['record' => $this->record]));
                });
        }

        // ── 3. Enviar a notaría ───────────────────────────────
        if ($estado === 'aprobado_gerencia') {
            $acciones[] = Action::make('enviar_notaria')
                ->label('🏛️ Enviar a notaría')
                ->color('primary')
                ->icon('heroicon-o-building-library')
                ->form([
                    TextInput::make('notaria_nombre')
                        ->label('Notaría')->placeholder('Notaría Primera de Ocaña')->required(),
                    TextInput::make('notaria_ciudad')->label('Ciudad')->default('Ocaña'),
                    TextInput::make('notaria_direccion')->label('Dirección notaría'),
                    TextInput::make('notaria_telefono')->label('Teléfono notaría'),
                    DatePicker::make('fecha_envio_notaria')->label('Fecha de envío')->default(now())->required(),
                    TextInput::make('enviado_por_nombre')->label('Llevado por')->default(Auth::user()?->name),
                    TextInput::make('numero_radicado_notaria')->label('N° Radicado notaría'),
                    Textarea::make('razon_cambio')->label('Notas')->rows(2),
                ])
                ->action(function (array $data) {
                    ContractNotaryTracking::create([
                        'administration_contract_id' => $this->record->id,
                        'gestionado_por'       => Auth::id(),
                        'notaria_nombre'       => $data['notaria_nombre'],
                        'notaria_ciudad'       => $data['notaria_ciudad'],
                        'notaria_direccion'    => $data['notaria_direccion'] ?? null,
                        'notaria_telefono'     => $data['notaria_telefono'] ?? null,
                        'fecha_envio_notaria'  => $data['fecha_envio_notaria'],
                        'enviado_por_nombre'   => $data['enviado_por_nombre'] ?? null,
                        'numero_radicado_notaria' => $data['numero_radicado_notaria'] ?? null,
                    ]);
                    $this->record->update(['estado' => 'enviado_notaria']);
                    ContractStatusHistory::create([
                        'administration_contract_id' => $this->record->id,
                        'changed_by'      => Auth::id(),
                        'estado_anterior' => 'aprobado_gerencia',
                        'estado_nuevo'    => 'enviado_notaria',
                        'canal'           => 'presencial',
                        'razon_cambio'    => $data['razon_cambio'] ?? 'Enviado a ' . $data['notaria_nombre'],
                        'ip_address'      => request()->ip(),
                        'cambiado_en'     => now(),
                    ]);
                    Notification::make()->title('📜 Enviado a notaría')->success()->send();
                    $this->redirect(static::getResource()::getUrl('edit', ['record' => $this->record]));
                });
        }

        // ── 4. Registrar autenticación notaría ────────────────
        if ($estado === 'enviado_notaria') {
            $acciones[] = Action::make('registrar_autenticacion')
                ->label('🔏 Registrar autenticación')
                ->color('warning')
                ->icon('heroicon-o-check-badge')
                ->form([
                    DatePicker::make('fecha_autenticacion')->label('Fecha de autenticación')->default(now())->required(),
                    TextInput::make('numero_escritura')->label('N° Escritura pública'),
                    TextInput::make('valor_autenticacion')->label('Valor autenticación ($)')->numeric()->prefix('$'),
                    Textarea::make('observaciones')->label('Observaciones')->rows(2),
                ])
                ->action(function (array $data) {
                    $notaria = $this->record->notaryTracking;
                    if ($notaria) {
                        $notaria->update([
                            'fecha_autenticacion' => $data['fecha_autenticacion'],
                            'numero_escritura'    => $data['numero_escritura'] ?? null,
                            'valor_autenticacion' => $data['valor_autenticacion'] ?? null,
                            'observaciones'       => $data['observaciones'] ?? null,
                        ]);
                    }
                    $this->record->update(['estado' => 'autenticado_notaria']);
                    ContractStatusHistory::create([
                        'administration_contract_id' => $this->record->id,
                        'changed_by'      => Auth::id(),
                        'estado_anterior' => 'enviado_notaria',
                        'estado_nuevo'    => 'autenticado_notaria',
                        'canal'           => 'presencial',
                        'razon_cambio'    => 'Autenticado en notaría' . ($data['numero_escritura'] ? ' — Escritura ' . $data['numero_escritura'] : ''),
                        'ip_address'      => request()->ip(),
                        'cambiado_en'     => now(),
                    ]);
                    Notification::make()->title('🔏 Autenticado en notaría')->success()->send();
                    $this->redirect(static::getResource()::getUrl('edit', ['record' => $this->record]));
                });
        }

        // ── 5. Registrar firma y activar ──────────────────────
        if ($estado === 'autenticado_notaria') {
            $acciones[] = Action::make('registrar_firma')
                ->label('✍️ Registrar firma — Activar')
                ->color('success')
                ->icon('heroicon-o-check-badge')
                ->requiresConfirmation()
                ->modalHeading('¿Activar el contrato?')
                ->modalDescription('Al activar, el contrato queda en solo lectura y el inmueble pasa a estado ARRENDADO automáticamente.')
                ->modalSubmitActionLabel('Sí, activar contrato')
                ->form([
                    DatePicker::make('fecha_firma')->label('Fecha de firma')->default(now())->required(),
                    TextInput::make('firmado_por')->label('Firmado por')->default($this->record->propietario?->nombre_completo),
                    FileUpload::make('path_contrato_firmado')
                        ->label('PDF contrato firmado y autenticado')
                        ->disk('public')->directory('contratos/firmados')
                        ->acceptedFileTypes(['application/pdf'])->maxSize(20480),
                    TextInput::make('recibido_por')->label('Recibido de notaría por'),
                    DatePicker::make('fecha_regreso')->label('Fecha regreso de notaría')->default(now()),
                    Textarea::make('observaciones')->label('Observaciones')->rows(2),
                ])
                ->action(function (array $data) {
                    $notaria = $this->record->notaryTracking;
                    if ($notaria) {
                        $notaria->update([
                            'fecha_regreso'          => $data['fecha_regreso'] ?? now(),
                            'recibido_por'            => $data['recibido_por'] ?? null,
                            'path_contrato_firmado'   => $data['path_contrato_firmado'] ?? null,
                            'observaciones'           => $data['observaciones'] ?? null,
                        ]);
                    }
                    $this->record->update([
                        'estado'      => 'activo',
                        'fecha_firma' => $data['fecha_firma'],
                        'firmado_por' => $data['firmado_por'],
                    ]);
                    Notification::make()->title('🟢 Contrato ACTIVO — Inmueble actualizado')->success()->send();
                    $this->redirect(static::getResource()::getUrl('edit', ['record' => $this->record]));
                });
        }

        // ── PDF ───────────────────────────────────────────────
        $acciones[] = Action::make('descargar_pdf')
            ->label('📄 PDF')
            ->color('gray')
            ->icon('heroicon-o-arrow-down-tray')
            ->url(fn () => route('contrato.pdf', $this->record))
            ->openUrlInNewTab();

        // ── Historial de estados ──────────────────────────────
        if ($record->statusHistory->isNotEmpty()) {
            $acciones[] = Action::make('ver_historial_estados')
                ->label('📋 Historial (' . $record->statusHistory->count() . ')')
                ->color('gray')
                ->icon('heroicon-o-clock')
                ->modalHeading('Historial de estados — ' . $record->numero_contrato)
                ->modalWidth('3xl')
                ->modalContent(fn () => view('filament.contracts.historial-estados', [
                    'historial' => $record->statusHistory()->with('changedBy')->get(),
                    'record'    => $record,
                ]))
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Cerrar');
        }

        // ── Historial cláusulas ───────────────────────────────
        $acciones[] = Action::make('ver_historial')
            ->label('🖊️ Cláusulas editadas')
            ->color('warning')
            ->icon('heroicon-o-document-text')
            ->modalHeading('Historial de cambios en cláusulas')
            ->modalWidth('5xl')
            ->modalContent(function () {
                $historial = \App\Models\ContractClauseHistory::whereHas('clause', function ($q) {
                    $q->where('administration_contract_id', $this->record->id);
                })->with(['clause', 'editor'])->orderByDesc('editado_en')->get();
                if ($historial->isEmpty()) return view('filament.contracts.historial-empty');
                return view('filament.contracts.historial', ['historial' => $historial]);
            })
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Cerrar');

        if (!$record->isReadOnly()) {
            $acciones[] = DeleteAction::make()->label('Borrar');
        }

        return $acciones;
    }
}

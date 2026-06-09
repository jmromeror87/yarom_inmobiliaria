<?php

namespace App\Filament\Resources\AdministrationContracts\Pages;

use App\Filament\Resources\AdministrationContracts\AdministrationContractResource;
use App\Models\ContractNotaryTracking;
use App\Models\ContractStatusHistory;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use App\Forms\Components\MapboxAddressInput;
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
                    Textarea::make('razon_cambio')->label('Notas internas')->rows(2),
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

                    if ($data['canal'] === 'whatsapp') {
                        $numero = preg_replace('/[^0-9]/', '', $data['telefono'] ?? '');
                        if (!str_starts_with($numero, '57')) $numero = '57' . $numero;

                        $pdfPath = $this->generarPdfTemporal();
                        $wap     = app(\App\Services\WhatsAppService::class);
                        $enviado = false;

                        if ($pdfPath && $wap->isConnected()) {
                            $res     = $wap->enviarConArchivo(
                                $numero,
                                $data['mensaje'],
                                $pdfPath,
                                'ContratoAdministracion-' . $this->record->numero_contrato . '.pdf'
                            );
                            $enviado = $res['ok'] ?? false;
                            if (file_exists($pdfPath)) @unlink($pdfPath);
                        }

                        if (!$enviado) {
                            Notification::make()->title('WhatsApp no disponible — use enlace manual')->warning()->send();
                            $this->redirect("https://wa.me/{$numero}?text=" . urlencode($data['mensaje']));
                            return;
                        }

                        Notification::make()->title('✅ Contrato enviado por WhatsApp con PDF adjunto')->success()->send();

                    } elseif ($data['canal'] === 'email') {
                        $asunto = urlencode('Contrato de Administración ' . $this->record->numero_contrato . ' — Serviarrendar S.A.S');
                        $cuerpo = urlencode($data['mensaje']);
                        Notification::make()->title('Abriendo cliente de correo')->success()->send();
                        $this->redirect("mailto:{$data['email']}?subject={$asunto}&body={$cuerpo}");
                        return;
                    } else {
                        Notification::make()->title('Entrega presencial registrada')->success()->send();
                    }

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

        // ── 3. Enviar a notaría — solo cambia estado, sin formulario ──
        if ($estado === 'aprobado_gerencia') {
            $acciones[] = Action::make('enviar_notaria')
                ->label('🏛️ Enviar a notaría')
                ->color('primary')
                ->icon('heroicon-o-building-library')
                ->requiresConfirmation()
                ->modalHeading('¿Confirmar envío a notaría?')
                ->modalDescription('El propietario llevará el contrato a autenticar. El sistema registrará la fecha de salida. Podrá registrar los datos de la notaría cuando regrese.')
                ->modalSubmitActionLabel('Sí, marcar como enviado')
                ->action(function () {
                    ContractNotaryTracking::create([
                        'administration_contract_id' => $this->record->id,
                        'gestionado_por'      => Auth::id(),
                        'fecha_envio_notaria' => now(),
                        'enviado_por_nombre'  => Auth::user()?->name,
                    ]);
                    $this->record->update(['estado' => 'enviado_notaria']);
                    ContractStatusHistory::create([
                        'administration_contract_id' => $this->record->id,
                        'changed_by'      => Auth::id(),
                        'estado_anterior' => 'aprobado_gerencia',
                        'estado_nuevo'    => 'enviado_notaria',
                        'canal'           => 'presencial',
                        'razon_cambio'    => 'Contrato entregado al propietario para autenticación',
                        'ip_address'      => request()->ip(),
                        'cambiado_en'     => now(),
                    ]);
                    Notification::make()->title('🏛️ Registrado — Pendiente retorno de notaría')->success()->send();
                    $this->redirect(static::getResource()::getUrl('edit', ['record' => $this->record]));
                });
        }

        // ── 4. Registrar retorno autenticado — datos de notaría + PDF ──
        if ($estado === 'enviado_notaria') {
            $acciones[] = Action::make('registrar_autenticacion')
                ->label('📋 Registrar autenticación')
                ->color('warning')
                ->icon('heroicon-o-document-check')
                ->modalHeading('Registrar retorno de notaría')
                ->modalDescription('El propietario regresó con el contrato autenticado. Complete los datos.')
                ->modalSubmitActionLabel('Guardar autenticación')
                ->form([
                    TextInput::make('notaria_nombre')
                        ->label('Notaría')->placeholder('Notaría Primera de Ocaña, Notaría 5 de Bucaramanga...')->required(),
                    TextInput::make('notaria_ciudad')
                        ->label('Ciudad donde autenticó')->default('Ocaña')->required(),
                    DatePicker::make('fecha_autenticacion')
                        ->label('Fecha de autenticación')->default(now())->required(),
                    TextInput::make('numero_radicado_notaria')
                        ->label('N° Radicado notaría'),
                    TextInput::make('numero_escritura')
                        ->label('N° Escritura pública (si aplica)'),
                    TextInput::make('valor_autenticacion')
                        ->label('Valor cobrado por autenticación ($)')->numeric()->prefix('$'),
                    TextInput::make('recibido_por')
                        ->label('Recibido en oficina por')->default(Auth::user()?->name),
                    FileUpload::make('path_contrato_firmado')
                        ->label('Subir contrato firmado y autenticado (PDF)')
                        ->disk('public')->directory('contratos/firmados')
                        ->acceptedFileTypes(['application/pdf'])->maxSize(20480)
                        ->required(),
                    Textarea::make('observaciones')
                        ->label('Observaciones')->rows(2),
                ])
                ->action(function (array $data) {
                    $notaria = $this->record->notaryTracking;
                    if ($notaria) {
                        $notaria->update([
                            'notaria_nombre'        => $data['notaria_nombre'],
                            'notaria_ciudad'        => $data['notaria_ciudad'],
                            'fecha_autenticacion'   => $data['fecha_autenticacion'],
                            'numero_radicado_notaria' => $data['numero_radicado_notaria'] ?? null,
                            'numero_escritura'      => $data['numero_escritura'] ?? null,
                            'valor_autenticacion'   => $data['valor_autenticacion'] ?? null,
                            'recibido_por'          => $data['recibido_por'] ?? null,
                            'path_contrato_firmado' => $data['path_contrato_firmado'] ?? null,
                            'observaciones'         => $data['observaciones'] ?? null,
                            'fecha_regreso'         => now(),
                        ]);
                    }
                    $this->record->update([
                        'estado'      => 'autenticado_notaria',
                        'fecha_firma' => $data['fecha_autenticacion'],
                        'firmado_por' => $this->record->propietario?->nombre_completo,
                    ]);
                    ContractStatusHistory::create([
                        'administration_contract_id' => $this->record->id,
                        'changed_by'      => Auth::id(),
                        'estado_anterior' => 'enviado_notaria',
                        'estado_nuevo'    => 'autenticado_notaria',
                        'canal'           => 'presencial',
                        'razon_cambio'    => 'Autenticado en ' . $data['notaria_nombre'] . ', ' . $data['notaria_ciudad'],
                        'ip_address'      => request()->ip(),
                        'cambiado_en'     => now(),
                    ]);
                    Notification::make()->title('📋 Autenticación registrada — listo para activar')->success()->send();
                    $this->redirect(static::getResource()::getUrl('edit', ['record' => $this->record]));
                });
        }

        // ── 5. Activar contrato — un solo clic ───────────────────
        if ($estado === 'autenticado_notaria') {
            $acciones[] = Action::make('activar_contrato')
                ->label('🟢 Activar contrato')
                ->color('success')
                ->icon('heroicon-o-check-badge')
                ->requiresConfirmation()
                ->modalHeading('¿Activar contrato?')
                ->modalDescription('El inmueble quedará disponible en el módulo de Solicitudes para recibir candidatos.')
                ->modalSubmitActionLabel('Sí, activar')
                ->action(function () {
                    $this->record->update(['estado' => 'activo']);
                    ContractStatusHistory::create([
                        'administration_contract_id' => $this->record->id,
                        'changed_by'      => Auth::id(),
                        'estado_anterior' => 'autenticado_notaria',
                        'estado_nuevo'    => 'activo',
                        'canal'           => 'sistema',
                        'razon_cambio'    => 'Contrato activado — inmueble disponible para arrendar',
                        'ip_address'      => request()->ip(),
                        'cambiado_en'     => now(),
                    ]);
                    Notification::make()->title('🟢 Contrato ACTIVO — Inmueble disponible en Solicitudes')->success()->send();
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

    protected function generarPdfTemporal(): ?string
    {
        try {
            $contract = $this->record->load([
                'property.tipo',
                'property.municipio.departamento',
                'propietario',
                'asesor',
                'clauses' => fn ($q) => $q->orderBy('orden'),
                'template',
            ]);

            $company    = \App\Models\Company::with(['municipio', 'departamento'])->first();
            $logoBase64 = null;

            if ($company?->logo_path) {
                $path = storage_path('app/public/' . $company->logo_path);
                if (file_exists($path)) {
                    $logoBase64 = 'data:' . mime_content_type($path) . ';base64,' . base64_encode(file_get_contents($path));
                }
            }

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.contrato-administracion', [
                'contrato'   => $contract,
                'contract'   => $contract,
                'company'    => $company,
                'logoBase64' => $logoBase64,
            ])->setPaper('letter', 'portrait');

            $tmpPath = storage_path('app/tmp/cad-' . $contract->numero_contrato . '-' . time() . '.pdf');
            if (!is_dir(dirname($tmpPath))) mkdir(dirname($tmpPath), 0755, true);

            file_put_contents($tmpPath, $pdf->output());
            return $tmpPath;

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('CAD PDF temporal error', ['error' => $e->getMessage()]);
            return null;
        }
    }
}

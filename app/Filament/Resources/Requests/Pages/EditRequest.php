<?php
/*
|--------------------------------------------------------------------------
| YarOM ERP - Soluciones de Gestión
|--------------------------------------------------------------------------
| Proyecto privado desarrollado por:
| Ingeniero Jhoan Romero Rivera
| LinkedIn: https://linkedin.com/in/jmromeror87
|
| Módulo: \1
| Archivo: EditRequest.php
| Fecha: CURRENT_DAY/05/2026
| Versión: v1.0
|--------------------------------------------------------------------------
*/
        

namespace App\Filament\Resources\Requests\Pages;

use App\Filament\Resources\Requests\RequestResource;
use App\Models\Request as SolicitudModel;
use App\Models\RequestSuraStudy;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditRequest extends EditRecord
{
    protected static string $resource = RequestResource::class;

    // ── Bloquear edición si está cerrada ──────────────────────
    protected function mutateFormDataBeforeFill(array $data): array
    {
        return $data;
    }

    protected function getHeaderWidgets(): array
    {
        return [];
    }

    // ── Vista personalizada con marca de agua ─────────────────
    public function getView(): string
    {
        $estado = $this->record->estado;
        if (in_array($estado, ['aprobada', 'desistida', 'rechazada'])) {
            return 'filament.requests.edit-readonly';
        }
        return parent::getView();
    }

    protected function getHeaderActions(): array
    {
        $record       = $this->record->load('suraStudies');
        $yaEnvioWA    = $record->suraStudies->where('canal_envio', 'whatsapp')->isNotEmpty();
        $yaEnvioEmail = $record->suraStudies->where('canal_envio', 'email')->isNotEmpty();
        $hayRespuesta = $record->suraStudies->where('resultado_sura', '!=', 'pendiente')->isNotEmpty();
        $estado       = $record->estado;
        $cerrada      = in_array($estado, ['aprobada', 'aprobada_gerente', 'desistida']);
        $rechazada    = $estado === 'rechazada';
        $esPropietario = $record->tipo === 'estudio_propietario';

        $acciones = [];

        // ── Badge de estado final ─────────────────────────────
        if ($cerrada || $rechazada) {
            $acciones[] = Action::make('badge_estado')
                ->label(match($estado) {
                    'aprobada'         => '✅ APROBADA (SURA)',
                    'aprobada_gerente' => '👔 APROBADA POR GERENTE',
                    'rechazada'        => '❌ RECHAZADA',
                    'desistida'        => '🚫 DESISTIDA',
                    default            => '🔒 Cerrada',
                })
                ->color(match($estado) {
                    'aprobada'         => 'success',
                    'aprobada_gerente' => 'success',
                    'rechazada'        => 'danger',
                    'desistida'        => 'gray',
                    default            => 'gray',
                })
                ->disabled();
        }

        // ── Re-aplicar si fue rechazada ───────────────────────
        if ($rechazada) {
            $acciones[] = Action::make('reaplicar')
                ->label('🔄 Nueva aplicación')
                ->color('warning')
                ->icon('heroicon-o-arrow-path')
                ->requiresConfirmation()
                ->modalHeading('¿Reabrir esta solicitud?')
                ->modalDescription('Se creará un nuevo registro de estudio manteniendo el historial completo. El estado volverá a "En estudio".')
                ->modalSubmitActionLabel('Sí, reabrir')
                ->action(function () {
                    $this->record->update([
                        'estado'         => 'en_estudio',
                        'fecha_decision' => null,
                        'decidido_por'   => null,
                    ]);
                    Notification::make()
                        ->title('Solicitud reabierta — estado: En estudio')
                        ->success()->send();
                    $this->redirect(static::getResource()::getUrl('edit', ['record' => $this->record]));
                });
        }

        // ── WhatsApp ──────────────────────────────────────────
        if (!$esPropietario && !$yaEnvioWA && !$cerrada && !$rechazada) {
            $acciones[] = Action::make('enviar_whatsapp_sura')
                ->label('📱 WhatsApp Sura')
                ->color('success')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->form([
                    TextInput::make('telefono_sura')
                        ->label('WhatsApp de Sura')
                        ->default('+573153000000')->required(),
                    TextInput::make('contacto_sura')->label('Contacto en Sura'),
                    Textarea::make('mensaje_enviado')
                        ->label('Mensaje')
                        ->default(fn () => $this->generarMensajeWA())
                        ->rows(7)->required(),
                    TextInput::make('notas')->label('Notas internas'),
                ])
                ->action(function (array $data) {
                    RequestSuraStudy::create([
                        'request_id'      => $this->record->id,
                        'canal_envio'     => 'whatsapp',
                        'fecha_envio'     => now(),
                        'enviado_por'     => Auth::id(),
                        'mensaje_enviado' => $data['mensaje_enviado'],
                        'contacto_sura'   => $data['contacto_sura'] ?? null,
                        'telefono_sura'   => $data['telefono_sura'],
                        'resultado_sura'  => 'pendiente',
                        'notas'           => $data['notas'] ?? null,
                    ]);
                    $this->record->update(['estado' => 'en_estudio']);
                    $numero  = preg_replace('/[^0-9]/', '', $data['telefono_sura']);
                    $mensaje = urlencode($data['mensaje_enviado']);
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
        }

        // ── Email ─────────────────────────────────────────────
        if (!$esPropietario && !$yaEnvioEmail && !$cerrada && !$rechazada) {
            $acciones[] = Action::make('enviar_email_sura')
                ->label('📧 Correo Sura')
                ->color('info')
                ->icon('heroicon-o-envelope')
                ->form([
                    TextInput::make('email_sura')
                        ->label('Correo de Sura')
                        ->email()->default('solicitudes@suramericana.com')->required(),
                    TextInput::make('contacto_sura')->label('Contacto'),
                    Textarea::make('mensaje_enviado')
                        ->label('Cuerpo del correo')
                        ->default(fn () => $this->generarMensajeWA())
                        ->rows(7)->required(),
                    TextInput::make('notas')->label('Notas internas'),
                ])
                ->action(function (array $data) {
                    RequestSuraStudy::create([
                        'request_id'      => $this->record->id,
                        'canal_envio'     => 'email',
                        'fecha_envio'     => now(),
                        'enviado_por'     => Auth::id(),
                        'mensaje_enviado' => $data['mensaje_enviado'],
                        'contacto_sura'   => $data['contacto_sura'] ?? null,
                        'email_sura'      => $data['email_sura'],
                        'resultado_sura'  => 'pendiente',
                        'notas'           => $data['notas'] ?? null,
                    ]);
                    $this->record->update(['estado' => 'en_estudio']);
                    $asunto = urlencode('Solicitud estudio ' . $this->record->numero);
                    $cuerpo = urlencode($data['mensaje_enviado']);
                    Notification::make()->title('Registrado — abriendo correo')->success()->send();
                    $this->redirect("mailto:{$data['email_sura']}?subject={$asunto}&body={$cuerpo}");
                });
        }

        // ── Registrar respuesta Sura ──────────────────────────
        if (!$hayRespuesta && !$cerrada && ($yaEnvioWA || $yaEnvioEmail)) {
            $acciones[] = Action::make('registrar_respuesta_sura')
                ->label('✅ Respuesta Sura')
                ->color('warning')
                ->icon('heroicon-o-clipboard-document-check')
                ->form([
                    TextInput::make('numero_solicitud_sura')
                        ->label('N° Solicitud Sura')->placeholder('Ej: 12958'),
                    Select::make('resultado_sura')
                        ->label('Resultado de Sura')
                        ->options([
                            'aprobada'    => '✅ Aprobada — Asegurables',
                            'rechazada'   => '❌ Rechazada — No asegurables',
                            'condicional' => '⚠️ Condicional',
                        ])->required(),
                    TextInput::make('analista_sura')->label('Analista de Sura'),
                    DateTimePicker::make('fecha_respuesta')
                        ->label('Fecha respuesta')->default(now()),
                    FileUpload::make('path_respuesta')
                        ->label('PDF respuesta Sura')
                        ->disk('public')->directory('solicitudes/sura')
                        ->acceptedFileTypes(['application/pdf','image/jpeg','image/png'])
                        ->maxSize(10240),
                    Textarea::make('observaciones_sura')
                        ->label('Observaciones de Sura')->rows(4),
                ])
                ->action(function (array $data) {
                    $ultimo = $this->record->suraStudies()
                        ->where('resultado_sura', 'pendiente')->latest()->first();
                    $payload = [
                        'numero_solicitud_sura' => $data['numero_solicitud_sura'] ?? null,
                        'resultado_sura'        => $data['resultado_sura'],
                        'analista_sura'         => $data['analista_sura'] ?? null,
                        'fecha_respuesta'       => $data['fecha_respuesta'],
                        'path_respuesta'        => $data['path_respuesta'] ?? null,
                        'observaciones_sura'    => $data['observaciones_sura'] ?? null,
                    ];
                    $ultimo ? $ultimo->update($payload)
                            : RequestSuraStudy::create(array_merge($payload, [
                                'request_id'  => $this->record->id,
                                'canal_envio' => 'presencial',
                                'fecha_envio' => now(),
                                'enviado_por' => Auth::id(),
                            ]));

                    $nuevoEstado = match($data['resultado_sura']) {
                        'aprobada'    => 'aprobada',
                        'rechazada'   => 'rechazada',
                        'condicional' => 'condicional',
                        default       => $this->record->estado,
                    };
                    $this->record->update(['estado' => $nuevoEstado, 'fecha_decision' => now()->toDateString()]);
                    Notification::make()->title('Respuesta Sura registrada — ' . strtoupper($nuevoEstado))->success()->send();
                    $this->redirect(static::getResource()::getUrl('edit', ['record' => $this->record]));
                });
        }

        // ── Historial Sura ────────────────────────────────────
        if ($record->suraStudies->isNotEmpty()) {
            $acciones[] = Action::make('ver_historial_sura')
                ->label('📋 Historial (' . $record->suraStudies->count() . ')')
                ->color('gray')
                ->icon('heroicon-o-clock')
                ->modalHeading('Historial Suramericana — ' . $record->numero)
                ->modalWidth('4xl')
                ->modalContent(fn () => view('filament.requests.historial-sura', [
                    'estudios' => $this->record->suraStudies()->with('enviadoPor')->latest()->get(),
                    'record'   => $this->record,
                ]))
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Cerrar');
        }

        // ── Aprobación directa del gerente ────────────────────
        $puedeAprobarGerente = !$cerrada && !$rechazada
            && !$esPropietario
            && auth()->user()?->hasAnyRole(['super_admin', 'admin', 'gerente']);

        if ($puedeAprobarGerente) {
            $company = \App\Models\Company::first();
            $tarifaDefault = $company?->tarifa_estudio_directo ?? 50000;

            $acciones[] = Action::make('aprobar_gerente')
                ->label('👔 Aprobar (gerente)')
                ->color('success')
                ->icon('heroicon-o-shield-check')
                ->modalHeading('Aprobación directa del gerente')
                ->modalDescription('Esta aprobación no pasa por SURA. Quedará registrado quién aprobó y la justificación.')
                ->form([
                    Textarea::make('justificacion_gerente')
                        ->label('Justificación de aprobación')
                        ->rows(3)->required()
                        ->placeholder('Arrendatario conocido, referencias verificadas, etc.'),
                    TextInput::make('tarifa_estudio_cobrada')
                        ->label('Tarifa de estudio cobrada ($)')
                        ->numeric()->prefix('$')
                        ->default($tarifaDefault)
                        ->helperText("Tarifa directa sin SURA. General: $" . number_format($tarifaDefault, 0, ',', '.')),
                    TextInput::make('decidido_por')
                        ->label('Aprobado por (nombre)')
                        ->default(auth()->user()?->name)
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $this->record->update([
                        'estado'                 => 'aprobada_gerente',
                        'tipo_aprobacion'        => 'gerente_directo',
                        'aprobado_por_id'        => auth()->user()?->getKey(),
                        'justificacion_gerente'  => $data['justificacion_gerente'],
                        'tarifa_estudio_cobrada' => $data['tarifa_estudio_cobrada'] ?? null,
                        'decidido_por'           => $data['decidido_por'],
                        'fecha_decision'         => now()->toDateString(),
                    ]);
                    Notification::make()
                        ->title('Solicitud aprobada directamente por gerencia')
                        ->success()->send();
                    $this->redirect(static::getResource()::getUrl('edit', ['record' => $this->record]));
                });
        }

        if (!$cerrada) {
            $acciones[] = DeleteAction::make()->label('Borrar');
        }

        return $acciones;
    }

    protected function generarMensajeWA(): string
    {
        $r    = $this->record->load(['property', 'thirds.third']);
        $tipo = match($r->tipo) {
            'estudio_arrendatario' => 'ARRENDATARIO',
            'estudio_comprador'    => 'COMPRADOR',
            default                => 'PROPIETARIO',
        };
        $msg  = "Buenos días, somos SERVIARRENDAR S.A.S\n\n";
        $msg .= "Solicitamos estudio de {$tipo}:\n\n";
        $msg .= "📋 N° Solicitud: {$r->numero}\n";
        $msg .= "🏠 Inmueble: {$r->property?->codigo} — {$r->property?->direccion}\n";
        $msg .= "💰 Canon: $" . number_format($r->canon_evaluar ?? 0, 0, ',', '.') . " COP\n\n";
        foreach ($r->thirds as $t) {
            $msg .= "👤 " . ucfirst($t->rol) . ": {$t->third?->nombre_completo} — CC {$t->third?->numero_documento}\n";
            if ($t->ingresos_declarados) $msg .= "   Ingresos: $" . number_format($t->ingresos_declarados, 0, ',', '.') . "\n";
        }
        $msg .= "\nQuedamos atentos a su respuesta.\nGracias.";
        return $msg;
    }
}

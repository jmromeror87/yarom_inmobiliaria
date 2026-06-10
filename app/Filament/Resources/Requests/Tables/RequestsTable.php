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
| Archivo: RequestsTable.php
| Fecha: CURRENT_DAY/05/2026
| Versión: v1.0
|--------------------------------------------------------------------------
*/
            

namespace App\Filament\Resources\Requests\Tables;

use App\Models\RequestSuraStudy;
use App\Services\WhatsAppService;
use Filament\Actions\Action;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class RequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('numero')
                    ->label('N° Solicitud')
                    ->searchable()->sortable()
                    ->weight('bold')->color('primary')
                    ->description(fn ($record) => $record->fecha_radicacion?->format('d/m/Y')),

                TextColumn::make('tipo')
                    ->label('Tipo')->badge()
                    ->color(fn ($state) => match($state) {
                        'estudio_propietario'  => 'info',
                        'estudio_arrendatario' => 'success',
                        'estudio_comprador'    => 'warning',
                        default                => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match($state) {
                        'estudio_propietario'  => 'Propietario',
                        'estudio_arrendatario' => 'Arrendatario',
                        'estudio_comprador'    => 'Comprador',
                        default                => $state,
                    }),

                TextColumn::make('property.codigo')
                    ->label('Inmueble')
                    ->description(fn ($record) => $record->property?->direccion)
                    ->searchable(),

                TextColumn::make('estado')
                    ->label('Estado')->badge()
                    ->color(fn ($state) => match($state) {
                        'aprobada'         => 'success',
                        'aprobada_gerente' => 'success',
                        'rechazada'        => 'danger',
                        'condicional'      => 'warning',
                        'en_estudio'       => 'info',
                        'radicada'         => 'gray',
                        'desistida'        => 'gray',
                        default            => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match($state) {
                        'radicada'         => 'Radicada',
                        'en_estudio'       => 'En estudio',
                        'aprobada'         => 'Aprobada (SURA)',
                        'aprobada_gerente' => 'Aprobada gerente',
                        'condicional'      => 'Condicional',
                        'rechazada'        => 'Rechazada',
                        'desistida'        => 'Desistida',
                        default            => $state,
                    }),

                TextColumn::make('thirds_count')
                    ->label('Terceros')
                    ->counts('thirds')
                    ->badge()->color('gray'),

                TextColumn::make('asesor.name')
                    ->label('Asesor')->searchable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->filters([
                SelectFilter::make('tipo')->label('Tipo')
                    ->options([
                        'estudio_propietario'  => 'Estudio propietario',
                        'estudio_arrendatario' => 'Estudio arrendatario',
                        'estudio_comprador'    => 'Estudio comprador',
                    ]),
                SelectFilter::make('estado')->label('Estado')
                    ->options([
                        'radicada'         => 'Radicada',
                        'en_estudio'       => 'En estudio',
                        'aprobada'         => 'Aprobada (SURA)',
                        'aprobada_gerente' => 'Aprobada por gerente',
                        'condicional'      => 'Condicional',
                        'rechazada'        => 'Rechazada',
                        'desistida'        => 'Desistida',
                    ]),
            ])
            ->recordActions([
                Action::make('enviar_sudamericana')
                    ->label('Enviar a Sura')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('info')
                    ->outlined()
                    ->visible(fn ($record) => in_array($record->estado, ['radicada', 'en_estudio']))
                    ->form([
                        TextInput::make('telefono_sura')
                            ->label('WhatsApp del asesor de Sudamericana')
                            ->placeholder('Ej: 3001234567')
                            ->tel()
                            ->required(),
                        TextInput::make('contacto_sura')
                            ->label('Nombre del contacto (opcional)')
                            ->placeholder('Ej: Carlos Pérez'),
                    ])
                    ->action(function ($record, array $data) {
                        $study = RequestSuraStudy::create([
                            'request_id'    => $record->id,
                            'canal_envio'   => 'whatsapp',
                            'fecha_envio'   => now(),
                            'enviado_por'   => Auth::id(),
                            'contacto_sura' => $data['contacto_sura'] ?? null,
                            'telefono_sura' => $data['telefono_sura'],
                            'resultado_sura'=> 'pendiente',
                        ]);

                        $token = $study->generateToken();
                        $url   = route('estudio.show', $token);

                        $inmueble = $record->property?->codigo . ' — ' . $record->property?->direccion;
                        $canon    = '$' . number_format($record->canon_evaluar ?? 0, 0, ',', '.');
                        $empresa  = \App\Models\Company::first()?->razon_social ?? 'Serviarrendar S.A.S';

                        $msg = "📋 *Solicitud de Estudio de Crédito*\n\n"
                            . ($data['contacto_sura'] ? "Estimad@ {$data['contacto_sura']},\n\n" : '')
                            . "Le enviamos la solicitud *{$record->numero}* para evaluación.\n\n"
                            . "🏠 Inmueble: {$inmueble}\n"
                            . "💰 Canon: {$canon} COP\n"
                            . "👥 Solicitantes: {$record->thirds()->count()} persona(s)\n\n"
                            . "🔗 *Registrar respuesta aquí:*\n{$url}\n\n"
                            . "El link permanecerá activo hasta que registre la decisión.\n"
                            . "— {$empresa}";

                        $study->update(['mensaje_enviado' => $msg]);
                        $record->update(['estado' => 'en_estudio']);

                        app(WhatsAppService::class)->enviar($data['telefono_sura'], $msg);

                        Notification::make()
                            ->title('Link enviado a Sudamericana')
                            ->body("Enviado a {$data['telefono_sura']}")
                            ->success()->send();
                    }),

                EditAction::make()
                    ->label('Editar')
                    ->icon('heroicon-o-pencil-square')
                    ->outlined()
                    ->hidden(fn ($record) => in_array($record->estado, ['aprobada', 'aprobada_gerente', 'desistida'])),

                Action::make('ver')
                    ->label('Ver')
                    ->icon('heroicon-o-eye')
                    ->outlined()
                    ->color('gray')
                    ->url(fn ($record) => \App\Filament\Resources\Requests\RequestResource::getUrl('edit', ['record' => $record]))
                    ->visible(fn ($record) => in_array($record->estado, ['aprobada', 'aprobada_gerente', 'desistida'])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Eliminar seleccionadas')
                        ->requiresConfirmation()
                        ->modalHeading('¿Eliminar solicitudes seleccionadas?')
                        ->modalDescription('Solo administradores pueden realizar esta acción.')
                        ->modalSubmitActionLabel('Sí, eliminar')
                        ->visible(fn () => auth()->user()?->hasAnyRole(['super_admin', 'admin'])),
                ]),
            ]);
    }
}

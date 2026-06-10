<?php

namespace App\Filament\Resources\RentalContracts\RelationManagers;

use App\Models\ContractAmendment;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AmendmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'amendments';
    protected static ?string $title = 'Otrosíes';

    public function form(Schema $schema): Schema
    {
        return $schema->components([

            Select::make('tipo')
                ->label('Tipo de modificación')
                ->options(ContractAmendment::TIPOS)
                ->required()
                ->live(),

            TextInput::make('titulo')
                ->label('Título del otrosí')
                ->required()
                ->maxLength(200)
                ->placeholder('Ej: Incremento de canon período 2027'),

            // Campos condicionales según tipo
            TextInput::make('valor_anterior')
                ->label('Valor anterior')
                ->numeric()
                ->prefix('$')
                ->visible(fn(Get $get) => in_array($get('tipo'), ['incremento_canon', 'cambio_comision'])),

            TextInput::make('valor_nuevo')
                ->label('Valor nuevo')
                ->numeric()
                ->prefix('$')
                ->visible(fn(Get $get) => in_array($get('tipo'), ['incremento_canon', 'cambio_comision'])),

            DatePicker::make('fecha_fin_anterior')
                ->label('Fecha fin actual del contrato')
                ->visible(fn(Get $get) => $get('tipo') === 'prorroga'),

            DatePicker::make('fecha_fin_nueva')
                ->label('Nueva fecha fin (prórroga hasta)')
                ->visible(fn(Get $get) => $get('tipo') === 'prorroga'),

            Textarea::make('texto_anterior')
                ->label('Texto anterior de la cláusula')
                ->rows(3)
                ->visible(fn(Get $get) => in_array($get('tipo'), ['modificacion_clausula', 'cesion_arrendatario', 'cambio_codeudor', 'adicion_areas', 'otro'])),

            Textarea::make('texto_nuevo')
                ->label('Nuevo texto de la cláusula')
                ->rows(3)
                ->visible(fn(Get $get) => in_array($get('tipo'), ['modificacion_clausula', 'cesion_arrendatario', 'cambio_codeudor', 'adicion_areas', 'otro'])),

            Textarea::make('descripcion')
                ->label('Descripción / Justificación')
                ->required()
                ->rows(3)
                ->placeholder('Motivo y alcance de la modificación...'),

            Textarea::make('clausula_modificada')
                ->label('Redacción de la cláusula modificada')
                ->rows(4)
                ->placeholder('Texto completo tal como quedará en el contrato...'),

            DatePicker::make('fecha_firma')
                ->label('Fecha de firma')
                ->required()
                ->default(today()),

            DatePicker::make('fecha_vigencia')
                ->label('Fecha de vigencia')
                ->required()
                ->default(today()),

            Select::make('estado')
                ->label('Estado')
                ->options([
                    'borrador' => 'Borrador',
                    'firmado'  => 'Firmado',
                    'anulado'  => 'Anulado',
                ])
                ->default('borrador')
                ->required(),

            TextInput::make('firmado_por_arrendador')->label('Firma arrendador'),
            TextInput::make('firmado_por_arrendatario')->label('Firma arrendatario'),
            TextInput::make('firmado_por_garante')->label('Firma garante / codeudor'),

            Toggle::make('aplica_cambio_automatico')
                ->label('Aplicar cambio automáticamente al firmar')
                ->helperText('Al marcar como Firmado, el sistema actualiza el contrato.')
                ->default(true),

            Textarea::make('notas')->label('Notas internas')->rows(2),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('numero')
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->columns([
                TextColumn::make('numero')
                    ->label('N°')
                    ->badge()
                    ->color('primary')
                    ->fontFamily('mono')
                    ->searchable(),

                TextColumn::make('tipo_label')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn($record) => match ($record->tipo) {
                        'incremento_canon'      => 'success',
                        'prorroga'              => 'info',
                        'cesion_arrendatario'   => 'warning',
                        'cambio_codeudor'       => 'gray',
                        'modificacion_clausula' => 'primary',
                        default                 => 'gray',
                    }),

                TextColumn::make('titulo')
                    ->label('Título')
                    ->limit(40)
                    ->searchable(),

                TextColumn::make('fecha_firma')
                    ->label('Firmado')
                    ->date('d/m/Y'),

                TextColumn::make('fecha_vigencia')
                    ->label('Vigencia')
                    ->date('d/m/Y'),

                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'borrador' => 'warning',
                        'firmado'  => 'success',
                        'anulado'  => 'danger',
                    })
                    ->formatStateUsing(fn($state) => match ($state) {
                        'borrador' => 'Borrador',
                        'firmado'  => 'Firmado',
                        'anulado'  => 'Anulado',
                    }),

                IconColumn::make('cambio_aplicado')
                    ->label('Aplicado')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-clock')
                    ->trueColor('success')
                    ->falseColor('gray'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Nuevo otrosí')
                    ->icon('heroicon-o-plus')
                    ->slideOver()
                    ->extraModalWindowAttributes(['style' => 'padding-bottom:80px;'])
                    ->extraAttributes([
                        'style' => 'background:linear-gradient(135deg,#1e3a8a,#E11D48)!important;color:#fff!important;border:none!important;font-weight:700!important;',
                    ]),
            ])
            ->recordActions([
                EditAction::make()->outlined()->label('Editar')
                    ->slideOver()
                    ->extraModalWindowAttributes(['style' => 'padding-bottom:80px;']),

                Action::make('firmar')
                    ->label('Firmar')
                    ->icon('heroicon-o-pencil-square')
                    ->outlined()
                    ->color('success')
                    ->visible(fn($record) => $record->estado === 'borrador')
                    ->requiresConfirmation()
                    ->modalHeading('Confirmar firma del otrosí')
                    ->modalDescription('Al firmar, el cambio se aplicará automáticamente al contrato si está configurado así.')
                    ->action(function ($record) {
                        $record->update(['estado' => 'firmado']);
                        Notification::make()
                            ->title('Otrosí firmado correctamente')
                            ->body($record->cambio_aplicado ? 'El cambio fue aplicado al contrato.' : '')
                            ->success()->send();
                    }),

                Action::make('anular')
                    ->label('Anular')
                    ->icon('heroicon-o-x-circle')
                    ->outlined()
                    ->color('danger')
                    ->visible(fn($record) => $record->estado !== 'anulado')
                    ->requiresConfirmation()
                    ->action(fn($record) => $record->update(['estado' => 'anulado'])),

                DeleteAction::make()->outlined()->color('danger'),
            ]);
    }
}

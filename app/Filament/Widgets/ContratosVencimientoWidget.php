<?php

namespace App\Filament\Widgets;

use App\Models\AdministrationContract;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Carbon\Carbon;

class ContratosVencimientoWidget extends BaseWidget
{
    protected static ?string $heading = '⚠️ Contratos Próximos a Vencer (90 días)';
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 8;
    protected static ?int $defaultPaginationPageOption = 5;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                AdministrationContract::query()
                    ->with(['property', 'propietario'])
                    ->whereNull('deleted_at')
                    ->where('estado', 'activo')
                    ->whereBetween('fecha_fin', [now(), now()->addDays(90)])
                    ->orderBy('fecha_fin', 'asc')
            )
            ->columns([
                Tables\Columns\TextColumn::make('numero_contrato')
                    ->label('N° Contrato')
                    ->weight('bold')
                    ->color('primary')
                    ->searchable(),

                Tables\Columns\TextColumn::make('propietario.nombre_completo')
                    ->label('Propietario')
                    ->searchable()
                    ->limit(25),

                Tables\Columns\TextColumn::make('property.direccion')
                    ->label('Inmueble')
                    ->limit(30),

                Tables\Columns\TextColumn::make('canon_pactado')
                    ->label('Canon')
                    ->money('COP', 0)
                    ->alignRight(),

                Tables\Columns\TextColumn::make('fecha_fin')
                    ->label('Vence')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('dias_restantes')
                    ->label('Días')
                    ->state(fn ($record) => now()->diffInDays($record->fecha_fin, false))
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        $state <= 15  => 'danger',
                        $state <= 30  => 'warning',
                        default       => 'info',
                    })
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('renovacion')
                    ->label('Renovación')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'automatica' ? 'success' : 'gray'),
            ])
            ->emptyStateHeading('Sin contratos por vencer')
            ->emptyStateDescription('No hay contratos que venzan en los próximos 90 días.')
            ->emptyStateIcon('heroicon-o-check-circle')
            ->paginated([5, 10]);
    }
}
<?php

namespace App\Filament\Resources\ContractTemplates\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ContractTemplateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Datos de la plantilla')
                ->icon('heroicon-o-document-duplicate')
                ->columns(2)
                ->schema([
                    TextInput::make('nombre')
                        ->label('Nombre de la plantilla')
                        ->required()->columnSpanFull()
                        ->placeholder('Contrato de Administración — Arriendo'),

                    Select::make('tipo_contrato')
                        ->label('Tipo de contrato')
                        ->options([
                            'administracion_arriendo' => 'Administración — Arriendo',
                            'administracion_venta'    => 'Administración — Venta',
                            'arrendamiento_vivienda'  => 'Arrendamiento vivienda (Ley 820)',
                            'arrendamiento_comercial' => 'Arrendamiento comercial',
                        ])->required()->default('administracion_arriendo'),

                    Toggle::make('is_default')
                        ->label('Plantilla por defecto')
                        ->helperText('Se usará automáticamente al crear contratos de este tipo'),

                    Toggle::make('is_active')
                        ->label('Activa')->default(true),
                ]),

            Section::make('Encabezado y pie de página')
                ->icon('heroicon-o-document-text')
                ->collapsible()
                ->schema([
                    Textarea::make('encabezado')
                        ->label('Encabezado del contrato')
                        ->rows(3)
                        ->helperText('Texto que aparece al inicio del contrato'),
                    Textarea::make('pie_pagina')
                        ->label('Pie de página')
                        ->rows(3)
                        ->helperText('Texto que aparece al final del contrato'),
                ]),

            Section::make('Cláusulas')
                ->icon('heroicon-o-list-bullet')
                ->description('Administre las cláusulas de esta plantilla')
                ->schema([
                    \Filament\Forms\Components\Repeater::make('clauses')
                        ->label('Cláusulas del contrato')
                        ->relationship()
                        ->schema([
                            TextInput::make('numero')
                                ->label('N° / Nombre')
                                ->placeholder('PRIMERO, SEGUNDA, PARÁGRAFO...')
                                ->required()->columnSpan(2),

                            Select::make('tipo')
                                ->label('Tipo')
                                ->options([
                                    'clausula'     => 'Cláusula',
                                    'paragrafo'    => 'Parágrafo',
                                    'considerando' => 'Considerando',
                                    'nota'         => 'Nota',
                                ])->default('clausula')->required(),

                            TextInput::make('titulo')
                                ->label('Título de la cláusula')
                                ->required()->columnSpan(2),

                            TextInput::make('orden')
                                ->label('Orden')->numeric()->default(0),

                            \Filament\Forms\Components\Textarea::make('contenido')
                                ->label('Contenido')
                                ->rows(5)->required()->columnSpanFull(),

                            Toggle::make('es_editable')
                                ->label('Editable en contrato')->default(true),

                            Toggle::make('es_obligatoria')
                                ->label('Obligatoria')->default(true),

                            Toggle::make('is_active')
                                ->label('Activa')->default(true),
                        ])
                        ->columns(3)
                        ->reorderable('orden')
                        ->reorderableWithButtons()
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string =>
                            ($state['numero'] ?? '') . ' — ' . ($state['titulo'] ?? 'Nueva cláusula')
                        )
                        ->addActionLabel('+ Agregar cláusula')
                        ->defaultItems(0)
                        ->columnSpanFull(),
                ]),
        ]);
    }
}

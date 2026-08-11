<?php

namespace App\Filament\Resources\OwnerLiquidations\Schemas;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class OwnerLiquidationForm
{
    private const ESTADO_INFO = [
        'pendiente' => ['label' => 'Pendiente', 'bg' => '#fef3c7', 'fg' => '#b45309'],
        'aprobada'  => ['label' => 'Aprobada',  'bg' => '#dbeafe', 'fg' => '#2563eb'],
        'pagada'    => ['label' => 'Pagada',    'bg' => '#d1fae5', 'fg' => '#059669'],
        'anulada'   => ['label' => 'Anulada',   'bg' => '#f1f5f9', 'fg' => '#64748b'],
    ];

    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            // ── Resumen ejecutivo ─────────────────────────────────────
            Placeholder::make('resumen_ejecutivo')
                ->hiddenLabel()
                ->columnSpanFull()
                ->content(function ($record) {
                    if (! $record) return '';

                    $info = self::ESTADO_INFO[$record->estado] ?? self::ESTADO_INFO['pendiente'];
                    $periodo = ($record->periodo_inicio?->format('d M') ?? '—') . ' — ' . ($record->periodo_fin?->format('d M Y') ?? '—');

                    $tiles = [
                        ['label' => 'Liquidación', 'value' => e($record->numero), 'sub' => $info['label'], 'subColor' => $info['fg'], 'subBg' => $info['bg']],
                        ['label' => 'Propietario', 'value' => e($record->propietario?->nombre_completo ?? '—'), 'sub' => null],
                        ['label' => 'Inmueble', 'value' => e($record->property?->codigo ?? '—'), 'sub' => e($record->property?->direccion ?? '')],
                        ['label' => 'Período', 'value' => $periodo, 'sub' => null],
                    ];

                    $tilesHtml = '';
                    foreach ($tiles as $t) {
                        $tilesHtml .= '<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:12px 16px;flex:1;min-width:150px;">'
                            . '<div style="font-size:10px;font-weight:800;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px;">' . $t['label'] . '</div>'
                            . '<div style="font-size:14px;font-weight:800;color:#0F172A;line-height:1.3;">' . $t['value'] . '</div>'
                            . (isset($t['sub']) && $t['sub'] ? '<div style="margin-top:4px;">'
                                . (isset($t['subBg'])
                                    ? '<span style="font-size:10px;font-weight:800;padding:2px 8px;border-radius:20px;background:' . $t['subBg'] . ';color:' . $t['subColor'] . ';">' . $t['sub'] . '</span>'
                                    : '<span style="font-size:11px;color:#64748b;font-weight:600;">' . $t['sub'] . '</span>')
                                . '</div>' : '')
                            . '</div>';
                    }

                    return new \Illuminate\Support\HtmlString(
                        '<div style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:4px;font-family:\'Plus Jakarta Sans\',sans-serif;">'
                        . $tilesHtml
                        . '<div style="background:linear-gradient(135deg,#0F172A,#1e3a5f);border-radius:12px;padding:12px 20px;flex:1.3;min-width:200px;display:flex;flex-direction:column;justify-content:center;">'
                        . '<div style="font-size:10px;font-weight:800;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px;">Total a girar</div>'
                        . '<div style="font-size:22px;font-weight:900;color:#fff;letter-spacing:-.02em;">$' . number_format((float) $record->total_giro, 0, ',', '.') . '</div>'
                        . '</div>'
                        . '</div>'
                    );
                }),

            Placeholder::make('banner_anulada')
                ->hiddenLabel()
                ->columnSpanFull()
                ->visible(fn ($record) => $record && $record->estado === 'anulada')
                ->content(function ($record) {
                    if (! $record) return '';

                    return new \Illuminate\Support\HtmlString(
                        '<div style="background:#f8fafc;border:1px solid #cbd5e1;border-radius:12px;padding:14px 18px;font-size:12.5px;font-family:\'Plus Jakarta Sans\',sans-serif;">'
                        . '<div style="font-weight:800;color:#334155;margin-bottom:4px;">🚫 Liquidación anulada por ' . e($record->anuladoPor?->name ?? 'sistema') . ' el ' . ($record->anulado_en?->format('d/m/Y h:i A') ?? '—') . '</div>'
                        . '<div style="color:#64748b;">Motivo: ' . e($record->motivo_anulacion ?? '—') . '</div>'
                        . '</div>'
                    );
                }),

            Section::make('Información del período')
                ->icon('heroicon-o-calendar')
                ->columnSpanFull()
                ->columns(3)
                ->schema([
                    TextInput::make('numero')
                        ->label('N° Liquidación')->disabled(),

                    Select::make('rental_contract_id')
                        ->label('Contrato de arriendo')
                        ->relationship('rentalContract', 'numero_contrato')
                        ->disabled()->columnSpan(2),

                    Select::make('propietario_id')
                        ->label('Propietario')
                        ->relationship('propietario', 'nombre_completo')
                        ->getOptionLabelFromRecordUsing(fn ($record) => trim($record->nombre_completo ?: $record->razon_social ?: trim(($record->primer_nombre ?? '') . ' ' . ($record->primer_apellido ?? ''))) ?: "Tercero #{$record->id} (sin nombre)")
                        ->disabled()->columnSpan(2),

                    Select::make('property_id')
                        ->label('Inmueble')
                        ->relationship('property', 'direccion')
                        ->disabled(),

                    DatePicker::make('periodo_inicio')
                        ->label('Inicio del período')
                        ->native(false)
                        ->helperText('Corrígelo si no coincide con el período real de la factura del inquilino.'),

                    DatePicker::make('periodo_fin')
                        ->label('Fin del período')
                        ->native(false),

                    Select::make('estado')
                        ->label('Estado')
                        ->options([
                            'pendiente' => 'Pendiente',
                            'aprobada'  => 'Aprobada',
                            'pagada'    => 'Pagada',
                            'anulada'   => 'Anulada',
                        ])
                        ->live()
                        ->required(),
                ]),

            Section::make('Liquidación económica')
                ->icon('heroicon-o-banknotes')
                ->columnSpanFull()
                ->description('Corrige estos valores solo si la liquidación quedó mal calculada — el total a girar se recalcula automáticamente al guardar.')
                ->columns(2)
                ->schema([
                    // ── Cascada visual: canon → deducciones → total ──────
                    Placeholder::make('cascada_financiera')
                        ->hiddenLabel()
                        ->columnSpanFull()
                        ->content(function (Get $get, $record) {
                            $canon = (float) ($get('canon_cobrado') ?? $record?->canon_cobrado ?? 0);
                            $comision = (float) ($get('comision_valor') ?? $record?->comision_valor ?? 0);
                            $iva = (float) ($get('iva_comision') ?? $record?->iva_comision ?? 0);
                            $rete = (float) ($get('retefuente_valor') ?? $record?->retefuente_valor ?? 0);
                            $desc = (float) ($get('otros_descuentos') ?? 0);
                            $total = max(0, $canon - $comision - $iva - $rete - $desc);

                            $filas = [
                                ['label' => 'Canon cobrado', 'valor' => $canon, 'signo' => ''],
                                ['label' => 'Comisión administración', 'valor' => $comision, 'signo' => '−'],
                                ['label' => 'IVA sobre comisión', 'valor' => $iva, 'signo' => '−'],
                                ['label' => 'Retención en la fuente', 'valor' => $rete, 'signo' => '−'],
                                ['label' => 'Otros descuentos', 'valor' => $desc, 'signo' => '−'],
                            ];

                            $filasHtml = '';
                            foreach ($filas as $f) {
                                $color = $f['signo'] === '−' && $f['valor'] > 0 ? '#dc2626' : '#0F172A';
                                $filasHtml .= '<div style="display:flex;justify-content:space-between;padding:7px 0;border-bottom:1px dashed #e2e8f0;font-size:12.5px;">'
                                    . '<span style="color:#64748b;font-weight:600;">' . $f['label'] . '</span>'
                                    . '<span style="font-weight:700;color:' . $color . ';">' . $f['signo'] . '$' . number_format($f['valor'], 0, ',', '.') . '</span>'
                                    . '</div>';
                            }

                            return new \Illuminate\Support\HtmlString(
                                '<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:14px 18px;margin-bottom:6px;font-family:\'Plus Jakarta Sans\',sans-serif;">'
                                . $filasHtml
                                . '<div style="display:flex;justify-content:space-between;align-items:center;margin-top:10px;padding-top:10px;">'
                                . '<span style="font-size:12px;font-weight:800;color:#0F172A;text-transform:uppercase;letter-spacing:.04em;">Total a girar</span>'
                                . '<span style="font-size:20px;font-weight:900;color:#059669;letter-spacing:-.01em;">$' . number_format($total, 0, ',', '.') . '</span>'
                                . '</div>'
                                . '</div>'
                            );
                        }),

                    TextInput::make('canon_cobrado')
                        ->label('Canon cobrado al inquilino')
                        ->prefix('$')->numeric()->live(onBlur: true),

                    TextInput::make('comision_porcentaje')
                        ->label('Comisión administración')
                        ->suffix('%')->numeric()->live(onBlur: true),

                    TextInput::make('comision_valor')
                        ->label('Valor comisión')
                        ->prefix('$')->numeric()->live(onBlur: true),

                    TextInput::make('iva_comision')
                        ->label('IVA sobre comisión (19%)')
                        ->prefix('$')->numeric()->live(onBlur: true),

                    TextInput::make('retefuente_valor')
                        ->label('Retefuente')
                        ->prefix('$')->numeric()->live(onBlur: true)
                        ->helperText('Solo aplica si el arrendatario es persona jurídica'),

                    TextInput::make('seguro_sura_deducido')
                        ->label('🛡️ Seguro SURA (pagado a ASURA)')
                        ->prefix('$')->numeric()->live(onBlur: true)
                        ->helperText('Base + IVA cobrado al inquilino — la inmobiliaria lo transfiere a ASURA')
                        ->visible(fn ($record) => $record && (float)$record->seguro_sura_deducido > 0),

                    TextInput::make('otros_descuentos')
                        ->label('Otros descuentos')
                        ->prefix('$')->numeric()->default(0)
                        ->live()
                        ->helperText('Reparaciones, deudas u otros cargos al propietario'),

                    Textarea::make('descripcion_descuentos')
                        ->label('Descripción de descuentos')
                        ->rows(2)->columnSpanFull()
                        ->visible(fn (Get $get) => (float)$get('otros_descuentos') > 0),
                ]),

            Section::make('Giro al propietario')
                ->icon('heroicon-o-building-library')
                ->columnSpanFull()
                ->columns(2)
                ->schema([
                    Placeholder::make('estado_giro')
                        ->hiddenLabel()
                        ->columnSpanFull()
                        ->content(function ($record) {
                            if (! $record) return '';
                            $pagada = $record->estado === 'pagada';
                            $bg = $pagada ? '#f0fdf4' : '#f8fafc';
                            $border = $pagada ? '#bbf7d0' : '#e2e8f0';
                            $fg = $pagada ? '#166534' : '#64748b';
                            $texto = $pagada
                                ? '✓ Giro realizado' . ($record->fecha_giro ? ' el ' . $record->fecha_giro->format('d/m/Y') : '')
                                : '● Giro pendiente';

                            return new \Illuminate\Support\HtmlString(
                                '<div style="background:' . $bg . ';border:1px solid ' . $border . ';border-radius:10px;padding:9px 14px;font-size:12.5px;font-weight:700;color:' . $fg . ';display:inline-flex;align-items:center;gap:6px;font-family:\'Plus Jakarta Sans\',sans-serif;">'
                                . $texto
                                . '</div>'
                            );
                        }),

                    DatePicker::make('fecha_giro')
                        ->label('Fecha del giro'),

                    Select::make('forma_giro')
                        ->label('Forma de giro')
                        ->options([
                            'transferencia' => 'Transferencia bancaria',
                            'consignacion'  => 'Consignación',
                            'cheque'        => 'Cheque',
                            'efectivo'      => 'Efectivo',
                        ])
                        ->live(),

                    Select::make('banco_giro_id')
                        ->label('Cuenta de la que salió el dinero')
                        ->options(fn () => \App\Models\Bank::where('is_active', true)
                            ->where('tipo_cuenta', '!=', 'caja')
                            ->get()
                            ->mapWithKeys(fn ($b) => [$b->id => $b->nombre . ($b->numero_cuenta ? " — {$b->numero_cuenta}" : '')]))
                        ->searchable()
                        ->visible(fn (Get $get) => $get('forma_giro') !== 'efectivo')
                        ->helperText('De qué cuenta (Bancolombia, Crediservir, etc.) realmente salió la plata.'),

                    TextInput::make('referencia_giro')
                        ->label('N° transacción / referencia')->columnSpanFull(),

                    FileUpload::make('comprobante_giro_path')
                        ->label('Comprobante del giro')
                        ->disk('public')->directory('liquidaciones/comprobantes')
                        ->acceptedFileTypes(['image/*', 'application/pdf'])
                        ->maxSize(5120)->columnSpanFull(),

                    Textarea::make('notas')
                        ->label('Notas internas')->rows(3)->columnSpanFull(),

                    Placeholder::make('resumen_confirmacion_giro')
                        ->label('')
                        ->columnSpanFull()
                        ->visible(fn (Get $get) => $get('estado') === 'pagada')
                        ->content(function (Get $get, $record) {
                            $totalGiro = max(0,
                                (float) ($record?->canon_cobrado ?? 0)
                                - (float) ($record?->comision_valor ?? 0)
                                - (float) ($record?->iva_comision ?? 0)
                                - (float) ($record?->retefuente_valor ?? 0)
                                - (float) ($get('otros_descuentos') ?? 0)
                            );
                            $formas = [
                                'transferencia' => 'Transferencia bancaria', 'consignacion' => 'Consignación',
                                'cheque' => 'Cheque', 'efectivo' => 'Efectivo',
                            ];
                            $forma = $formas[$get('forma_giro') ?? ''] ?? '—';
                            $fecha = $get('fecha_giro') ? \Carbon\Carbon::parse($get('fecha_giro'))->format('d/m/Y') : '—';

                            return new \Illuminate\Support\HtmlString(
                                '<div style="background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:12px 14px;font-size:13px;">'
                                . '⚠️ <strong>Estás a punto de marcar esta liquidación como pagada:</strong>'
                                . '<div style="margin-top:6px;line-height:1.6;">'
                                . '💰 Total a girar: <strong>$' . number_format($totalGiro, 0, ',', '.') . '</strong><br>'
                                . '📅 Fecha del giro: <strong>' . $fecha . '</strong><br>'
                                . '🏦 Forma de giro: <strong>' . e($forma) . '</strong><br>'
                                . '👤 Propietario: <strong>' . e($record?->propietario?->nombre_completo) . '</strong>'
                                . '</div>'
                                . '<div style="margin-top:8px;font-style:italic;color:#92400e;">Verifica los datos antes de guardar — una vez pagada, la liquidación queda bloqueada para edición.</div>'
                                . '</div>'
                            );
                        }),
                    Checkbox::make('confirmo_giro')
                        ->label('Confirmo que los datos son correctos y entiendo que esta acción no se puede reversar.')
                        ->columnSpanFull()
                        ->visible(fn (Get $get) => $get('estado') === 'pagada')
                        ->required(fn (Get $get) => $get('estado') === 'pagada')
                        ->accepted(fn (Get $get) => $get('estado') === 'pagada')
                        ->dehydrated(false),
                ]),

            Section::make('Actividad de la liquidación')
                ->icon('heroicon-o-clock')
                ->columnSpanFull()
                ->collapsed()
                ->schema([
                    Placeholder::make('timeline_actividad')
                        ->hiddenLabel()
                        ->columnSpanFull()
                        ->content(function ($record) {
                            if (! $record) return '';

                            $historial = $record->statusHistories()->with('usuario')->get();

                            if ($historial->isEmpty()) {
                                return new \Illuminate\Support\HtmlString(
                                    '<div style="font-size:12.5px;color:#94a3b8;">Sin cambios de estado registrados todavía.</div>'
                                );
                            }

                            $filas = '';
                            foreach ($historial as $h) {
                                $info = self::ESTADO_INFO[$h->estado_nuevo] ?? ['label' => $h->estado_nuevo, 'bg' => '#f1f5f9', 'fg' => '#64748b'];
                                $filas .= '<div style="display:flex;gap:12px;padding:9px 0;border-bottom:1px solid #f1f5f9;">'
                                    . '<div style="width:8px;height:8px;border-radius:50%;background:' . $info['fg'] . ';margin-top:5px;flex-shrink:0;"></div>'
                                    . '<div>'
                                    . '<div style="font-size:12.5px;font-weight:700;color:#0F172A;">' . e($h->estado_anterior_label) . ' → <span style="color:' . $info['fg'] . ';">' . e($h->estado_nuevo_label) . '</span></div>'
                                    . '<div style="font-size:11px;color:#94a3b8;margin-top:2px;">' . $h->cambiado_en?->format('d M Y, h:i A') . ' · ' . e($h->usuario?->name ?? 'Sistema') . '</div>'
                                    . '</div>'
                                    . '</div>';
                            }

                            return new \Illuminate\Support\HtmlString('<div style="font-family:\'Plus Jakarta Sans\',sans-serif;">' . $filas . '</div>');
                        }),
                ]),

        ]);
    }
}

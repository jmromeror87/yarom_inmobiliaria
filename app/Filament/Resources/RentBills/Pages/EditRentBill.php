<?php

namespace App\Filament\Resources\RentBills\Pages;

use App\Filament\Resources\RentBills\RentBillResource;
use App\Models\Bank;
use App\Models\Company;
use App\Models\RentPayment;
use App\Helpers\WhatsApp;
use App\Services\WhatsAppService;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditRentBill extends EditRecord
{
    protected static string $resource = RentBillResource::class;

    public function getView(): string
    {
        return 'filament.rent-bills.edit-invoice';
    }

    protected function getHeaderActions(): array
    {
        $record   = $this->record->load(['arrendatario','rentalContract','payments','property']);
        $acciones = [];

        // ── Badge pagada ───────────────────────────────────
        if ($record->estado === 'pagada') {
            $acciones[] = Action::make('badge_pagada')
                ->label('✅ PAGADA')->color('success')->disabled();
        }

        // ── Ajustes (mora y saldo arrastrado) ──────────────
        if (!in_array($record->estado, ['pagada', 'anulada'])) {
            $acciones[] = Action::make('ajustes')
                ->label('⚙️ Ajustes')
                ->color('gray')
                ->outlined()
                ->icon('heroicon-o-adjustments-horizontal')
                ->modalHeading('Ajustes de la factura')
                ->modalDescription('Decide si esta factura puntual aplica mora, si arrastra saldo pendiente de un periodo anterior, y corrige el periodo de arriendo si quedó mal.')
                ->modalSubmitActionLabel('Guardar ajustes')
                ->schema([
                    Toggle::make('aplicar_mora')
                        ->label('Aplicar mora a esta factura')
                        ->default(fn () => $record->aplicar_mora)
                        ->helperText('Si lo apagas, esta factura deja de acumular mora aunque esté vencida. Solo afecta este mes.'),
                    TextInput::make('saldo_anterior_arrastrado')
                        ->label('Saldo arrastrado de periodo anterior')
                        ->numeric()->prefix('$')
                        ->default(fn () => $record->saldo_anterior_arrastrado),
                    TextInput::make('nota_saldo_arrastrado')
                        ->label('Nota del saldo arrastrado')
                        ->placeholder('Ej: saldo pendiente de junio 2026')
                        ->default(fn () => $record->nota_saldo_arrastrado)
                        ->columnSpanFull(),

                    Grid::make(2)->schema([
                        DatePicker::make('periodo_inicio')
                            ->label('Período — desde')
                            ->native(false)
                            ->default(fn () => $record->periodo_inicio),
                        DatePicker::make('periodo_fin')
                            ->label('Período — hasta')
                            ->native(false)
                            ->default(fn () => $record->periodo_fin),
                    ]),
                    DatePicker::make('fecha_limite_pago')
                        ->label('Fecha límite de pago')
                        ->native(false)
                        ->default(fn () => $record->fecha_limite_pago)
                        ->helperText('Corrige esto si el período de arriendo de esta factura no coincide con el contrato real.')
                        ->columnSpanFull(),
                ])
                ->action(function (array $data) use ($record) {
                    $data['aplicar_mora'] ??= false;

                    // El recálculo de mora tiene efecto inmediato via un hook
                    // en el modelo (RentBill::booted): si se apaga aplicar_mora
                    // limpia mora/saldo al instante; si se prende, o si se
                    // corrige fecha_limite_pago/periodo_inicio/periodo_fin/
                    // saldo_anterior_arrastrado, recalcula y reliquida ya
                    // mismo — no hay que tocar mora_acumulada manualmente aquí.
                    $record->update([
                        'aplicar_mora'              => $data['aplicar_mora'],
                        'saldo_anterior_arrastrado' => $data['saldo_anterior_arrastrado'] ?? 0,
                        'nota_saldo_arrastrado'     => $data['nota_saldo_arrastrado'] ?? null,
                        'periodo_inicio'            => $data['periodo_inicio'] ?? $record->periodo_inicio,
                        'periodo_fin'               => $data['periodo_fin'] ?? $record->periodo_fin,
                        'fecha_limite_pago'         => $data['fecha_limite_pago'] ?? $record->fecha_limite_pago,
                    ]);

                    // Blindaje contable: el hook del modelo ya recalculó
                    // mora_acumulada/saldo_pendiente en memoria, pero eso solo
                    // corrige la factura — si no se causa aquí también, la
                    // contabilidad se queda con el valor viejo (o sin nada) y
                    // el libro se desalinea contra lo que ve el inquilino.
                    // Se causa con el mismo servicio y misma guarda de
                    // idempotencia (un comprobante por factura por mes) que
                    // usa VerificarMoraJob, así que nunca duplica.
                    try {
                        $tipoMoraMes = 'mora_rent_bill_' . now()->format('Ym');
                        $entryMesActual = \App\Models\AccountingEntry::where('referencia_id', $record->id)
                            ->where('referencia_tipo', $tipoMoraMes)
                            ->where('estado', '!=', 'anulado')
                            ->first();

                        if ($entryMesActual) {
                            $moraYaCausada = (float) $entryMesActual->lines()
                                ->where('debito', '>', 0)
                                ->sum('debito');

                            // El comprobante de este mes quedó con un valor
                            // que ya no coincide con la mora recalculada (o la
                            // factura dejó de aplicar mora): se anula para
                            // volver a causar el valor correcto, si no queda
                            // un asiento viejo desalineado en el libro.
                            if (round($moraYaCausada, 2) !== round((float) $record->mora_acumulada, 2)) {
                                $entryMesActual->anular("Ajuste manual de factura {$record->numero}: recálculo de mora/periodo");
                            }
                        }

                        if ($record->aplicar_mora && (float) $record->mora_acumulada > 0) {
                            \App\Services\ContabilidadService::generarParaMora($record, (float) $record->mora_acumulada);
                            \App\Services\ContabilidadService::generarProvisionCartera($record);
                        }
                    } catch (\Throwable $e) {
                        \Illuminate\Support\Facades\Log::warning("Contabilidad mora (ajuste manual) {$record->numero}: " . $e->getMessage());
                    }

                    Notification::make()->title('Ajustes guardados')->success()->send();
                });
        }

        // ── Registrar pago ────────────────────────────────
        if (!in_array($record->estado, ['pagada','anulada'])) {
            $acciones[] = Action::make('registrar_pago')
                ->label('💰 Registrar pago')
                ->color('success')
                ->icon('heroicon-o-banknotes')
                ->modalHeading('Registrar pago')
                ->modalDescription(fn () => "Factura {$record->numero} — {$record->arrendatario?->nombre_completo}")
                ->modalSubmitActionLabel('✓ Registrar pago')
                ->slideOver()
                ->modalWidth('md')
                ->modalFooterActionsAlignment('start')
                ->schema([
                    Placeholder::make('deuda_total_inquilino')
                        ->label('')
                        ->columnSpanFull()
                        ->content(function () use ($record) {
                            $facturas = \App\Models\RentBill::where('arrendatario_id', $record->arrendatario_id)
                                ->whereNotIn('estado', ['pagada', 'anulada'])
                                ->orderBy('anio')->orderBy('mes')
                                ->get();

                            $totalDeuda = $facturas->sum(fn ($f) => $f->saldo_pendiente + $f->mora_acumulada + $f->saldo_anterior_arrastrado);
                            $cantidad = $facturas->count();

                            if ($cantidad <= 1) {
                                return new \Illuminate\Support\HtmlString(
                                    '<div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:10px 14px;font-size:13px;">'
                                    . '💡 Esta es la única factura pendiente de <strong>' . e($record->arrendatario?->nombre_completo) . '</strong>.'
                                    . '</div>'
                                );
                            }

                            $detalle = $facturas->map(function ($f) {
                                $periodo = \Carbon\Carbon::create($f->anio, $f->mes, 1)->translatedFormat('M Y');
                                $valor = number_format($f->saldo_pendiente + $f->mora_acumulada + $f->saldo_anterior_arrastrado, 0, ',', '.');
                                $diasMora = $f->dias_mora > 0 ? " ({$f->dias_mora}d mora)" : '';
                                return "{$periodo}: \${$valor}{$diasMora}";
                            })->implode(' · ');

                            return new \Illuminate\Support\HtmlString(
                                '<div style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:10px 14px;font-size:13px;">'
                                . '⚠️ <strong>' . e($record->arrendatario?->nombre_completo) . '</strong> tiene <strong>' . $cantidad . ' facturas pendientes</strong> por un total de <strong>$' . number_format($totalDeuda, 0, ',', '.') . '</strong>.'
                                . '<div style="margin-top:4px;color:#7f1d1d;">' . e($detalle) . '</div>'
                                . '<div style="margin-top:4px;font-style:italic;">Si hicieron un acuerdo de pago, puedes registrar aquí solo el valor real recibido para esta factura.</div>'
                                . '</div>'
                            );
                        }),
                    Select::make('modo_pago')
                        ->label('¿Qué quieres registrar?')
                        ->options(function () use ($record) {
                            $opciones = ['esta_factura' => "Pagar esta factura ({$record->numero})"];

                            $pendientes = \App\Models\RentBill::where('arrendatario_id', $record->arrendatario_id)
                                ->whereNotIn('estado', ['pagada', 'anulada'])
                                ->where('saldo_pendiente', '>', 0)
                                ->get();

                            if ($pendientes->count() > 1) {
                                $total = $pendientes->sum(fn ($f) => $f->saldo_pendiente + $f->mora_acumulada + $f->saldo_anterior_arrastrado);
                                $opciones['total_deuda'] = 'Pagar toda la deuda acumulada ($' . number_format($total, 0, ',', '.') . ')';
                                $opciones['abono_mes']   = 'Abonar y elegir a qué mes aplica';
                            }

                            return $opciones;
                        })
                        ->default('esta_factura')
                        ->native(false)
                        ->live()
                        ->columnSpanFull()
                        ->afterStateUpdated(function ($state, callable $set) use ($record) {
                            if ($state === 'esta_factura') {
                                $set('total_pagado', $record->saldo_pendiente + $record->mora_acumulada + $record->saldo_anterior_arrastrado);
                            } elseif ($state === 'total_deuda') {
                                $pendientes = \App\Models\RentBill::where('arrendatario_id', $record->arrendatario_id)
                                    ->whereNotIn('estado', ['pagada', 'anulada'])
                                    ->where('saldo_pendiente', '>', 0)->get();
                                $set('total_pagado', $pendientes->sum(fn ($f) => $f->saldo_pendiente + $f->mora_acumulada + $f->saldo_anterior_arrastrado));
                            } else {
                                $set('total_pagado', null);
                            }
                        }),

                    Select::make('rent_bill_objetivo')
                        ->label('¿A qué mes va el abono?')
                        ->options(function () use ($record) {
                            return \App\Models\RentBill::where('arrendatario_id', $record->arrendatario_id)
                                ->whereNotIn('estado', ['pagada', 'anulada'])
                                ->where('saldo_pendiente', '>', 0)
                                ->orderBy('periodo_inicio')
                                ->get()
                                ->mapWithKeys(function ($f) {
                                    $periodo = \Carbon\Carbon::create($f->anio, $f->mes, 1)->translatedFormat('F Y');
                                    $debe = number_format($f->saldo_pendiente + $f->mora_acumulada + $f->saldo_anterior_arrastrado, 0, ',', '.');
                                    return [$f->id => ucfirst($periodo) . " ({$f->numero}) — debe \${$debe}"];
                                });
                        })
                        ->default($record->id)
                        ->native(false)
                        ->columnSpanFull()
                        ->visible(fn (Get $get) => $get('modo_pago') === 'abono_mes')
                        ->required(fn (Get $get) => $get('modo_pago') === 'abono_mes')
                        ->helperText('Si abonas más de lo que debe ese mes, el resto se aplica automáticamente a los siguientes meses pendientes, del más antiguo al más reciente.'),

                    self::grupoLabel('💰 Valor y fecha'),
                    Grid::make(2)->schema([
                        TextInput::make('total_pagado')
                            ->label('Valor recibido')
                            ->numeric()->prefix('$')
                            ->default(fn () => $record->saldo_pendiente + $record->mora_acumulada + $record->saldo_anterior_arrastrado)
                            ->disabled(fn (Get $get) => $get('modo_pago') === 'total_deuda')
                            ->dehydrated(true)
                            ->required(),
                        DatePicker::make('fecha_pago')
                            ->label('Fecha de pago')
                            ->default(now())
                            ->native(false)
                            ->required(),
                    ]),

                    self::grupoLabel('💳 Método de pago', 'El destino contable del dinero se asigna automáticamente según lo que elijas aquí.'),
                    Select::make('forma_pago')
                        ->label('Forma de pago')
                        ->options([
                            'efectivo'      => '💵 Efectivo',
                            'transferencia' => '🏦 Transferencia',
                            'consignacion'  => '🏧 Consignación',
                            'nequi'         => '📱 Nequi',
                            'daviplata'     => '📱 Daviplata',
                            'pse'           => '💻 PSE',
                            'cheque'        => '📝 Cheque',
                        ])
                        ->default('transferencia')
                        ->native(false)
                        ->live()
                        ->required()
                        ->columnSpanFull()
                        ->afterStateUpdated(function ($state, callable $set) {
                            if ($state === 'efectivo') {
                                $set('bank_id', Bank::where('tipo_cuenta', 'caja')->value('id'));
                            } else {
                                $set('bank_id', null);
                            }
                        }),

                    Select::make('bank_id')
                        ->label('Cuenta destino')
                        ->options(fn () => Bank::where('is_active', true)
                            ->where('tipo_cuenta', '!=', 'caja')
                            ->get()
                            ->mapWithKeys(fn ($b) => [$b->id => $b->nombre . ($b->numero_cuenta ? " — {$b->numero_cuenta}" : '')]))
                        ->native(false)
                        ->searchable()
                        ->columnSpanFull()
                        ->visible(fn (Get $get) => $get('forma_pago') !== 'efectivo')
                        ->required(fn (Get $get) => $get('forma_pago') !== 'efectivo')
                        ->helperText('Cuenta bancaria donde efectivamente entró el dinero — determina a qué cuenta contable se contabiliza.'),

                    Placeholder::make('info_caja')
                        ->label('')
                        ->columnSpanFull()
                        ->content('💵 Este pago se contabilizará en Caja general.')
                        ->visible(fn (Get $get) => $get('forma_pago') === 'efectivo'),

                    self::grupoLabel('📎 Soporte'),
                    Grid::make(2)->schema([
                        TextInput::make('referencia_pago')->label('Referencia / N° comprobante'),
                        TextInput::make('banco_origen')->label('Banco de origen del pagador')
                            ->helperText('Ej: si pagó por Nequi, aquí puedes anotar de qué banco venía la plata.'),
                    ]),
                    FileUpload::make('comprobante_path')
                        ->label('Comprobante de pago')
                        ->disk('public')->directory('pagos/comprobantes')
                        ->acceptedFileTypes(['application/pdf','image/jpeg','image/png'])
                        ->maxSize(5120)
                        ->columnSpanFull(),
                    Textarea::make('notas')->label('Notas')->rows(2)->columnSpanFull(),

                    Placeholder::make('resumen_confirmacion')
                        ->label('')
                        ->columnSpanFull()
                        ->content(function (Get $get) use ($record) {
                            $valor  = number_format((float) ($get('total_pagado') ?? 0), 0, ',', '.');
                            $fecha  = $get('fecha_pago') ? \Carbon\Carbon::parse($get('fecha_pago'))->format('d/m/Y') : '—';
                            $formas = [
                                'efectivo' => 'Efectivo', 'transferencia' => 'Transferencia', 'consignacion' => 'Consignación',
                                'nequi' => 'Nequi', 'daviplata' => 'Daviplata', 'pse' => 'PSE', 'cheque' => 'Cheque',
                            ];
                            $forma = $formas[$get('forma_pago')] ?? '—';

                            return new \Illuminate\Support\HtmlString(
                                '<div style="background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:12px 14px;font-size:13px;">'
                                . '⚠️ <strong>Estás a punto de registrar este pago:</strong>'
                                . '<div style="margin-top:6px;line-height:1.6;">'
                                . '💰 Valor: <strong>$' . $valor . '</strong><br>'
                                . '📅 Fecha: <strong>' . $fecha . '</strong><br>'
                                . '💳 Forma de pago: <strong>' . e($forma) . '</strong><br>'
                                . '📋 Factura: <strong>' . e($record->numero) . '</strong> — ' . e($record->arrendatario?->nombre_completo)
                                . '</div>'
                                . '<div style="margin-top:8px;font-style:italic;color:#92400e;">Verifica los datos antes de confirmar — una vez registrado, este pago no podrá revertirse desde aquí.</div>'
                                . '</div>'
                            );
                        }),
                    Checkbox::make('confirmo_registro')
                        ->label('Confirmo que los datos son correctos y entiendo que esta acción no se puede reversar.')
                        ->columnSpanFull()
                        ->required()
                        ->accepted()
                        ->dehydrated(false),
                ])
                ->action(function (array $data) {
                    $modo = $data['modo_pago'] ?? 'esta_factura';

                    // Modo simple (default): igual que siempre, un solo pago
                    // contra esta factura puntual, sin tocar otras.
                    if ($modo === 'esta_factura') {
                        $mora  = $this->record->mora_acumulada;
                        $canon = max(0, $data['total_pagado'] - $mora);

                        RentPayment::create([
                            'rent_bill_id'        => $this->record->id,
                            'rental_contract_id'  => $this->record->rental_contract_id,
                            'arrendatario_id'     => $this->record->arrendatario_id,
                            'registrado_por'      => Auth::id(),
                            'valor_canon'         => $canon,
                            'valor_mora'          => $mora,
                            'valor_administracion'=> $this->record->cuota_administracion,
                            'total_pagado'        => $data['total_pagado'],
                            'forma_pago'          => $data['forma_pago'],
                            'fecha_pago'          => $data['fecha_pago'],
                            'referencia_pago'     => $data['referencia_pago'] ?? null,
                            'banco_origen'        => $data['banco_origen'] ?? null,
                            'bank_id'             => $data['bank_id'] ?? null,
                            'comprobante_path'    => $data['comprobante_path'] ?? null,
                            'notas'               => $data['notas'] ?? null,
                        ]);

                        Notification::make()
                            ->title('✅ Pago registrado — Liquidación al propietario generada')
                            ->success()->send();

                        $this->redirect(static::getResource()::getUrl('edit', ['record' => $this->record]));
                        return;
                    }

                    // Modo "total_deuda" / "abono_mes": se reparte el valor
                    // recibido en cascada entre las facturas pendientes del
                    // inquilino — empezando por la elegida (o la más antigua
                    // si es "pagar todo"), y lo que sobra se aplica a las
                    // siguientes en orden de periodo, igual que hace el link
                    // de pago Wompi cuando el inquilino paga varios meses.
                    $pendientes = \App\Models\RentBill::where('arrendatario_id', $this->record->arrendatario_id)
                        ->whereNotIn('estado', ['pagada', 'anulada'])
                        ->where('saldo_pendiente', '>', 0)
                        ->orderBy('periodo_inicio')
                        ->get();

                    $anclaId = $modo === 'abono_mes' ? (int) ($data['rent_bill_objetivo'] ?? $this->record->id) : null;
                    $ancla   = $anclaId ? ($pendientes->firstWhere('id', $anclaId) ?? $this->record) : $pendientes->first();

                    $orden = collect([$ancla])->merge($pendientes->where('id', '!=', $ancla->id));

                    $restante = (float) $data['total_pagado'];
                    $facturasPagadas = 0;

                    foreach ($orden as $f) {
                        if ($restante <= 0) break;

                        $aplicar = min($restante, (float) $f->saldo_pendiente);
                        if ($aplicar <= 0) continue;

                        $moraFactura  = min($aplicar, (float) $f->mora_acumulada);
                        $canonFactura = round($aplicar - $moraFactura, 2);

                        RentPayment::create([
                            'rent_bill_id'         => $f->id,
                            'rental_contract_id'   => $f->rental_contract_id,
                            'arrendatario_id'      => $f->arrendatario_id,
                            'registrado_por'       => Auth::id(),
                            'valor_canon'          => $canonFactura,
                            'valor_mora'           => $moraFactura,
                            'valor_administracion' => $f->cuota_administracion,
                            'total_pagado'         => $aplicar,
                            'forma_pago'           => $data['forma_pago'],
                            'fecha_pago'           => $data['fecha_pago'],
                            'referencia_pago'      => $data['referencia_pago'] ?? null,
                            'banco_origen'         => $data['banco_origen'] ?? null,
                            'bank_id'              => $data['bank_id'] ?? null,
                            'comprobante_path'     => $data['comprobante_path'] ?? null,
                            'notas'                => $data['notas'] ?? null,
                        ]);

                        $restante = round($restante - $aplicar, 2);
                        $facturasPagadas++;
                    }

                    Notification::make()
                        ->title($facturasPagadas > 1
                            ? "✅ Pago registrado y repartido entre {$facturasPagadas} facturas"
                            : '✅ Pago registrado — Liquidación al propietario generada')
                        ->success()->send();

                    $this->redirect(static::getResource()::getUrl('edit', ['record' => $this->record]));
                });
        }

        // ── Enviar recordatorio WhatsApp ───────────────────
        if (!in_array($record->estado, ['pagada','anulada']) && $record->arrendatario?->celular) {
            $acciones[] = Action::make('enviar_recordatorio')
                ->label('📱 Recordatorio WA')
                ->color('info')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->action(function () {
                    $r     = $this->record;
                    $saldo = '$' . number_format($r->saldo_pendiente + $r->mora_acumulada + $r->saldo_anterior_arrastrado, 0, ',', '.');
                    $mora  = $r->mora_acumulada > 0 ? "\n📈 Mora: $" . number_format($r->mora_acumulada, 0, ',', '.') : '';
                    $msg   = "Recordatorio de pago — Serviarrendar S.A.S\n\n" .
                             "Estimado(a) {$r->arrendatario->nombre_completo},\n\n" .
                             "📋 Factura: {$r->numero}\n" .
                             "💵 Saldo pendiente: {$saldo}{$mora}\n" .
                             "📆 Venció: {$r->fecha_limite_pago->format('d/m/Y')}\n\n" .
                             "Por favor regularice su pago.\n\nServiarrendar S.A.S ☎️ 3186934710";

                    WhatsApp::enviar($r->arrendatario->celular, $msg);
                    Notification::make()->title('📱 Recordatorio enviado')->success()->send();
                    $this->redirect(static::getResource()::getUrl('edit', ['record' => $this->record]));
                });
        }

        // ── PDF ────────────────────────────────────────────
        $acciones[] = Action::make('pdf')
            ->label('📄 PDF')
            ->color('gray')
            ->icon('heroicon-o-arrow-down-tray')
            ->url(fn () => route('factura.pdf', $record))
            ->openUrlInNewTab();

        // ── Anular ─────────────────────────────────────────
        if ($record->estado !== 'anulada') {
            $acciones[] = Action::make('anular_factura')
                ->label('Anular')
                ->icon('heroicon-o-no-symbol')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('¿Deseas anular esta factura?')
                ->modalDescription("Factura {$record->numero} — {$record->arrendatario?->nombre_completo} — \$" . number_format($record->total_factura, 0, ',', '.') . '. Esta acción reversa automáticamente todos los asientos contables ligados (factura, mora, pagos y, si aplica, la liquidación al propietario) y queda registrada con tu usuario y la fecha de hoy.')
                ->modalSubmitActionLabel('Sí, anular factura')
                ->schema([
                    Textarea::make('motivo')
                        ->label('Motivo de la anulación')
                        ->placeholder('Ej: factura duplicada, error en el valor del contrato, contrato terminado anticipadamente...')
                        ->required()
                        ->minLength(10)
                        ->rows(3),
                ])
                ->action(function (array $data) {
                    try {
                        $this->record->anularConReversion($data['motivo'], Auth::id());
                        Notification::make()
                            ->title('Factura anulada')
                            ->body("Se reversaron los asientos contables de {$this->record->numero}.")
                            ->warning()
                            ->send();
                        $this->redirect(static::getResource()::getUrl('edit', ['record' => $this->record]));
                    } catch (\Throwable $e) {
                        Notification::make()->title('Error al anular: ' . $e->getMessage())->danger()->send();
                    }
                });
        }

        return $acciones;
    }

    public function enviarReciboWhatsapp(int $paymentId): void
    {
        $payment = RentPayment::with(['bill.property', 'bill.rentalContract', 'arrendatario', 'bank', 'registradoPor'])
            ->findOrFail($paymentId);

        $celular = $payment->arrendatario?->celular;

        if (!$celular) {
            Notification::make()->title('El arrendatario no tiene celular registrado')->danger()->send();
            return;
        }

        $company = Company::with('municipio')->first();
        $bill    = $payment->bill;

        $msg = "🧾 *Recibo de pago*\n\n"
            . "Estimado(a) {$payment->arrendatario?->nombre_completo},\n\n"
            . "Hemos registrado tu pago correspondiente a la factura {$bill?->numero}.\n\n"
            . "💵 Valor: \$" . number_format((float) $payment->total_pagado, 0, ',', '.') . " COP\n"
            . "📆 Fecha: {$payment->fecha_pago->format('d/m/Y')}\n"
            . "🏠 Inmueble: {$bill?->property?->codigo} — {$bill?->property?->direccion}\n\n"
            . "Adjuntamos el recibo en PDF.\n\n"
            . '— ' . ($company?->razon_social ?? 'Serviarrendar S.A.S') . "\n☎️ " . ($company?->celular ?? '318 693 4710');

        $wap     = app(WhatsAppService::class);
        $enviado = false;
        $errorEnvio = null;

        if ($wap->isConnected()) {
            try {
                $logoBase64 = null;
                if ($company?->logo_path) {
                    $p = storage_path('app/public/' . $company->logo_path);
                    if (file_exists($p)) $logoBase64 = 'data:' . mime_content_type($p) . ';base64,' . base64_encode(file_get_contents($p));
                }
                $pdf     = Pdf::loadView('pdf.recibo-pago', compact('payment', 'company', 'logoBase64'))->setPaper('a5', 'landscape');
                $tmpPath = storage_path('app/tmp/recibo-' . $payment->numero . '-' . time() . '.pdf');
                if (!is_dir(dirname($tmpPath))) mkdir(dirname($tmpPath), 0755, true);
                file_put_contents($tmpPath, $pdf->output());
                $res     = $wap->enviarConArchivo($celular, $msg, $tmpPath, 'Recibo-' . $payment->numero . '.pdf');
                $enviado = $res['ok'] ?? false;
                if (!$enviado) {
                    $errorEnvio = $res['error'] ?? 'El servicio no pudo enviar el recibo con el archivo adjunto.';
                    \Illuminate\Support\Facades\Log::warning('WhatsApp recibo no enviado', ['payment' => $payment->numero, 'res' => $res]);
                }
                if (file_exists($tmpPath)) @unlink($tmpPath);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('WhatsApp recibo excepción', ['payment' => $payment->numero, 'error' => $e->getMessage()]);
                $res     = $wap->enviar($celular, $msg);
                $enviado = $res['ok'] ?? false;
                $errorEnvio = $res['error'] ?? $e->getMessage();
            }
        } else {
            $errorEnvio = 'No fue posible consultar el estado del servicio de WhatsApp.';
            \Illuminate\Support\Facades\Log::warning('WhatsApp no conectado al intentar enviar recibo', ['payment' => $payment->numero]);
        }

        if ($enviado) {
            Notification::make()->title('✅ Recibo enviado por WhatsApp')->success()->send();
        } else {
            $fallback = WhatsApp::urlFallback($celular, $msg);
            Notification::make()
                ->title('No se pudo enviar el recibo por WhatsApp')
                ->body($errorEnvio ?: 'Abra WhatsApp para enviar el mensaje y adjunte el recibo PDF manualmente desde el chat.')
                ->actions([
                    Action::make('abrir_whatsapp')
                        ->label('Abrir WhatsApp')
                        ->icon('heroicon-o-chat-bubble-left-right')
                        ->color('success')
                        ->url($fallback)
                        ->openUrlInNewTab(),
                ])
                ->warning()->send();
        }
    }

    /**
     * Título de grupo estilo "kicker" (mayúsculas, pequeño, gris) para
     * separar secciones del formulario sin usar cajas/bordes pesados.
     */
    private static function grupoLabel(string $texto, ?string $descripcion = null): Placeholder
    {
        $html = '<div style="margin-top:4px;">'
            . '<span style="font-size:11px;font-weight:800;letter-spacing:.07em;text-transform:uppercase;color:#64748b;">' . e($texto) . '</span>'
            . ($descripcion ? '<p style="font-size:12px;color:#94a3b8;margin-top:2px;">' . e($descripcion) . '</p>' : '')
            . '</div>';

        return Placeholder::make('grupo_' . md5($texto))
            ->hiddenLabel()
            ->columnSpanFull()
            ->content(new \Illuminate\Support\HtmlString($html));
    }
}

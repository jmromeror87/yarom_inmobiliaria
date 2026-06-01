<x-filament-panels::page>

{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- ENCABEZADO DEL COMPROBANTE --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
@php
    $tipoColors = [
        'CC' => '#64748b', 'CI' => '#16a34a', 'CE' => '#dc2626',
        'ND' => '#d97706', 'NC' => '#2563eb', 'CA' => '#7c3aed',
    ];
    $tipoLabels = [
        'CC' => 'Comp. Contabilidad', 'CI' => 'Comp. Ingreso',
        'CE' => 'Comp. Egreso',       'ND' => 'Nota Débito',
        'NC' => 'Nota Crédito',       'CA' => 'Comp. Ajuste',
    ];
    $estadoColors = [
        'borrador'       => '#d97706',
        'contabilizado'  => '#16a34a',
        'anulado'        => '#dc2626',
    ];
    $estadoLabels = [
        'borrador'      => 'Borrador',
        'contabilizado' => 'Contabilizado',
        'anulado'       => 'Anulado',
    ];
    $color     = $tipoColors[$record->tipo]    ?? '#64748b';
    $eCcolor   = $estadoColors[$record->estado] ?? '#64748b';
    $totalDeb  = (float) $record->total_debitos;
    $totalCre  = (float) $record->total_creditos;
    $cuadrado  = abs($totalDeb - $totalCre) < 0.01;
    $fmt = fn($v) => '$' . number_format($v, 0, ',', '.');
@endphp

<div style="font-family: system-ui, sans-serif; max-width: 960px; margin: 0 auto; padding: 0 0 40px;">

    {{-- ── Cabecera con gradiente ──────────────────────────── --}}
    <div style="background: linear-gradient(135deg, {{ $color }}18 0%, {{ $color }}08 100%);
                border: 2px solid {{ $color }}40; border-radius: 16px;
                padding: 28px 32px; margin-bottom: 24px;">

        <div style="display: flex; align-items: flex-start; justify-content: space-between; flex-wrap: wrap; gap: 16px;">

            {{-- Tipo + número --}}
            <div>
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                    <span style="background: {{ $color }}; color: #fff; font-size: 13px; font-weight: 800;
                                 padding: 4px 12px; border-radius: 6px; letter-spacing: 1px;">
                        {{ $record->tipo }}
                    </span>
                    <span style="font-size: 13px; color: {{ $color }}; font-weight: 600;">
                        {{ $tipoLabels[$record->tipo] ?? $record->tipo }}
                    </span>
                    <span style="background: {{ $eCcolor }}20; color: {{ $eCcolor }}; border: 1px solid {{ $eCcolor }}50;
                                 font-size: 12px; font-weight: 700; padding: 3px 10px; border-radius: 20px;">
                        {{ strtoupper($estadoLabels[$record->estado] ?? $record->estado) }}
                    </span>
                </div>
                <div style="font-size: 28px; font-weight: 900; color: #111827; font-family: monospace; letter-spacing: -1px;">
                    {{ $record->numero }}
                </div>
                @if($record->referencia)
                    <div style="font-size: 13px; color: #6b7280; margin-top: 4px;">
                        Ref: <strong>{{ $record->referencia }}</strong>
                    </div>
                @endif
            </div>

            {{-- Fecha + período --}}
            <div style="text-align: right;">
                <div style="font-size: 13px; color: #6b7280; margin-bottom: 4px;">Fecha</div>
                <div style="font-size: 22px; font-weight: 800; color: #111827;">
                    {{ $record->fecha?->format('d/m/Y') }}
                </div>
                <div style="margin-top: 8px;">
                    <span style="background: #f1f5f9; color: #475569; font-size: 12px;
                                 padding: 3px 10px; border-radius: 6px; font-weight: 600;">
                        📅 {{ $record->period?->nombre ?? '—' }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Descripción --}}
        <div style="margin-top: 16px; padding: 12px 16px; background: rgba(255,255,255,0.7);
                    border-radius: 8px; border-left: 4px solid {{ $color }};">
            <span style="font-size: 14px; color: #374151;">{{ $record->descripcion }}</span>
        </div>

        {{-- Tercero + Centro de costo --}}
        @if($record->third || $record->costCenter)
        <div style="display: flex; gap: 20px; margin-top: 12px; flex-wrap: wrap;">
            @if($record->third)
            <div style="font-size: 13px; color: #6b7280;">
                👤 Tercero: <strong style="color: #111827;">{{ $record->third->nombre_completo }}</strong>
            </div>
            @endif
            @if($record->costCenter)
            <div style="font-size: 13px; color: #6b7280;">
                🏷️ C. Costo: <strong style="color: #111827;">{{ $record->costCenter->codigo }} — {{ $record->costCenter->nombre }}</strong>
            </div>
            @endif
        </div>
        @endif
    </div>

    {{-- ── Trazabilidad / Auditoría ────────────────────────── --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
                gap: 12px; margin-bottom: 24px;">

        {{-- Creado --}}
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 16px;">
            <div style="font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase;
                        letter-spacing: 0.5px; margin-bottom: 6px;">📝 Creado</div>
            <div style="font-size: 13px; font-weight: 600; color: #374151;">
                {{ $record->creadoPor?->name ?? 'Sistema' }}
            </div>
            <div style="font-size: 12px; color: #6b7280;">
                {{ $record->created_at?->format('d/m/Y H:i') }}
            </div>
        </div>

        {{-- Contabilizado --}}
        @if($record->contabilizado_en)
        <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 10px; padding: 14px 16px;">
            <div style="font-size: 11px; font-weight: 700; color: #16a34a; text-transform: uppercase;
                        letter-spacing: 0.5px; margin-bottom: 6px;">✅ Contabilizado</div>
            <div style="font-size: 13px; font-weight: 600; color: #374151;">
                {{ $record->contabilizadoPor?->name ?? '—' }}
            </div>
            <div style="font-size: 12px; color: #6b7280;">
                {{ $record->contabilizado_en?->format('d/m/Y H:i') }}
            </div>
        </div>
        @endif

        {{-- Anulado --}}
        @if($record->anulado_en)
        <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 10px; padding: 14px 16px;">
            <div style="font-size: 11px; font-weight: 700; color: #dc2626; text-transform: uppercase;
                        letter-spacing: 0.5px; margin-bottom: 6px;">❌ Anulado</div>
            <div style="font-size: 13px; font-weight: 600; color: #374151;">
                {{ $record->anuladoPor?->name ?? '—' }}
            </div>
            <div style="font-size: 12px; color: #6b7280; margin-bottom: 4px;">
                {{ $record->anulado_en?->format('d/m/Y H:i') }}
            </div>
            @if($record->razon_anulacion)
            <div style="font-size: 12px; color: #dc2626; font-style: italic; margin-top: 4px;">
                "{{ $record->razon_anulacion }}"
            </div>
            @endif
        </div>
        @endif
    </div>

    {{-- ── Líneas contables ─────────────────────────────────── --}}
    <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; margin-bottom: 20px;">
        <div style="background: #f8fafc; padding: 12px 20px; border-bottom: 1px solid #e2e8f0;">
            <span style="font-size: 14px; font-weight: 700; color: #374151;">
                📋 Movimientos contables ({{ $record->lines->count() }} líneas)
            </span>
        </div>
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                <thead>
                    <tr style="background: #f1f5f9;">
                        <th style="padding: 10px 16px; text-align: left; color: #475569; font-weight: 700; border-bottom: 2px solid #e2e8f0; white-space: nowrap;">#</th>
                        <th style="padding: 10px 16px; text-align: left; color: #475569; font-weight: 700; border-bottom: 2px solid #e2e8f0;">Cuenta PUC</th>
                        <th style="padding: 10px 16px; text-align: left; color: #475569; font-weight: 700; border-bottom: 2px solid #e2e8f0;">Descripción</th>
                        <th style="padding: 10px 16px; text-align: left; color: #475569; font-weight: 700; border-bottom: 2px solid #e2e8f0;">Tercero</th>
                        <th style="padding: 10px 16px; text-align: right; color: #16a34a; font-weight: 700; border-bottom: 2px solid #e2e8f0; white-space: nowrap;">Débito</th>
                        <th style="padding: 10px 16px; text-align: right; color: #dc2626; font-weight: 700; border-bottom: 2px solid #e2e8f0; white-space: nowrap;">Crédito</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($record->lines as $i => $line)
                    <tr style="border-bottom: 1px solid #f1f5f9; {{ $loop->even ? 'background:#fafafa;' : '' }}">
                        <td style="padding: 10px 16px; color: #94a3b8; font-size: 12px;">{{ $i + 1 }}</td>
                        <td style="padding: 10px 16px;">
                            <div style="font-family: monospace; font-weight: 700; color: #1e40af; font-size: 13px;">
                                {{ $line->account?->codigo ?? '—' }}
                            </div>
                            <div style="font-size: 12px; color: #6b7280; margin-top: 2px;">
                                {{ $line->account?->nombre ?? '—' }}
                            </div>
                        </td>
                        <td style="padding: 10px 16px; color: #374151;">
                            {{ $line->descripcion ?: '—' }}
                        </td>
                        <td style="padding: 10px 16px; color: #374151; font-size: 12px;">
                            {{ $line->third?->nombre_completo ?? '—' }}
                        </td>
                        <td style="padding: 10px 16px; text-align: right; font-family: monospace;
                                   font-weight: 600; color: {{ $line->debito > 0 ? '#16a34a' : '#d1d5db' }};">
                            {{ $line->debito > 0 ? $fmt($line->debito) : '—' }}
                        </td>
                        <td style="padding: 10px 16px; text-align: right; font-family: monospace;
                                   font-weight: 600; color: {{ $line->credito > 0 ? '#dc2626' : '#d1d5db' }};">
                            {{ $line->credito > 0 ? $fmt($line->credito) : '—' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background: #f8fafc; border-top: 2px solid #e2e8f0;">
                        <td colspan="4" style="padding: 12px 16px; font-weight: 700; color: #374151; font-size: 13px;">
                            TOTALES
                        </td>
                        <td style="padding: 12px 16px; text-align: right; font-family: monospace;
                                   font-weight: 800; font-size: 14px; color: #16a34a;">
                            {{ $fmt($totalDeb) }}
                        </td>
                        <td style="padding: 12px 16px; text-align: right; font-family: monospace;
                                   font-weight: 800; font-size: 14px; color: #dc2626;">
                            {{ $fmt($totalCre) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- ── Panel cuadre ─────────────────────────────────────── --}}
    @php
        $diff = abs($totalDeb - $totalCre);
        $cuadColor  = $cuadrado ? '#16a34a' : '#dc2626';
        $cuadBg     = $cuadrado ? '#f0fdf4' : '#fef2f2';
        $cuadBorder = $cuadrado ? '#bbf7d0' : '#fecaca';
        $cuadIcon   = $cuadrado ? '✅' : '❌';
        $cuadText   = $cuadrado ? 'COMPROBANTE CUADRADO' : 'DIFERENCIA: ' . $fmt($diff);
    @endphp
    <div style="display: flex; align-items: center; justify-content: space-between;
                background: {{ $cuadBg }}; border: 2px solid {{ $cuadBorder }};
                border-radius: 12px; padding: 16px 24px; flex-wrap: wrap; gap: 12px;">
        <div style="display: flex; gap: 32px; align-items: center; flex-wrap: wrap;">
            <div>
                <span style="font-size: 12px; color: #6b7280;">Total Débitos</span>
                <div style="font-size: 20px; font-weight: 900; font-family: monospace; color: #16a34a;">
                    {{ $fmt($totalDeb) }}
                </div>
            </div>
            <div style="font-size: 24px; color: #94a3b8;">=</div>
            <div>
                <span style="font-size: 12px; color: #6b7280;">Total Créditos</span>
                <div style="font-size: 20px; font-weight: 900; font-family: monospace; color: #dc2626;">
                    {{ $fmt($totalCre) }}
                </div>
            </div>
        </div>
        <div style="font-size: 15px; font-weight: 800; color: {{ $cuadColor }}; letter-spacing: 0.5px;">
            {{ $cuadIcon }} {{ $cuadText }}
        </div>
    </div>

</div>

</x-filament-panels::page>

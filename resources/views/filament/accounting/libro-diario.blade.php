<x-filament-panels::page>

@php
    $entries  = $this->getEntries();
    $periodos = $this->getPeriodos();
    $totales  = $this->getTotales();
    $totalDeb = $totales['debitos'];
    $totalCre = $totales['creditos'];
    $fmt = fn($v) => '$' . number_format($v, 2, ',', '.');
    $tipoLabels = [
        'CC'=>'Cont.','CI'=>'Ingreso','CE'=>'Egreso','ND'=>'N.Déb','NC'=>'N.Cre','CA'=>'Ajuste',
    ];
    $tipoColors = [
        'CC'=>'#2563eb','CI'=>'#16a34a','CE'=>'#dc2626','ND'=>'#d97706','NC'=>'#7c3aed','CA'=>'#64748b',
    ];
@endphp

<style>
.acc-card{background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:20px 24px;margin-bottom:16px;}
.acc-filter{display:flex;gap:12px;align-items:center;flex-wrap:wrap;margin-bottom:20px;}
.acc-filter select{padding:8px 14px;border:1px solid #cbd5e1;border-radius:10px;font-size:13px;font-weight:600;color:#0f172a;background:#fff;cursor:pointer;}
.acc-table{width:100%;border-collapse:collapse;font-size:13px;}
.acc-table th{background:#f8fafc;padding:10px 14px;text-align:left;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:0.08em;color:#64748b;border-bottom:2px solid #e2e8f0;position:sticky;top:0;z-index:1;}
.acc-table td{padding:10px 14px;border-bottom:1px solid #f1f5f9;vertical-align:top;}
.acc-table tr:hover td{background:#f8fafc;}
.acc-scroll{max-height:70vh;overflow-y:auto;}
.acc-pagination{display:flex;justify-content:flex-end;align-items:center;gap:4px;margin:12px 0;}
.acc-pagination a,.acc-pagination span{display:inline-flex;align-items:center;justify-content:center;min-width:32px;height:32px;padding:0 8px;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;}
.acc-pagination a{color:#334155;background:#fff;border:1px solid #e2e8f0;}
.acc-pagination a:hover{background:#f1f5f9;}
.acc-pagination span.acc-page-current{background:#0f172a;color:#fff;}
.acc-pagination span.acc-page-disabled{color:#cbd5e1;background:#f8fafc;border:1px solid #f1f5f9;}
.acc-pagination .acc-page-info{font-size:12px;color:#94a3b8;margin-left:8px;white-space:nowrap;}
.acc-num{font-family:monospace;font-weight:700;}
.acc-badge{display:inline-block;padding:2px 9px;border-radius:99px;font-size:10px;font-weight:800;}
.acc-deb{color:#2563eb;font-family:monospace;font-weight:700;}
.acc-cre{color:#16a34a;font-family:monospace;font-weight:700;}
.acc-total{background:#f8fafc;font-weight:900;border-top:2px solid #e2e8f0;}
.acc-entry-lines{margin-left:20px;}
</style>

<div>
    {{-- Filtros --}}
    <div class="acc-filter">
        <label style="font-size:12px;font-weight:700;color:#64748b;">Período:</label>
        <select wire:model.live="periodo_id">
            <option value="">— Todos los períodos —</option>
            @foreach($periodos as $id => $nombre)
            <option value="{{ $id }}" @selected($this->periodo_id == $id)>{{ $nombre }}</option>
            @endforeach
        </select>
        <label style="font-size:12px;font-weight:700;color:#64748b;margin-left:8px;">Por página:</label>
        <select wire:model.live="perPage">
            @foreach([25, 50, 100, 200] as $n)
            <option value="{{ $n }}" @selected($this->perPage == $n)>{{ $n }}</option>
            @endforeach
        </select>
        <span style="font-size:12px;color:#94a3b8;">{{ $totales['count'] }} comprobantes contabilizados</span>
    </div>

    @if($entries->isEmpty())
    <div style="text-align:center;padding:48px;color:#94a3b8;">
        <div style="font-size:36px;margin-bottom:8px;">📖</div>
        <div style="font-weight:700;">No hay comprobantes contabilizados en este período.</div>
    </div>
    @else

    {{-- Paginación (arriba) --}}
    @include('filament.accounting.partials.paginacion')

    {{-- Tabla --}}
    <div class="acc-card" style="padding:0;overflow:hidden;">
        <div class="acc-scroll">
        <table class="acc-table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Número</th>
                    <th>Tipo</th>
                    <th>Descripción</th>
                    <th style="text-align:right;">Débito</th>
                    <th style="text-align:right;">Crédito</th>
                </tr>
            </thead>
            <tbody>
                @foreach($entries as $entry)
                {{-- Fila del comprobante --}}
                <tr style="background:#fafbfc;">
                    <td class="acc-num">{{ $entry->fecha->format('d/m/Y') }}</td>
                    <td>
                        <span class="acc-num" style="color:#2563eb;">{{ $entry->numero }}</span>
                    </td>
                    <td>
                        <span class="acc-badge" style="background:{{ $tipoColors[$entry->tipo] ?? '#64748b' }}22;color:{{ $tipoColors[$entry->tipo] ?? '#64748b' }};">
                            {{ $tipoLabels[$entry->tipo] ?? $entry->tipo }}
                        </span>
                    </td>
                    <td style="font-weight:600;color:#0f172a;">{{ $entry->descripcion }}</td>
                    <td class="acc-deb" style="text-align:right;">{{ $fmt($entry->total_debitos) }}</td>
                    <td class="acc-cre" style="text-align:right;">{{ $fmt($entry->total_creditos) }}</td>
                </tr>
                {{-- Líneas del comprobante --}}
                @foreach($entry->lines as $line)
                <tr>
                    <td colspan="3"></td>
                    <td style="padding-left:32px;font-size:12px;color:#475569;">
                        <span style="font-family:monospace;font-weight:700;color:#0f172a;">{{ $line->account?->codigo }}</span>
                        {{ $line->account?->nombre }}
                        @if($line->third) <span style="color:#94a3b8;"> — {{ $line->third->nombre_completo }}</span> @endif
                        @if($line->descripcion) <span style="color:#94a3b8;font-style:italic;"> · {{ $line->descripcion }}</span> @endif
                    </td>
                    <td style="text-align:right;font-size:12px;" class="acc-deb">{{ $line->debito > 0 ? $fmt($line->debito) : '' }}</td>
                    <td style="text-align:right;font-size:12px;" class="acc-cre">{{ $line->credito > 0 ? $fmt($line->credito) : '' }}</td>
                </tr>
                @endforeach
                @endforeach
            </tbody>
        </table>
        </div>

        {{-- Totales del período completo (no solo la página actual) --}}
        <table class="acc-table" style="margin:0;">
            <tbody>
                <tr class="acc-total">
                    <td style="padding:12px 14px;font-size:13px;font-weight:900;text-transform:uppercase;letter-spacing:0.05em;color:#0f172a;width:100%;">TOTALES DEL PERÍODO</td>
                    <td class="acc-deb" style="text-align:right;padding:12px 14px;font-size:14px;white-space:nowrap;">{{ $fmt($totalDeb) }}</td>
                    <td class="acc-cre" style="text-align:right;padding:12px 14px;font-size:14px;white-space:nowrap;">{{ $fmt($totalCre) }}</td>
                </tr>

                {{-- Verificación cuadre --}}
                @php $diff = abs($totalDeb - $totalCre); $cuadrado = $diff < 0.01; @endphp
                <tr>
                    <td colspan="3" style="text-align:center;padding:12px;background:{{ $cuadrado ? '#f0fdf4' : '#fef2f2' }};color:{{ $cuadrado ? '#15803d' : '#dc2626' }};font-weight:900;font-size:13px;">
                        {{ $cuadrado ? '✅ Libro cuadrado — Débitos = Créditos' : '❌ DESCUADRE: ' . $fmt($diff) }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- Paginación (abajo) --}}
    @include('filament.accounting.partials.paginacion')
    @endif
</div>

</x-filament-panels::page>

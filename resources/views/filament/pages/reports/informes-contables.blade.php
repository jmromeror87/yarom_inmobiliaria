<x-filament-panels::page>

    {{-- ── SELECTOR DE INFORME + FILTROS ─────────────────────────────── --}}
    <div style="background:#fff;border-radius:16px;border:1.5px solid #E2E8F0;padding:24px;margin-bottom:20px;box-shadow:0 2px 12px rgba(0,0,0,.04)">

        {{-- Tipo de informe --}}
        <div style="font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:#64748B;margin-bottom:12px">Tipo de informe</div>
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin-bottom:20px">
            @foreach($this->getTiposInforme() as $key => $label)
            <button
                wire:click="$set('tipoInforme','{{ $key }}')"
                style="text-align:left;padding:10px 14px;border-radius:10px;border:1.5px solid {{ $tipoInforme === $key ? '#1E3A8A' : '#E2E8F0' }};background:{{ $tipoInforme === $key ? '#DBEAFE' : '#F8FAFC' }};color:{{ $tipoInforme === $key ? '#1E3A8A' : '#475569' }};font-size:12px;font-weight:{{ $tipoInforme === $key ? '700' : '500' }};cursor:pointer;transition:all .15s">
                {{ $label }}
            </button>
            @endforeach
        </div>

        {{-- Filtros de fecha --}}
        <div style="display:flex;gap:16px;align-items:flex-end;flex-wrap:wrap">
            @if(!$this->getRequiereFechaHasta())
            <div style="flex:1;min-width:180px">
                <label style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#64748B;display:block;margin-bottom:6px">Desde</label>
                <input type="date" wire:model.lazy="desde" value="{{ $desde }}"
                    style="width:100%;padding:9px 12px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:13px;color:#0F172A;background:#F8FAFC">
            </div>
            @endif
            <div style="flex:1;min-width:180px">
                <label style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#64748B;display:block;margin-bottom:6px">
                    {{ $this->getRequiereFechaHasta() ? 'Fecha de corte' : 'Hasta' }}
                </label>
                <input type="date" wire:model.lazy="hasta" value="{{ $hasta }}"
                    style="width:100%;padding:9px 12px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:13px;color:#0F172A;background:#F8FAFC">
            </div>

            {{-- Atajos de período --}}
            <div style="display:flex;gap:6px;flex-wrap:wrap">
                @foreach([
                    ['Hoy', now()->toDateString(), now()->toDateString()],
                    ['Este mes', now()->startOfMonth()->toDateString(), now()->toDateString()],
                    ['Trimestre', now()->startOfQuarter()->toDateString(), now()->toDateString()],
                    ['Este año', now()->startOfYear()->toDateString(), now()->toDateString()],
                    ['Año anterior', now()->subYear()->startOfYear()->toDateString(), now()->subYear()->endOfYear()->toDateString()],
                ] as [$lbl, $d, $h])
                <button wire:click="$set('desde','{{ $d }}'); $set('hasta','{{ $h }}')"
                    style="padding:7px 12px;border:1px solid #CBD5E1;border-radius:6px;background:#F8FAFC;color:#475569;font-size:11px;font-weight:600;cursor:pointer">
                    {{ $lbl }}
                </button>
                @endforeach
            </div>

            {{-- Botón calcular --}}
            <button wire:click="calcular" wire:loading.attr="disabled"
                style="padding:11px 28px;background:linear-gradient(135deg,#0F172A,#1E3A8A);color:#fff;border:none;border-radius:10px;font-size:13px;font-weight:800;cursor:pointer;display:flex;align-items:center;gap:8px;white-space:nowrap">
                <span wire:loading wire:target="calcular">⏳</span>
                <span wire:loading.remove wire:target="calcular">📊</span>
                Calcular informe
            </button>
        </div>

        @if($tipoInforme === 'balance_prueba')
        <div style="margin-top:12px;display:flex;align-items:center;gap:8px">
            <input type="checkbox" wire:model.lazy="soloConMov" id="soloConMov" style="width:16px;height:16px;cursor:pointer">
            <label for="soloConMov" style="font-size:12px;color:#475569;font-weight:600;cursor:pointer">
                Solo cuentas con movimiento
            </label>
        </div>
        @endif
    </div>

    {{-- ── RESULTADO ──────────────────────────────────────────────────── --}}
    @php $reportData = $this->getReportData(); @endphp
    @if($calculado && !empty($reportData))
    <div style="background:#fff;border-radius:16px;border:1.5px solid #E2E8F0;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,.04)">

        {{-- Header del informe --}}
        <div style="background:linear-gradient(135deg,#0F172A,#1E3A8A);padding:20px 28px;color:#fff;display:flex;justify-content:space-between;align-items:flex-start">
            <div>
                <div style="font-size:12px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:rgba(255,255,255,.5);margin-bottom:4px">
                    {{ \App\Models\Company::first()?->razon_social ?? 'Serviarrendar S.A.S' }}
                </div>
                <div style="font-size:22px;font-weight:900;letter-spacing:-.02em">{{ $reportData['titulo'] ?? '' }}</div>
                <div style="font-size:12px;color:rgba(255,255,255,.65);margin-top:4px">
                    {{ $reportData['periodo_label'] ?? $reportData['hasta_label'] ?? '' }}
                </div>
            </div>
            <div style="display:flex;gap:10px">
                <button wire:click="exportarExcel"
                    style="display:flex;align-items:center;gap:6px;padding:9px 18px;background:#16A34A;color:#fff;border:none;border-radius:8px;font-size:12px;font-weight:700;cursor:pointer">
                    📥 Excel
                </button>
                <button wire:click="exportarPdf"
                    style="display:flex;align-items:center;gap:6px;padding:9px 18px;background:#DC2626;color:#fff;border:none;border-radius:8px;font-size:12px;font-weight:700;cursor:pointer">
                    📄 PDF
                </button>
            </div>
        </div>

        {{-- KPIs --}}
        @if(!empty($reportData['kpis']))
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:0;border-bottom:1px solid #E2E8F0">
            @php
            $kpiColors = [
                'green'   => ['#F0FDF4','#14532D','#BBF7D0'],
                'red'     => ['#FFF1F2','#9F1239','#FECDD3'],
                'blue'    => ['#EFF6FF','#1E3A8A','#BFDBFE'],
                'orange'  => ['#FFF7ED','#7C2D12','#FED7AA'],
                'gray'    => ['#F8FAFC','#0F172A','#CBD5E1'],
                'purple'  => ['#FAF5FF','#4C1D95','#DDD6FE'],
                'emerald' => ['#ECFDF5','#064E3B','#A7F3D0'],
                'indigo'  => ['#EEF2FF','#312E81','#C7D2FE'],
            ];
            @endphp
            @foreach($reportData['kpis'] as $kpi)
            @php [$bg, $fg, $border] = $kpiColors[$kpi['color'] ?? 'gray'] ?? $kpiColors['gray']; @endphp
            <div style="background:{{ $bg }};padding:18px 16px;text-align:center;border-right:1px solid {{ $border }};border-bottom:1px solid {{ $border }}">
                <div style="font-size:24px;line-height:1">{{ $kpi['icon'] ?? '📊' }}</div>
                <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:{{ $fg }};opacity:.75;margin-top:6px">{{ $kpi['label'] }}</div>
                <div style="font-size:18px;font-weight:900;color:{{ $fg }};margin-top:4px;line-height:1.1">
                    @if($kpi['es_pct'] ?? false)
                        {{ $kpi['valor'] }}
                    @else
                        @if(is_numeric($kpi['valor']))
                            ${{ number_format((float)$kpi['valor'], 0, ',', '.') }}
                        @else
                            {{ $kpi['valor'] }}
                        @endif
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endif

        {{-- Contenido del informe según tipo --}}
        <div style="padding:24px">
            @include('filament.pages.reports.partials.' . ($reportData['tipo'] ?? 'generico'), ['data' => $reportData])
        </div>
    </div>

    @elseif(!$calculado)
    {{-- Estado vacío --}}
    <div style="background:#fff;border-radius:16px;border:1.5px solid #E2E8F0;padding:60px;text-align:center;color:#94A3B8">
        <div style="font-size:48px;margin-bottom:16px">📊</div>
        <div style="font-size:16px;font-weight:700;color:#475569;margin-bottom:6px">Selecciona un informe y haz clic en "Calcular"</div>
        <div style="font-size:13px">Los datos se extraen directamente de los asientos contabilizados.</div>
    </div>
    @endif

</x-filament-panels::page>

<x-filament-panels::page>
    <style>
        .cal-wrap { display:flex; flex-direction:column; gap:14px; }

        .cal-toolbar { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; }
        .cal-titulo { font-size:1.3rem; font-weight:800; color:#0F172A; letter-spacing:-0.01em; }
        .cal-nav { display:flex; align-items:center; gap:6px; }
        .cal-btn { background:#fff; border:1px solid #e2e8f0; border-radius:9px; padding:7px 14px; cursor:pointer; font-size:0.85rem; font-weight:600; color:#334155; box-shadow:0 1px 2px rgba(0,0,0,.04); transition:all .15s; }
        .cal-btn:hover { background:#f8fafc; border-color:#cbd5e1; }
        .cal-btn.hoy { color:#4f46e5; border-color:#c7d2fe; }

        .cal-leyenda { display:flex; align-items:center; gap:18px; flex-wrap:wrap; background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:10px 16px; font-size:0.76rem; color:#475569; }
        .cal-leyenda-item { display:flex; align-items:center; gap:6px; }
        .cal-dot { width:9px; height:9px; border-radius:50%; display:inline-block; }
        .cal-leyenda-sep { width:1px; height:16px; background:#e2e8f0; }

        .cal-resumen { display:flex; gap:12px; flex-wrap:wrap; }
        .cal-kpi { flex:1; min-width:140px; background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:12px 14px; }
        .cal-kpi-label { font-size:0.68rem; font-weight:700; text-transform:uppercase; letter-spacing:.04em; color:#94a3b8; }
        .cal-kpi-valor { font-size:1.1rem; font-weight:800; color:#0F172A; margin-top:2px; }

        .cal-grid { display:grid; grid-template-columns:repeat(7,1fr); gap:8px; }
        .cal-dow { text-align:center; font-size:0.7rem; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:.04em; padding-bottom:4px; }
        .cal-cell { min-height:96px; border:1px solid #e2e8f0; border-radius:12px; padding:8px; background:#fff; cursor:default; transition:all .15s; display:flex; flex-direction:column; }
        .cal-cell.con-datos { cursor:pointer; }
        .cal-cell.con-datos:hover { box-shadow:0 4px 14px rgba(15,23,42,.08); transform:translateY(-1px); border-color:#cbd5e1; }
        .cal-cell.vacia { background:transparent; border-color:transparent; }
        .cal-cell.hoy { border:1.5px solid #6366f1; background:linear-gradient(180deg,#eef2ff 0%,#fff 100%); }
        .cal-dia-num { font-size:0.82rem; font-weight:700; color:#334155; }
        .cal-dia-num.hoy { color:#4f46e5; }
        .cal-badge { margin-top:6px; display:inline-flex; align-items:center; gap:4px; font-size:0.68rem; font-weight:700; padding:3px 7px; border-radius:6px; width:fit-content; }
        .cal-badge.completo { background:#dcfce7; color:#15803d; }
        .cal-badge.parcial { background:#fef3c7; color:#b45309; }
        .cal-badge.ninguno { background:#fee2e2; color:#b91c1c; }
        .cal-badge.gracia { background:#e0e7ff; color:#4338ca; }
        .cal-monto { font-size:0.68rem; color:#94a3b8; margin-top:auto; padding-top:4px; font-weight:600; }

        .cal-modal-item { display:flex; align-items:center; justify-content:space-between; padding:10px 12px; border:1px solid #e2e8f0; border-radius:10px; margin-bottom:7px; transition:background .15s; }
        .cal-modal-item:hover { background:#f8fafc; border-color:#cbd5e1; }
    </style>

    <div class="cal-wrap">

        {{-- Leyenda de ayuda --}}
        <div class="cal-leyenda">
            <div class="cal-leyenda-item"><span class="cal-dot" style="background:#16a34a;"></span> Todos pagados</div>
            <div class="cal-leyenda-item"><span class="cal-dot" style="background:#d97706;"></span> Pago parcial</div>
            <div class="cal-leyenda-item"><span class="cal-dot" style="background:#4338ca;"></span> Aún en días de gracia</div>
            <div class="cal-leyenda-item"><span class="cal-dot" style="background:#dc2626;"></span> Sin pagos, gracia vencida</div>
            <div class="cal-leyenda-sep"></div>
            <div class="cal-leyenda-item">🖱️ Clic en un día para ver quién debe pagar y registrar el pago</div>
        </div>

        {{-- Resumen del mes --}}
        <div class="cal-resumen">
            <div class="cal-kpi">
                <div class="cal-kpi-label">Facturas este mes</div>
                <div class="cal-kpi-valor">{{ collect($dias)->filter()->sum('total') }}</div>
            </div>
            <div class="cal-kpi">
                <div class="cal-kpi-label">Ya pagadas</div>
                <div class="cal-kpi-valor" style="color:#16a34a;">{{ collect($dias)->filter()->sum('pagadas') }}</div>
            </div>
            <div class="cal-kpi">
                <div class="cal-kpi-label">Pendientes</div>
                <div class="cal-kpi-valor" style="color:#dc2626;">{{ collect($dias)->filter()->sum('total') - collect($dias)->filter()->sum('pagadas') }}</div>
            </div>
            <div class="cal-kpi">
                <div class="cal-kpi-label">Total esperado</div>
                <div class="cal-kpi-valor">${{ number_format(collect($dias)->filter()->flatMap(fn($d) => $d['facturas'])->sum('total_factura'), 0, ',', '.') }}</div>
            </div>
        </div>

        <div class="cal-toolbar">
            <div class="cal-titulo">{{ $this->mesLabel }}</div>
            <div class="cal-nav">
                <button class="cal-btn" wire:click="mesAnterior">‹</button>
                <button class="cal-btn hoy" wire:click="irHoy">Hoy</button>
                <button class="cal-btn" wire:click="mesSiguiente">›</button>
            </div>
        </div>

        <div class="cal-grid">
            @foreach(['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'] as $dow)
                <div class="cal-dow">{{ $dow }}</div>
            @endforeach

            @foreach($dias as $d)
                @if(is_null($d))
                    <div class="cal-cell vacia"></div>
                @else
                    @php
                        $badgeClass = $d['total'] == 0 ? '' : ($d['pagadas'] == $d['total'] ? 'completo' : ($d['pagadas'] > 0 ? 'parcial' : ($d['enGracia'] ? 'gracia' : 'ninguno')));
                        $badgeIcono = match($badgeClass) {
                            'completo' => '✓',
                            'parcial' => '◐',
                            'gracia' => '🕐',
                            default => '●',
                        };
                    @endphp
                    <div class="cal-cell {{ $d['esHoy'] ? 'hoy' : '' }} {{ $d['total'] > 0 ? 'con-datos' : '' }}"
                         @if($d['total'] > 0) x-on:click="$dispatch('open-modal', { id: 'dia-{{ $d['dia'] }}' })" @endif>
                        <div class="cal-dia-num {{ $d['esHoy'] ? 'hoy' : '' }}">{{ $d['dia'] }}</div>
                        @if($d['total'] > 0)
                            <span class="cal-badge {{ $badgeClass }}">{{ $badgeIcono }} {{ $d['pagadas'] }}/{{ $d['total'] }} cobros</span>
                            <div class="cal-monto">${{ number_format(collect($d['facturas'])->sum('total_factura'), 0, ',', '.') }}</div>
                        @endif
                    </div>

                    @if($d['total'] > 0)
                        <x-filament::modal id="dia-{{ $d['dia'] }}" width="lg">
                            <x-slot name="heading">
                                Vencen el {{ str_pad($d['dia'], 2, '0', STR_PAD_LEFT) }}/{{ str_pad($mes, 2, '0', STR_PAD_LEFT) }}/{{ $anio }}
                            </x-slot>

                            <div>
                                @foreach($d['facturas'] as $f)
                                    <a href="{{ \App\Filament\Resources\RentBills\RentBillResource::getUrl('edit', ['record' => $f['id']]) }}" class="cal-modal-item" style="text-decoration:none;color:inherit;">
                                        <div>
                                            <div style="font-weight:700;font-size:0.85rem;color:#0F172A;">{{ $f['arrendatario'] }}</div>
                                            <div style="font-size:0.72rem;color:#94a3b8;">{{ $f['numero'] }} · {{ $f['inmueble'] }}</div>
                                        </div>
                                        <div style="text-align:right;">
                                            <div style="font-weight:700;font-size:0.85rem;">${{ number_format($f['total_factura'], 0, ',', '.') }}</div>
                                            @php
                                                $fBadge = $f['estado'] === 'pagada' ? 'completo' : ($f['estado'] === 'parcial' ? 'parcial' : ($f['en_gracia'] ? 'gracia' : 'ninguno'));
                                                $fLabel = $f['estado'] === 'pagada' ? 'Pagada' : ($f['estado'] === 'parcial' ? 'Parcial' : ($f['en_gracia'] ? 'En gracia hasta ' . \Carbon\Carbon::parse($f['fin_gracia'])->format('d/m') : 'Vencida'));
                                            @endphp
                                            <span class="cal-badge {{ $fBadge }}">{{ $fLabel }}</span>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </x-filament::modal>
                    @endif
                @endif
            @endforeach
        </div>
    </div>
</x-filament-panels::page>

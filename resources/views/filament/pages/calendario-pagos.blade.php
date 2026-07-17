<x-filament-panels::page>
    <style>
        .cal-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:16px; flex-wrap:wrap; gap:12px; }
        .cal-titulo { font-size:1.25rem; font-weight:700; color:#0F172A; }
        .cal-nav { display:flex; align-items:center; gap:8px; }
        .cal-btn { background:#fff; border:1px solid #e2e8f0; border-radius:8px; padding:6px 12px; cursor:pointer; font-size:0.85rem; font-weight:600; color:#334155; }
        .cal-btn:hover { background:#f1f5f9; }
        .cal-grid { display:grid; grid-template-columns:repeat(7,1fr); gap:6px; }
        .cal-dow { text-align:center; font-size:0.72rem; font-weight:700; color:#94a3b8; text-transform:uppercase; padding-bottom:6px; }
        .cal-cell { min-height:88px; border:1px solid #e2e8f0; border-radius:8px; padding:6px; background:#fff; cursor:pointer; transition:box-shadow .15s; }
        .cal-cell:hover { box-shadow:0 2px 8px rgba(0,0,0,.08); }
        .cal-cell.vacia { background:transparent; border:none; cursor:default; }
        .cal-cell.hoy { border:2px solid #6366f1; background:#eef2ff; }
        .cal-dia-num { font-size:0.8rem; font-weight:700; color:#0F172A; }
        .cal-dia-num.hoy { color:#6366f1; }
        .cal-badge { margin-top:4px; display:inline-block; font-size:0.68rem; font-weight:700; padding:2px 6px; border-radius:5px; }
        .cal-badge.completo { background:#dcfce7; color:#16a34a; }
        .cal-badge.parcial { background:#fef3c7; color:#d97706; }
        .cal-badge.ninguno { background:#fee2e2; color:#dc2626; }
        .cal-cobros { font-size:0.62rem; color:#94a3b8; margin-top:2px; }

        .cal-modal-item { display:flex; align-items:center; justify-content:space-between; padding:8px 10px; border:1px solid #e2e8f0; border-radius:8px; margin-bottom:6px; }
        .cal-modal-item:hover { background:#f8fafc; }
    </style>

    <div class="cal-header">
        <div class="cal-titulo">{{ $this->mesLabel }}</div>
        <div class="cal-nav">
            <button class="cal-btn" wire:click="mesAnterior">‹</button>
            <button class="cal-btn" wire:click="irHoy">Hoy</button>
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
                    $badgeClass = $d['total'] == 0 ? '' : ($d['pagadas'] == $d['total'] ? 'completo' : ($d['pagadas'] > 0 ? 'parcial' : 'ninguno'));
                @endphp
                <div class="cal-cell {{ $d['esHoy'] ? 'hoy' : '' }}"
                     @if($d['total'] > 0) x-on:click="$dispatch('open-modal', { id: 'dia-{{ $d['dia'] }}' })" @endif>
                    <div class="cal-dia-num {{ $d['esHoy'] ? 'hoy' : '' }}">{{ $d['dia'] }}</div>
                    @if($d['total'] > 0)
                        <span class="cal-badge {{ $badgeClass }}">{{ $d['pagadas'] }}/{{ $d['total'] }} cobros</span>
                        <div class="cal-cobros">${{ number_format(collect($d['facturas'])->sum('total_factura'), 0, ',', '.') }}</div>
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
                                        <span class="cal-badge {{ $f['estado'] === 'pagada' ? 'completo' : ($f['estado'] === 'parcial' ? 'parcial' : 'ninguno') }}">{{ ucfirst($f['estado']) }}</span>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </x-filament::modal>
                @endif
            @endif
        @endforeach
    </div>
</x-filament-panels::page>

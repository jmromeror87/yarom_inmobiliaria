<div style="font-family:'Plus Jakarta Sans',system-ui,sans-serif;margin-bottom:4px;">

    {{-- Hero --}}
    <div style="background:linear-gradient(135deg,#0F172A 0%,#1e3a8a 60%,#1e2d45 100%);
                border-radius:18px;padding:20px 28px;margin-bottom:16px;
                display:flex;align-items:center;justify-content:space-between;
                box-shadow:0 8px 28px rgba(15,23,42,.22);position:relative;overflow:hidden;">
        <div style="position:absolute;right:-20px;top:-20px;width:160px;height:160px;border-radius:50%;background:rgba(255,255,255,.03);"></div>
        <div style="display:flex;align-items:center;gap:12px;position:relative;z-index:1;">
            <div style="width:44px;height:44px;background:rgba(255,255,255,.1);border-radius:12px;display:flex;align-items:center;justify-content:center;">
                <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z"/>
                </svg>
            </div>
            <div>
                <p style="font-size:18px;font-weight:900;color:#fff;margin:0;">Plan de Cuentas PUC</p>
                <p style="font-size:11px;color:rgba(255,255,255,.5);margin:2px 0 0;">Haz clic en una clase para filtrar la tabla</p>
            </div>
        </div>
        <div style="position:relative;z-index:1;display:flex;align-items:center;gap:10px;">
            <div style="background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15);border-radius:12px;padding:10px 20px;text-align:center;">
                <div style="font-size:9px;font-weight:700;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.08em;margin-bottom:3px;">Total cuentas</div>
                <div style="font-size:28px;font-weight:900;color:#fff;line-height:1;">{{ $total }}</div>
            </div>
            @if($claseActiva)
            <button wire:click="clearFilter"
                    style="background:rgba(225,29,72,.2);border:1px solid rgba(225,29,72,.4);color:#fb7185;
                           border-radius:10px;padding:8px 16px;font-size:11px;font-weight:700;cursor:pointer;">
                ✕ Quitar filtro
            </button>
            @endif
        </div>
    </div>

    {{-- KPI Cards --}}
    <div style="display:grid;grid-template-columns:repeat(6,1fr);gap:12px;">
        @foreach($clases as $k => $c)
        @php $activa = $claseActiva === $k; @endphp
        <button wire:click="{{ $activa ? 'clearFilter' : 'filterClase(\''.$k.'\')' }}"
                style="background:{{ $activa ? $c['color'] : '#fff' }};
                       border:2px solid {{ $activa ? $c['color'] : $c['bdr'] }};
                       border-radius:14px;padding:16px 12px;text-align:center;cursor:pointer;
                       box-shadow:{{ $activa ? '0 6px 20px '.$c['color'].'55' : '0 2px 8px rgba(15,23,42,.06)' }};
                       transition:all .15s;width:100%;">

            <div style="width:36px;height:36px;background:{{ $activa ? 'rgba(255,255,255,.2)' : $c['bg'] }};
                        border-radius:10px;border:1px solid {{ $activa ? 'rgba(255,255,255,.3)' : $c['bdr'] }};
                        display:flex;align-items:center;justify-content:center;margin:0 auto 10px;">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24"
                     stroke="{{ $activa ? '#fff' : $c['color'] }}" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $c['icon'] }}"/>
                </svg>
            </div>

            <div style="font-size:28px;font-weight:900;color:{{ $activa ? '#fff' : $c['color'] }};
                        line-height:1;margin-bottom:4px;">{{ $c['count'] }}</div>
            <div style="font-size:10px;font-weight:700;color:{{ $activa ? 'rgba(255,255,255,.85)' : '#64748b' }};
                        text-transform:uppercase;letter-spacing:.06em;margin-bottom:2px;">{{ $c['label'] }}</div>
            <div style="font-size:9px;color:{{ $activa ? 'rgba(255,255,255,.6)' : '#94a3b8' }};font-weight:500;">
                Cuentas registradas
            </div>
        </button>
        @endforeach
    </div>

</div>

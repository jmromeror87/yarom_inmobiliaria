<div style="font-family:'Plus Jakarta Sans',system-ui,sans-serif;margin-bottom:4px;">

    {{-- Hero --}}
    <div style="background:linear-gradient(135deg,#0F172A 0%,#1e3a8a 60%,#1e40af 100%);
                border-radius:18px;padding:20px 28px;margin-bottom:0;
                display:flex;align-items:center;justify-content:space-between;
                box-shadow:0 8px 28px rgba(15,23,42,.22);position:relative;overflow:hidden;">

        <div style="position:absolute;right:-20px;top:-20px;width:160px;height:160px;border-radius:50%;background:rgba(255,255,255,.03);"></div>

        {{-- Left --}}
        <div style="display:flex;align-items:center;gap:14px;position:relative;z-index:1;">
            <div style="width:48px;height:48px;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.15);
                        border-radius:13px;display:flex;align-items:center;justify-content:center;">
                <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>
                </svg>
            </div>
            <div>
                <p style="font-size:18px;font-weight:900;color:#fff;margin:0;">Períodos Contables</p>
                <p style="font-size:11px;color:rgba(255,255,255,.5);margin:3px 0 0;font-weight:500;">
                    Control de apertura y cierre contable mensual
                </p>
            </div>
        </div>

        {{-- KPIs --}}
        <div style="position:relative;z-index:1;display:flex;gap:10px;align-items:center;">

            {{-- Período actual --}}
            @if($periodoActual)
            <div style="background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15);border-radius:12px;padding:10px 18px;text-align:center;">
                <div style="font-size:9px;font-weight:700;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.08em;margin-bottom:3px;">Mes actual</div>
                <div style="font-size:14px;font-weight:800;color:#fff;">{{ $mesNames[$periodoActual->mes] }} {{ $periodoActual->anio }}</div>
                <div style="margin-top:4px;">
                    <span style="background:{{ $periodoActual->estado === 'abierto' ? 'rgba(134,239,172,.2)' : 'rgba(148,163,184,.15)' }};
                                 border:1px solid {{ $periodoActual->estado === 'abierto' ? 'rgba(134,239,172,.4)' : 'rgba(148,163,184,.3)' }};
                                 color:{{ $periodoActual->estado === 'abierto' ? '#86efac' : '#94a3b8' }};
                                 border-radius:20px;padding:2px 10px;font-size:9px;font-weight:700;text-transform:uppercase;">
                        {{ $periodoActual->estado === 'abierto' ? 'Abierto' : 'Cerrado' }}
                    </span>
                </div>
            </div>
            @else
            <div style="background:rgba(225,29,72,.1);border:1px solid rgba(225,29,72,.25);border-radius:12px;padding:10px 18px;text-align:center;">
                <div style="font-size:9px;font-weight:700;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.08em;margin-bottom:3px;">Mes actual</div>
                <div style="font-size:12px;font-weight:700;color:#fca5a5;">Sin período abierto</div>
            </div>
            @endif

            {{-- Stats chips --}}
            <div style="display:flex;gap:8px;">
                <div style="background:rgba(134,239,172,.12);border:1px solid rgba(134,239,172,.2);border-radius:12px;padding:10px 16px;text-align:center;min-width:70px;">
                    <div style="font-size:24px;font-weight:900;color:#86efac;line-height:1;">{{ $abiertos }}</div>
                    <div style="font-size:9px;font-weight:700;color:rgba(255,255,255,.45);text-transform:uppercase;letter-spacing:.06em;margin-top:3px;">Abiertos</div>
                </div>
                <div style="background:rgba(148,163,184,.1);border:1px solid rgba(148,163,184,.15);border-radius:12px;padding:10px 16px;text-align:center;min-width:70px;">
                    <div style="font-size:24px;font-weight:900;color:#94a3b8;line-height:1;">{{ $cerrados }}</div>
                    <div style="font-size:9px;font-weight:700;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.06em;margin-top:3px;">Cerrados</div>
                </div>
                <div style="background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.12);border-radius:12px;padding:10px 16px;text-align:center;min-width:70px;">
                    <div style="font-size:24px;font-weight:900;color:#fff;line-height:1;">{{ $comprobantes }}</div>
                    <div style="font-size:9px;font-weight:700;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.06em;margin-top:3px;">Comprobantes</div>
                </div>
            </div>
        </div>
    </div>

</div>

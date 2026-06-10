<div style="font-family:'Plus Jakarta Sans',system-ui,sans-serif;margin-bottom:4px;">

    <div style="background:linear-gradient(135deg,#0F172A 0%,#1e3a8a 60%,#1e40af 100%);
                border-radius:18px;padding:20px 28px;
                display:flex;align-items:center;justify-content:space-between;
                box-shadow:0 8px 28px rgba(15,23,42,.22);position:relative;overflow:hidden;">

        <div style="position:absolute;right:-20px;top:-20px;width:160px;height:160px;border-radius:50%;background:rgba(255,255,255,.03);"></div>

        {{-- Left --}}
        <div style="display:flex;align-items:center;gap:14px;position:relative;z-index:1;">
            <div style="width:48px;height:48px;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.15);
                        border-radius:13px;display:flex;align-items:center;justify-content:center;">
                <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                </svg>
            </div>
            <div>
                <p style="font-size:18px;font-weight:900;color:#fff;margin:0;">Comprobantes Contables</p>
                <p style="font-size:11px;color:rgba(255,255,255,.5);margin:3px 0 0;font-weight:500;">
                    Registro de movimientos contables del sistema
                </p>
            </div>
        </div>

        {{-- KPIs --}}
        <div style="position:relative;z-index:1;display:flex;gap:10px;align-items:center;">

            {{-- Período actual --}}
            @if($periodoActual)
            <div style="background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15);border-radius:12px;padding:10px 16px;text-align:center;">
                <div style="font-size:9px;font-weight:700;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.08em;margin-bottom:3px;">Período actual</div>
                <div style="font-size:13px;font-weight:800;color:#fff;">{{ $periodoActual->nombre }}</div>
                <div style="font-size:20px;font-weight:900;color:#93c5fd;line-height:1.2;">{{ $enPeriodoActual }}</div>
                <div style="font-size:9px;color:rgba(255,255,255,.4);font-weight:600;">comprobantes</div>
            </div>
            @endif

            {{-- Stats --}}
            <div style="background:rgba(134,239,172,.12);border:1px solid rgba(134,239,172,.2);border-radius:12px;padding:10px 16px;text-align:center;min-width:75px;">
                <div style="font-size:26px;font-weight:900;color:#86efac;line-height:1;">{{ $contabilizados }}</div>
                <div style="font-size:9px;font-weight:700;color:rgba(255,255,255,.45);text-transform:uppercase;letter-spacing:.06em;margin-top:3px;">Contabilizados</div>
            </div>

            <div style="background:rgba(251,191,36,.1);border:1px solid rgba(251,191,36,.2);border-radius:12px;padding:10px 16px;text-align:center;min-width:75px;">
                <div style="font-size:26px;font-weight:900;color:#fbbf24;line-height:1;">{{ $borradores }}</div>
                <div style="font-size:9px;font-weight:700;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.06em;margin-top:3px;">Borradores</div>
            </div>

            <div style="background:rgba(148,163,184,.1);border:1px solid rgba(148,163,184,.15);border-radius:12px;padding:10px 16px;text-align:center;min-width:75px;">
                <div style="font-size:26px;font-weight:900;color:#94a3b8;line-height:1;">{{ $anulados }}</div>
                <div style="font-size:9px;font-weight:700;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.06em;margin-top:3px;">Anulados</div>
            </div>

            <div style="background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.12);border-radius:12px;padding:10px 16px;text-align:center;min-width:75px;">
                <div style="font-size:26px;font-weight:900;color:#fff;line-height:1;">{{ $total }}</div>
                <div style="font-size:9px;font-weight:700;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.06em;margin-top:3px;">Total</div>
            </div>
        </div>
    </div>

</div>

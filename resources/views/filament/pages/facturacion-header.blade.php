<div style="font-family:'Plus Jakarta Sans',system-ui,sans-serif;margin-bottom:20px;">

    {{-- Hero navy --}}
    <div style="background:linear-gradient(135deg,#0F172A 0%,#1e3a8a 60%,#1e2d45 100%);
                border-radius:20px;padding:24px 32px;
                display:flex;align-items:center;justify-content:space-between;
                box-shadow:0 8px 32px rgba(15,23,42,.25);position:relative;overflow:hidden;">

        <div style="position:absolute;right:-30px;top:-30px;width:200px;height:200px;border-radius:50%;background:rgba(255,255,255,.03);"></div>
        <div style="position:absolute;right:70px;bottom:-50px;width:140px;height:140px;border-radius:50%;background:rgba(225,29,72,.06);"></div>

        {{-- Título --}}
        <div style="position:relative;z-index:1;display:flex;align-items:center;gap:14px;">
            <div style="width:48px;height:48px;background:rgba(255,255,255,.1);border-radius:14px;
                        display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg width="26" height="26" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25M9 12.75l2.25 2.25L15 9.75m-5.25 2.25H3.375c-.621 0-1.125.504-1.125 1.125v3.026c0 .385.201.74.53.935l3 1.8A2.25 2.25 0 008.25 21h7.5a2.25 2.25 0 001.97-1.164l3-1.8a1.125 1.125 0 00.53-.935V14.25c0-.621-.504-1.125-1.125-1.125H14.25"/>
                </svg>
            </div>
            <div>
                <h1 style="font-size:21px;font-weight:900;color:#fff;margin:0;letter-spacing:-.3px;">Facturación</h1>
                <p style="font-size:12px;color:rgba(255,255,255,.5);margin:3px 0 0;">Período: <strong style="color:rgba(255,255,255,.8);">{{ ucfirst($periodoLabel) }}</strong></p>
            </div>
        </div>

        {{-- KPIs --}}
        <div style="position:relative;z-index:1;display:flex;gap:10px;">

            <div style="background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.12);border-radius:14px;padding:12px 20px;text-align:center;min-width:110px;">
                <div style="font-size:9px;font-weight:700;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.08em;margin-bottom:4px;">Facturado</div>
                <div style="font-size:15px;font-weight:900;color:#fff;line-height:1.2;">{{ $fmt($totalFacturado) }}</div>
            </div>

            <div style="background:rgba(34,197,94,.15);border:1px solid rgba(34,197,94,.3);border-radius:14px;padding:12px 20px;text-align:center;min-width:110px;">
                <div style="font-size:9px;font-weight:700;color:rgba(74,222,128,.6);text-transform:uppercase;letter-spacing:.08em;margin-bottom:4px;">Recaudado</div>
                <div style="font-size:15px;font-weight:900;color:#4ade80;line-height:1.2;">{{ $fmt($totalRecaudado) }}</div>
            </div>

            <div style="background:rgba(225,29,72,.15);border:1px solid rgba(225,29,72,.3);border-radius:14px;padding:12px 20px;text-align:center;min-width:90px;">
                <div style="font-size:9px;font-weight:700;color:rgba(251,113,133,.6);text-transform:uppercase;letter-spacing:.08em;margin-bottom:4px;">Mora</div>
                <div style="font-size:15px;font-weight:900;color:#fb7185;line-height:1.2;">{{ $fmt($totalMora) }}</div>
            </div>

            <div style="background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.12);border-radius:14px;padding:12px 20px;text-align:center;min-width:80px;">
                <div style="font-size:9px;font-weight:700;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.08em;margin-bottom:4px;">Pendientes</div>
                <div style="font-size:26px;font-weight:900;color:#fff;line-height:1;">{{ $pendientes }}</div>
            </div>

            <div style="background:{{ $efectividad >= 90 ? 'rgba(34,197,94,.15)' : ($efectividad >= 70 ? 'rgba(234,179,8,.15)' : 'rgba(225,29,72,.15)') }};
                        border:1px solid {{ $efectividad >= 90 ? 'rgba(34,197,94,.3)' : ($efectividad >= 70 ? 'rgba(234,179,8,.3)' : 'rgba(225,29,72,.3)') }};
                        border-radius:14px;padding:12px 20px;text-align:center;min-width:90px;">
                <div style="font-size:9px;font-weight:700;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.08em;margin-bottom:4px;">Efectividad</div>
                <div style="font-size:22px;font-weight:900;color:{{ $efectividad >= 90 ? '#4ade80' : ($efectividad >= 70 ? '#fbbf24' : '#fb7185') }};line-height:1;">{{ $efectividad }}%</div>
            </div>

        </div>
    </div>

</div>

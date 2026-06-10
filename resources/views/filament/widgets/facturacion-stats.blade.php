<div style="font-family:'Plus Jakarta Sans',system-ui,sans-serif;margin-bottom:4px;">
    <div style="background:linear-gradient(135deg,#0F172A 0%,#1e3a8a 60%,#1e2d45 100%);
                border-radius:18px;padding:22px 28px;
                display:flex;align-items:center;justify-content:space-between;
                box-shadow:0 8px 28px rgba(15,23,42,.22);position:relative;overflow:hidden;">

        <div style="position:absolute;right:-30px;top:-30px;width:180px;height:180px;border-radius:50%;background:rgba(255,255,255,.03);"></div>
        <div style="position:absolute;right:60px;bottom:-50px;width:130px;height:130px;border-radius:50%;background:rgba(225,29,72,.06);"></div>

        <div style="position:relative;z-index:1;display:flex;align-items:center;gap:12px;">
            <div style="width:44px;height:44px;background:rgba(255,255,255,.1);border-radius:12px;display:flex;align-items:center;justify-content:center;">
                <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z"/>
                </svg>
            </div>
            <div>
                <p style="font-size:18px;font-weight:900;color:#fff;margin:0;letter-spacing:-.2px;">Facturación</p>
                <p style="font-size:11px;color:rgba(255,255,255,.5);margin:2px 0 0;">Período: <strong style="color:rgba(255,255,255,.8);">{{ ucfirst($periodoLabel) }}</strong></p>
            </div>
        </div>

        <div style="position:relative;z-index:1;display:flex;gap:10px;flex-wrap:wrap;justify-content:flex-end;">

            <div style="background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.12);border-radius:12px;padding:10px 18px;text-align:center;min-width:100px;">
                <div style="font-size:9px;font-weight:700;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.08em;margin-bottom:3px;">Facturado</div>
                <div style="font-size:13px;font-weight:900;color:#fff;">{{ $fmt($totalFacturado) }}</div>
            </div>

            <div style="background:rgba(34,197,94,.15);border:1px solid rgba(34,197,94,.3);border-radius:12px;padding:10px 18px;text-align:center;min-width:100px;">
                <div style="font-size:9px;font-weight:700;color:rgba(74,222,128,.6);text-transform:uppercase;letter-spacing:.08em;margin-bottom:3px;">Recaudado</div>
                <div style="font-size:13px;font-weight:900;color:#4ade80;">{{ $fmt($totalRecaudado) }}</div>
            </div>

            <div style="background:rgba(225,29,72,.15);border:1px solid rgba(225,29,72,.3);border-radius:12px;padding:10px 18px;text-align:center;min-width:90px;">
                <div style="font-size:9px;font-weight:700;color:rgba(251,113,133,.6);text-transform:uppercase;letter-spacing:.08em;margin-bottom:3px;">Mora</div>
                <div style="font-size:13px;font-weight:900;color:#fb7185;">{{ $fmt($totalMora) }}</div>
            </div>

            <div style="background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.12);border-radius:12px;padding:10px 18px;text-align:center;min-width:70px;">
                <div style="font-size:9px;font-weight:700;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.08em;margin-bottom:3px;">Pendientes</div>
                <div style="font-size:24px;font-weight:900;color:#fff;line-height:1;">{{ $pendientes }}</div>
            </div>

            @php
                $efColor = $efectividad >= 90 ? '#4ade80' : ($efectividad >= 70 ? '#fbbf24' : '#fb7185');
                $efBg    = $efectividad >= 90 ? 'rgba(34,197,94,.15)' : ($efectividad >= 70 ? 'rgba(234,179,8,.15)' : 'rgba(225,29,72,.15)');
                $efBdr   = $efectividad >= 90 ? 'rgba(34,197,94,.3)' : ($efectividad >= 70 ? 'rgba(234,179,8,.3)' : 'rgba(225,29,72,.3)');
            @endphp
            <div style="background:{{ $efBg }};border:1px solid {{ $efBdr }};border-radius:12px;padding:10px 18px;text-align:center;min-width:80px;">
                <div style="font-size:9px;font-weight:700;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.08em;margin-bottom:3px;">Efectividad</div>
                <div style="font-size:22px;font-weight:900;color:{{ $efColor }};line-height:1;">{{ $efectividad }}%</div>
            </div>

        </div>
    </div>
</div>

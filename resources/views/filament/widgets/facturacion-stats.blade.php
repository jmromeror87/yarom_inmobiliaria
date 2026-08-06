<div style="font-family:'Plus Jakarta Sans',system-ui,sans-serif;margin-bottom:4px;">
    <div style="background:linear-gradient(135deg,#0F172A 0%,#1e3a8a 55%,#1e2d45 100%);
                border-radius:20px;padding:24px 26px;
                box-shadow:0 10px 32px rgba(15,23,42,.25);position:relative;overflow:hidden;">

        <div style="position:absolute;right:-40px;top:-40px;width:200px;height:200px;border-radius:50%;background:rgba(255,255,255,.03);"></div>
        <div style="position:absolute;right:80px;bottom:-60px;width:150px;height:150px;border-radius:50%;background:rgba(225,29,72,.07);"></div>
        <div style="position:absolute;left:0;top:0;bottom:0;width:4px;background:linear-gradient(180deg,#f59e0b,#E11D48);"></div>

        <div style="position:relative;z-index:1;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;margin-bottom:18px;">
            <div style="display:flex;align-items:center;gap:12px;">
                <div style="width:46px;height:46px;background:rgba(255,255,255,.1);border-radius:13px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="23" height="23" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z"/>
                    </svg>
                </div>
                <div>
                    <p style="font-size:19px;font-weight:900;color:#fff;margin:0;letter-spacing:-.3px;">Facturación</p>
                    <p style="font-size:11.5px;color:rgba(255,255,255,.55);margin:2px 0 0;">Período: <strong style="color:rgba(255,255,255,.85);">{{ ucfirst($periodoLabel) }}</strong></p>
                </div>
            </div>

            @php
                $efColor = $efectividad >= 90 ? '#4ade80' : ($efectividad >= 70 ? '#fbbf24' : '#fb7185');
                $efBg    = $efectividad >= 90 ? 'rgba(34,197,94,.16)' : ($efectividad >= 70 ? 'rgba(234,179,8,.16)' : 'rgba(225,29,72,.16)');
                $efBdr   = $efectividad >= 90 ? 'rgba(34,197,94,.35)' : ($efectividad >= 70 ? 'rgba(234,179,8,.35)' : 'rgba(225,29,72,.35)');
            @endphp
            <div style="background:{{ $efBg }};border:1px solid {{ $efBdr }};border-radius:14px;padding:10px 20px;text-align:center;">
                <div style="font-size:9.5px;font-weight:800;color:rgba(255,255,255,.6);text-transform:uppercase;letter-spacing:.09em;margin-bottom:2px;">Efectividad de cobro</div>
                <div style="font-size:26px;font-weight:900;color:{{ $efColor }};line-height:1;letter-spacing:-.02em;">{{ $efectividad }}%</div>
            </div>
        </div>

        <div style="position:relative;z-index:1;display:grid;grid-template-columns:repeat(4, minmax(120px, 1fr));gap:10px;">

            <div style="background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.12);border-radius:14px;padding:13px 16px;">
                <div style="display:flex;align-items:center;gap:6px;margin-bottom:6px;">
                    <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="rgba(255,255,255,.55)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0-1 3.75m8.5-3.75 1 3.75m0 0 .5 2.25M9.5 20.25l-.5 2.25" /></svg>
                    <div style="font-size:9.5px;font-weight:800;color:rgba(255,255,255,.55);text-transform:uppercase;letter-spacing:.07em;">Facturado</div>
                </div>
                <div style="font-size:15px;font-weight:900;color:#fff;letter-spacing:-.01em;">{{ $fmt($totalFacturado) }}</div>
            </div>

            <div style="background:rgba(34,197,94,.12);border:1px solid rgba(34,197,94,.28);border-radius:14px;padding:13px 16px;">
                <div style="display:flex;align-items:center;gap:6px;margin-bottom:6px;">
                    <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="#4ade80" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.28m5.94 2.28-2.28 5.941" /></svg>
                    <div style="font-size:9.5px;font-weight:800;color:rgba(74,222,128,.75);text-transform:uppercase;letter-spacing:.07em;">Recaudado</div>
                </div>
                <div style="font-size:15px;font-weight:900;color:#4ade80;letter-spacing:-.01em;">{{ $fmt($totalRecaudado) }}</div>
            </div>

            <div style="background:rgba(225,29,72,.12);border:1px solid rgba(225,29,72,.28);border-radius:14px;padding:13px 16px;">
                <div style="display:flex;align-items:center;gap:6px;margin-bottom:6px;">
                    <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="#fb7185" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" /></svg>
                    <div style="font-size:9.5px;font-weight:800;color:rgba(251,113,133,.8);text-transform:uppercase;letter-spacing:.07em;">Mora</div>
                </div>
                <div style="font-size:15px;font-weight:900;color:#fb7185;letter-spacing:-.01em;">{{ $fmt($totalMora) }}</div>
            </div>

            <div style="background:rgba(251,191,36,.12);border:1px solid rgba(251,191,36,.28);border-radius:14px;padding:13px 16px;">
                <div style="display:flex;align-items:center;gap:6px;margin-bottom:6px;">
                    <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="#fbbf24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                    <div style="font-size:9.5px;font-weight:800;color:rgba(251,191,36,.8);text-transform:uppercase;letter-spacing:.07em;">Pendientes</div>
                </div>
                <div style="font-size:19px;font-weight:900;color:#fbbf24;letter-spacing:-.01em;">{{ $pendientes }}</div>
            </div>

        </div>
    </div>
</div>

<style>
@media (max-width: 640px) {
    .fi-page div[style*="grid-template-columns:repeat(4"] { grid-template-columns: repeat(2, minmax(0, 1fr)) !important; }
}
</style>

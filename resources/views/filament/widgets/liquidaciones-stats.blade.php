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
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                    </svg>
                </div>
                <div>
                    <p style="font-size:19px;font-weight:900;color:#fff;margin:0;letter-spacing:-.3px;">Liquidaciones</p>
                    <p style="font-size:11.5px;color:rgba(255,255,255,.55);margin:2px 0 0;">Período: <strong style="color:rgba(255,255,255,.85);">{{ ucfirst($periodoLabel) }}</strong></p>
                </div>
            </div>

            <div style="background:rgba(251,191,36,.16);border:1px solid rgba(251,191,36,.35);border-radius:14px;padding:10px 20px;text-align:center;">
                <div style="font-size:9.5px;font-weight:800;color:rgba(255,255,255,.6);text-transform:uppercase;letter-spacing:.09em;margin-bottom:2px;">Por girar (pend. + aprob.)</div>
                <div style="font-size:22px;font-weight:900;color:#fbbf24;line-height:1;letter-spacing:-.02em;">{{ $fmt($porGirar) }}</div>
            </div>
        </div>

        <div style="position:relative;z-index:1;display:grid;grid-template-columns:repeat(4, minmax(120px, 1fr));gap:10px;">

            <a href="{{ \App\Filament\Resources\OwnerLiquidations\OwnerLiquidationResource::getUrl('index') }}?filtro=pendiente" style="text-decoration:none;background:rgba(251,191,36,.12);border:1px solid rgba(251,191,36,.28);border-radius:14px;padding:13px 16px;display:block;transition:background .15s ease,transform .15s ease;cursor:pointer;" onmouseover="this.style.background='rgba(251,191,36,.2)';this.style.transform='translateY(-1px)';" onmouseout="this.style.background='rgba(251,191,36,.12)';this.style.transform='none';">
                <div style="display:flex;align-items:center;gap:6px;margin-bottom:6px;">
                    <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="#fbbf24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                    <div style="font-size:9.5px;font-weight:800;color:rgba(251,191,36,.8);text-transform:uppercase;letter-spacing:.07em;">Pendientes</div>
                </div>
                <div style="font-size:15px;font-weight:900;color:#fbbf24;letter-spacing:-.01em;">{{ $fmt($pendienteSuma) }}</div>
                <div style="font-size:10px;color:rgba(255,255,255,.45);margin-top:2px;">{{ $pendienteCount }} liquidación{{ $pendienteCount === 1 ? '' : 'es' }}</div>
            </a>

            <a href="{{ \App\Filament\Resources\OwnerLiquidations\OwnerLiquidationResource::getUrl('index') }}?filtro=aprobada" style="text-decoration:none;background:rgba(56,144,255,.12);border:1px solid rgba(56,144,255,.28);border-radius:14px;padding:13px 16px;display:block;transition:background .15s ease,transform .15s ease;cursor:pointer;" onmouseover="this.style.background='rgba(56,144,255,.2)';this.style.transform='translateY(-1px)';" onmouseout="this.style.background='rgba(56,144,255,.12)';this.style.transform='none';">
                <div style="display:flex;align-items:center;gap:6px;margin-bottom:6px;">
                    <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="#60a5fa" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m9 12.75 2.25 2.25 6-6m6 3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                    <div style="font-size:9.5px;font-weight:800;color:rgba(96,165,250,.85);text-transform:uppercase;letter-spacing:.07em;">Aprobadas</div>
                </div>
                <div style="font-size:15px;font-weight:900;color:#60a5fa;letter-spacing:-.01em;">{{ $fmt($aprobadaSuma) }}</div>
                <div style="font-size:10px;color:rgba(255,255,255,.45);margin-top:2px;">{{ $aprobadaCount }} liquidación{{ $aprobadaCount === 1 ? '' : 'es' }}</div>
            </a>

            <a href="{{ \App\Filament\Resources\OwnerLiquidations\OwnerLiquidationResource::getUrl('index') }}?filtro=pagada" style="text-decoration:none;background:rgba(34,197,94,.12);border:1px solid rgba(34,197,94,.28);border-radius:14px;padding:13px 16px;display:block;transition:background .15s ease,transform .15s ease;cursor:pointer;" onmouseover="this.style.background='rgba(34,197,94,.2)';this.style.transform='translateY(-1px)';" onmouseout="this.style.background='rgba(34,197,94,.12)';this.style.transform='none';">
                <div style="display:flex;align-items:center;gap:6px;margin-bottom:6px;">
                    <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="#4ade80" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.28m5.94 2.28-2.28 5.941" /></svg>
                    <div style="font-size:9.5px;font-weight:800;color:rgba(74,222,128,.75);text-transform:uppercase;letter-spacing:.07em;">Pagadas (mes)</div>
                </div>
                <div style="font-size:15px;font-weight:900;color:#4ade80;letter-spacing:-.01em;">{{ $fmt($pagadaMesSuma) }}</div>
                <div style="font-size:10px;color:rgba(255,255,255,.45);margin-top:2px;">{{ $pagadaMesCount }} liquidación{{ $pagadaMesCount === 1 ? '' : 'es' }}</div>
            </a>

            <a href="{{ \App\Filament\Resources\OwnerLiquidations\OwnerLiquidationResource::getUrl('index') }}?filtro=anulada" style="text-decoration:none;background:rgba(225,29,72,.12);border:1px solid rgba(225,29,72,.28);border-radius:14px;padding:13px 16px;display:block;transition:background .15s ease,transform .15s ease;cursor:pointer;" onmouseover="this.style.background='rgba(225,29,72,.2)';this.style.transform='translateY(-1px)';" onmouseout="this.style.background='rgba(225,29,72,.12)';this.style.transform='none';">
                <div style="display:flex;align-items:center;gap:6px;margin-bottom:6px;">
                    <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="#fb7185" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                    <div style="font-size:9.5px;font-weight:800;color:rgba(251,113,133,.8);text-transform:uppercase;letter-spacing:.07em;">Anuladas</div>
                </div>
                <div style="font-size:19px;font-weight:900;color:#fb7185;letter-spacing:-.01em;">{{ $anuladaCount }}</div>
            </a>

        </div>
    </div>
</div>

<style>
@media (max-width: 640px) {
    .fi-page div[style*="grid-template-columns:repeat(4"] { grid-template-columns: repeat(2, minmax(0, 1fr)) !important; }
}
</style>

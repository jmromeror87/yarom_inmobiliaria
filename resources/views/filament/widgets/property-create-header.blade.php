<div style="background:linear-gradient(135deg,#0F172A 0%,#1e2d45 55%,#1a1f3a 100%);border-radius:1.25rem;padding:24px 32px;margin-bottom:16px;display:flex;align-items:center;gap:24px;position:relative;overflow:hidden;">

    <div style="position:absolute;right:-30px;top:-30px;width:180px;height:180px;border-radius:50%;background:radial-gradient(circle,rgba(225,29,72,.15),transparent 70%);pointer-events:none;"></div>

    <div style="width:64px;height:64px;border-radius:18px;background:linear-gradient(135deg,#1e3a8a,#E11D48);display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 8px 24px rgba(225,29,72,.28);">
        <svg width="30" height="30" fill="none" viewBox="0 0 24 24" stroke="#fff" stroke-width="1.7">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 9.75L12 3l9 6.75V21H15v-6H9v6H3V9.75z"/>
        </svg>
    </div>

    <div style="flex:1;">
        <h2 style="font-size:18px;font-weight:900;color:#fff;margin:0;letter-spacing:-.02em;">Nuevo Inmueble</h2>
        <p style="font-size:12.5px;color:rgba(255,255,255,.55);margin:5px 0 0;font-weight:400;line-height:1.5;">
            Complete la información del inmueble. Los datos se usarán en contratos, facturas y reportes del portafolio.
        </p>
    </div>

    <div style="display:flex;gap:8px;flex-shrink:0;flex-wrap:wrap;justify-content:flex-end;">
        @foreach(['Tipo','Propietario','Ubicación','Características','Documentos','Financiero','CTL'] as $i => $step)
            <div style="display:flex;align-items:center;gap:5px;">
                <div style="width:22px;height:22px;border-radius:50%;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;">
                    <span style="font-size:9px;font-weight:800;color:rgba(255,255,255,.7);">{{ $i+1 }}</span>
                </div>
                <span style="font-size:9.5px;color:rgba(255,255,255,.5);font-weight:600;white-space:nowrap;">{{ $step }}</span>
            </div>
        @endforeach
    </div>

</div>

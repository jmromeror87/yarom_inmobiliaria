<div style="background:linear-gradient(135deg,#0F172A 0%,#1e2d45 55%,#1a1f3a 100%);border-radius:1.25rem;padding:24px 32px;margin-bottom:16px;display:flex;align-items:center;gap:24px;position:relative;overflow:hidden;">

    {{-- Decoración --}}
    <div style="position:absolute;right:-30px;top:-30px;width:180px;height:180px;border-radius:50%;background:radial-gradient(circle,rgba(225,29,72,.15),transparent 70%);pointer-events:none;"></div>

    {{-- Icono --}}
    <div style="width:64px;height:64px;border-radius:18px;background:linear-gradient(135deg,#1e3a8a,#E11D48);display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 8px 24px rgba(225,29,72,.28);">
        <svg width="30" height="30" fill="none" viewBox="0 0 24 24" stroke="#fff" stroke-width="1.7">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21c-2.331 0-4.512-.645-6.374-1.766z"/>
        </svg>
    </div>

    {{-- Texto --}}
    <div style="flex:1;">
        <h2 style="font-size:18px;font-weight:900;color:#fff;margin:0;letter-spacing:-.02em;">Nuevo Tercero</h2>
        <p style="font-size:12.5px;color:rgba(255,255,255,.55);margin:5px 0 0;font-weight:400;line-height:1.5;">
            Complete los pasos del asistente. Los campos marcados son obligatorios. Los datos se guardan bajo la <strong style="color:rgba(255,255,255,.8);">Ley 1581/2012</strong> de Habeas Data.
        </p>
    </div>

    {{-- Pasos resumen --}}
    <div style="display:flex;gap:8px;flex-shrink:0;flex-wrap:wrap;justify-content:flex-end;">
        @foreach(['Roles', 'Datos', 'Contacto', 'Dirección', 'Laboral', 'Bancario', 'Habeas Data'] as $i => $step)
            <div style="display:flex;align-items:center;gap:5px;">
                <div style="width:22px;height:22px;border-radius:50%;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;">
                    <span style="font-size:9px;font-weight:800;color:rgba(255,255,255,.7);">{{ $i+1 }}</span>
                </div>
                <span style="font-size:9.5px;color:rgba(255,255,255,.5);font-weight:600;white-space:nowrap;">{{ $step }}</span>
            </div>
        @endforeach
    </div>

</div>

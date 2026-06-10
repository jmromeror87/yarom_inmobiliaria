<div>
    {{-- KPI Grid --}}
    <div style="display:grid;grid-template-columns:repeat(5,1fr);gap:14px;margin-bottom:14px;">

        {{-- Total Inmuebles --}}
        <div wire:click="filterTable('estado', '')" style="cursor:pointer;background:#fff;border-radius:14px;border-left:5px solid #0F172A;padding:18px 20px;box-shadow:0 2px 10px rgba(15,23,42,.07);display:flex;flex-direction:column;gap:4px;height:110px;justify-content:space-between;transition:box-shadow .15s;position:relative;"
             onmouseover="this.style.boxShadow='0 4px 18px rgba(15,23,42,.13)'" onmouseout="this.style.boxShadow='0 2px 10px rgba(15,23,42,.07)'">
            <div style="position:absolute;top:14px;right:14px;width:34px;height:34px;background:#f1f5f9;border-radius:9px;display:flex;align-items:center;justify-content:center;">
                <svg width="17" height="17" fill="none" viewBox="0 0 24 24" stroke="#0F172A" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9.75L12 3l9 6.75V21H15v-6H9v6H3V9.75z"/></svg>
            </div>
            <span style="font-size:9px;font-weight:800;color:#94a3b8;letter-spacing:.08em;text-transform:uppercase;">Total Inmuebles</span>
            <span style="font-size:38px;font-weight:900;color:#0F172A;line-height:1;">{{ $total }}</span>
            <span style="font-size:10px;color:#64748b;font-weight:600;">{{ $ocupacion }}% ocupación</span>
        </div>

        {{-- Disponibles --}}
        <div wire:click="filterTable('estado', 'disponible')" style="cursor:pointer;background:#fff;border-radius:14px;border-left:5px solid #16a34a;padding:18px 20px;box-shadow:0 2px 10px rgba(15,23,42,.07);display:flex;flex-direction:column;gap:4px;height:110px;justify-content:space-between;transition:box-shadow .15s;position:relative;"
             onmouseover="this.style.boxShadow='0 4px 18px rgba(22,163,74,.18)'" onmouseout="this.style.boxShadow='0 2px 10px rgba(15,23,42,.07)'">
            <div style="position:absolute;top:14px;right:14px;width:34px;height:34px;background:#dcfce7;border-radius:9px;display:flex;align-items:center;justify-content:center;">
                <svg width="17" height="17" fill="none" viewBox="0 0 24 24" stroke="#16a34a" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <span style="font-size:9px;font-weight:800;color:#94a3b8;letter-spacing:.08em;text-transform:uppercase;">Disponibles</span>
            <span style="font-size:38px;font-weight:900;color:#16a34a;line-height:1;">{{ $disponibles }}</span>
            <span style="font-size:10px;color:#64748b;font-weight:600;">Listos para arrendar/vender</span>
        </div>

        {{-- Arrendados --}}
        <div wire:click="filterTable('estado', 'arrendado')" style="cursor:pointer;background:#fff;border-radius:14px;border-left:5px solid #2563EB;padding:18px 20px;box-shadow:0 2px 10px rgba(15,23,42,.07);display:flex;flex-direction:column;gap:4px;height:110px;justify-content:space-between;transition:box-shadow .15s;position:relative;"
             onmouseover="this.style.boxShadow='0 4px 18px rgba(37,99,235,.18)'" onmouseout="this.style.boxShadow='0 2px 10px rgba(15,23,42,.07)'">
            <div style="position:absolute;top:14px;right:14px;width:34px;height:34px;background:#dbeafe;border-radius:9px;display:flex;align-items:center;justify-content:center;">
                <svg width="17" height="17" fill="none" viewBox="0 0 24 24" stroke="#2563EB" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z"/></svg>
            </div>
            <span style="font-size:9px;font-weight:800;color:#94a3b8;letter-spacing:.08em;text-transform:uppercase;">Arrendados</span>
            <span style="font-size:38px;font-weight:900;color:#2563EB;line-height:1;">{{ $arrendados }}</span>
            <span style="font-size:10px;color:#64748b;font-weight:600;">${{ number_format($canonTotal, 0, ',', '.') }} COP/mes</span>
        </div>

        {{-- En Venta --}}
        <div wire:click="filterTable('estado', 'en_venta')" style="cursor:pointer;background:#fff;border-radius:14px;border-left:5px solid #f59e0b;padding:18px 20px;box-shadow:0 2px 10px rgba(15,23,42,.07);display:flex;flex-direction:column;gap:4px;height:110px;justify-content:space-between;transition:box-shadow .15s;position:relative;"
             onmouseover="this.style.boxShadow='0 4px 18px rgba(245,158,11,.18)'" onmouseout="this.style.boxShadow='0 2px 10px rgba(15,23,42,.07)'">
            <div style="position:absolute;top:14px;right:14px;width:34px;height:34px;background:#fef3c7;border-radius:9px;display:flex;align-items:center;justify-content:center;">
                <svg width="17" height="17" fill="none" viewBox="0 0 24 24" stroke="#f59e0b" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14.25l6-6m4.5-3.493V21.75l-3.75-1.5-3.75 1.5-3.75-1.5-3.75 1.5V4.757c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0c1.1.128 1.907 1.077 1.907 2.185z"/></svg>
            </div>
            <span style="font-size:9px;font-weight:800;color:#94a3b8;letter-spacing:.08em;text-transform:uppercase;">En Venta</span>
            <span style="font-size:38px;font-weight:900;color:#f59e0b;line-height:1;">{{ $enVenta }}</span>
            <span style="font-size:10px;color:#64748b;font-weight:600;">Ofertados para venta</span>
        </div>

        {{-- Captación / Mantenimiento --}}
        <div wire:click="filterTable('estado', 'en_captacion')" style="cursor:pointer;background:#fff;border-radius:14px;border-left:5px solid #E11D48;padding:18px 20px;box-shadow:0 2px 10px rgba(15,23,42,.07);display:flex;flex-direction:column;gap:4px;height:110px;justify-content:space-between;transition:box-shadow .15s;position:relative;"
             onmouseover="this.style.boxShadow='0 4px 18px rgba(225,29,72,.18)'" onmouseout="this.style.boxShadow='0 2px 10px rgba(15,23,42,.07)'">
            <div style="position:absolute;top:14px;right:14px;width:34px;height:34px;background:#ffe4e6;border-radius:9px;display:flex;align-items:center;justify-content:center;">
                <svg width="17" height="17" fill="none" viewBox="0 0 24 24" stroke="#E11D48" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
            </div>
            <span style="font-size:9px;font-weight:800;color:#94a3b8;letter-spacing:.08em;text-transform:uppercase;">En Captación</span>
            <span style="font-size:38px;font-weight:900;color:#E11D48;line-height:1;">{{ $captacion }}</span>
            <span style="font-size:10px;color:#64748b;font-weight:600;">{{ $mantenimiento }} en mantenimiento</span>
        </div>

    </div>

    {{-- Barra inferior: Ver todos --}}
    <div wire:click="clearFilter()" style="cursor:pointer;background:linear-gradient(135deg,#0F172A,#1e2d45);border-radius:12px;padding:11px 20px;display:flex;align-items:center;justify-content:space-between;transition:opacity .15s;"
         onmouseover="this.style.opacity='.88'" onmouseout="this.style.opacity='1'">
        <div style="display:flex;align-items:center;gap:10px;">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="rgba(255,255,255,.6)" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/></svg>
            <span style="font-size:11px;font-weight:700;color:rgba(255,255,255,.7);letter-spacing:.04em;">Clic aquí para ver todos los inmuebles · Portafolio completo</span>
        </div>
        <span style="font-size:11px;font-weight:800;color:rgba(255,255,255,.5);">{{ $total }} inmuebles en total</span>
    </div>
</div>

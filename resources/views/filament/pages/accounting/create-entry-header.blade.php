<div style="font-family:'Plus Jakarta Sans',system-ui,sans-serif;">

    {{-- Back + breadcrumb --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:8px;">
        <a href="{{ \App\Filament\Resources\Accounting\AccountingEntryResource::getUrl('index') }}"
           style="display:inline-flex;align-items:center;gap:6px;font-size:12px;font-weight:600;
                  color:#64748b;text-decoration:none;padding:6px 12px;border-radius:8px;
                  border:1px solid #e2e8f0;background:#fff;">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/>
            </svg>
            Comprobantes
        </a>

        {{-- Tipos de comprobante chips --}}
        <div style="display:flex;gap:6px;flex-wrap:wrap;">
            @foreach(['CC'=>['Contabilidad','#64748b'],'CI'=>['Ingreso','#16a34a'],'CE'=>['Egreso','#E11D48'],'CR'=>['Recaudo','#0284c7'],'ND'=>['Nota Déb.','#d97706'],'NC'=>['Nota Cred.','#7c3aed'],'CA'=>['Ajuste','#475569']] as $cod=>[$nombre,$color])
            <span style="background:{{ $color }}15;border:1px solid {{ $color }}35;color:{{ $color }};
                         border-radius:20px;padding:3px 10px;font-size:10px;font-weight:700;">
                {{ $cod }} — {{ $nombre }}
            </span>
            @endforeach
        </div>
    </div>

    {{-- Hero Banner --}}
    <div style="background:linear-gradient(135deg,#0F172A 0%,#1e3a8a 55%,#1e40af 100%);
                border-radius:18px;padding:20px 28px;margin-bottom:20px;
                display:flex;align-items:center;justify-content:space-between;
                box-shadow:0 8px 28px rgba(15,23,42,.22);position:relative;overflow:hidden;">

        <div style="position:absolute;right:-30px;top:-30px;width:180px;height:180px;border-radius:50%;background:rgba(255,255,255,.04);"></div>

        <div style="display:flex;align-items:center;gap:16px;position:relative;z-index:1;">
            <div style="width:52px;height:52px;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.15);
                        border-radius:14px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg width="26" height="26" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p style="font-size:20px;font-weight:900;color:#fff;margin:0;letter-spacing:-.3px;">Nuevo Comprobante Contable</p>
                <p style="font-size:12px;color:rgba(255,255,255,.5);margin:4px 0 0;font-weight:500;">
                    Complete el encabezado y los movimientos. El comprobante debe cuadrar (Débitos = Créditos).
                </p>
            </div>
        </div>

        {{-- Tips --}}
        <div style="position:relative;z-index:1;display:flex;flex-direction:column;gap:6px;align-items:flex-end;">
            <div style="display:flex;align-items:center;gap:6px;background:rgba(255,255,255,.08);
                        border:1px solid rgba(255,255,255,.12);border-radius:8px;padding:6px 12px;">
                <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="#86efac" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2m6-2a10 10 0 11-20 0 10 10 0 0120 0z"/>
                </svg>
                <span style="font-size:10px;color:rgba(255,255,255,.7);font-weight:600;">Débitos = Créditos para cuadrar</span>
            </div>
            <div style="display:flex;align-items:center;gap:6px;background:rgba(255,255,255,.08);
                        border:1px solid rgba(255,255,255,.12);border-radius:8px;padding:6px 12px;">
                <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="#93c5fd" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span style="font-size:10px;color:rgba(255,255,255,.7);font-weight:600;">Solo cuentas nivel 4 aceptan movimiento</span>
            </div>
        </div>
    </div>

</div>

<div style="font-family:'Plus Jakarta Sans',system-ui,sans-serif;">

    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:8px;">
        <a href="{{ \App\Filament\Resources\PropertyServices\PropertyServiceResource::getUrl('index') }}"
           style="display:inline-flex;align-items:center;gap:6px;font-size:12px;font-weight:600;
                  color:#64748b;text-decoration:none;padding:6px 12px;border-radius:8px;
                  border:1px solid #e2e8f0;background:#fff;">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/>
            </svg>
            Servicios / Mantenimientos
        </a>

        {{-- Tipos chip --}}
        <div style="display:flex;gap:6px;flex-wrap:wrap;">
            @foreach([
                ['Mantenimiento','#0284c7'],['Reparación','#d97706'],
                ['Remodelación','#7c3aed'],['Limpieza','#16a34a'],
                ['Inspección','#64748b'],['Otro','#475569'],
            ] as [$label,$color])
            <span style="background:{{ $color }}15;border:1px solid {{ $color }}35;color:{{ $color }};
                         border-radius:20px;padding:3px 10px;font-size:10px;font-weight:700;">
                {{ $label }}
            </span>
            @endforeach
        </div>
    </div>

    {{-- Hero Banner --}}
    <div style="background:linear-gradient(135deg,#0F172A 0%,#0369a1 55%,#0284c7 100%);
                border-radius:18px;padding:20px 28px;margin-bottom:20px;
                display:flex;align-items:center;justify-content:space-between;
                box-shadow:0 8px 28px rgba(15,23,42,.22);position:relative;overflow:hidden;">

        <div style="position:absolute;right:-30px;top:-30px;width:180px;height:180px;border-radius:50%;background:rgba(255,255,255,.04);"></div>

        <div style="display:flex;align-items:center;gap:16px;position:relative;z-index:1;">
            <div style="width:52px;height:52px;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.15);
                        border-radius:14px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg width="26" height="26" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437l1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008z"/>
                </svg>
            </div>
            <div>
                <p style="font-size:20px;font-weight:900;color:#fff;margin:0;letter-spacing:-.3px;">Nuevo Servicio / Mantenimiento</p>
                <p style="font-size:12px;color:rgba(255,255,255,.5);margin:4px 0 0;font-weight:500;">
                    Registre el servicio, el proveedor y cómo se recupera el costo
                </p>
            </div>
        </div>

        {{-- Vías de cobro --}}
        <div style="position:relative;z-index:1;display:flex;flex-direction:column;gap:6px;align-items:flex-end;">
            <div style="display:flex;align-items:center;gap:6px;background:rgba(255,255,255,.08);
                        border:1px solid rgba(255,255,255,.12);border-radius:8px;padding:6px 12px;">
                <svg width="12" height="12" fill="#fbbf24" viewBox="0 0 24 24"><path d="M12 2a10 10 0 100 20A10 10 0 0012 2zm1 14H11v-2h2v2zm0-4H11V7h2v5z"/></svg>
                <span style="font-size:10px;color:rgba(255,255,255,.75);font-weight:600;">Propietario: descuenta en liquidación</span>
            </div>
            <div style="display:flex;align-items:center;gap:6px;background:rgba(255,255,255,.08);
                        border:1px solid rgba(255,255,255,.12);border-radius:8px;padding:6px 12px;">
                <svg width="12" height="12" fill="#86efac" viewBox="0 0 24 24"><path d="M12 2a10 10 0 100 20A10 10 0 0012 2zm1 14H11v-2h2v2zm0-4H11V7h2v5z"/></svg>
                <span style="font-size:10px;color:rgba(255,255,255,.75);font-weight:600;">Inquilino: pago directo</span>
            </div>
            <div style="display:flex;align-items:center;gap:6px;background:rgba(255,255,255,.08);
                        border:1px solid rgba(255,255,255,.12);border-radius:8px;padding:6px 12px;">
                <svg width="12" height="12" fill="#93c5fd" viewBox="0 0 24 24"><path d="M12 2a10 10 0 100 20A10 10 0 0012 2zm1 14H11v-2h2v2zm0-4H11V7h2v5z"/></svg>
                <span style="font-size:10px;color:rgba(255,255,255,.75);font-weight:600;">Deducción: resta del canon</span>
            </div>
        </div>
    </div>

</div>

<div style="font-family:'Plus Jakarta Sans',system-ui,sans-serif;">

    {{-- Breadcrumb row (Filament standard breadcrumbs go here by default, we replicate) --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:8px;">

        {{-- Back link --}}
        <a href="{{ \App\Filament\Resources\Accounting\AccountingAccountResource::getUrl('index') }}"
           style="display:inline-flex;align-items:center;gap:6px;font-size:12px;font-weight:600;
                  color:#64748b;text-decoration:none;padding:6px 12px;border-radius:8px;
                  border:1px solid #e2e8f0;background:#fff;
                  transition:all .15s;"
           onmouseover="this.style.color='#1e3a8a';this.style.borderColor='#93c5fd';"
           onmouseout="this.style.color='#64748b';this.style.borderColor='#e2e8f0';">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/>
            </svg>
            Plan de Cuentas
        </a>

        {{-- Step indicator --}}
        <div style="display:flex;align-items:center;gap:6px;">
            <div style="width:24px;height:24px;border-radius:50%;background:linear-gradient(135deg,#1e3a8a,#2563eb);
                        display:flex;align-items:center;justify-content:center;">
                <span style="font-size:10px;font-weight:800;color:#fff;">1</span>
            </div>
            <span style="font-size:11px;font-weight:600;color:#1e3a8a;">Datos de la cuenta</span>
            <div style="width:24px;height:1px;background:#e2e8f0;"></div>
            <div style="width:24px;height:24px;border-radius:50%;background:#f1f5f9;border:1px solid #e2e8f0;
                        display:flex;align-items:center;justify-content:center;">
                <span style="font-size:10px;font-weight:700;color:#94a3b8;">2</span>
            </div>
            <span style="font-size:11px;color:#94a3b8;">Guardar</span>
        </div>
    </div>

    {{-- Hero Banner --}}
    <div style="background:linear-gradient(135deg,#0F172A 0%,#1e3a8a 55%,#1e40af 100%);
                border-radius:18px;padding:22px 28px;margin-bottom:20px;
                display:flex;align-items:center;justify-content:space-between;
                box-shadow:0 8px 28px rgba(15,23,42,.22);position:relative;overflow:hidden;">

        {{-- Decorative circles --}}
        <div style="position:absolute;right:-30px;top:-30px;width:180px;height:180px;border-radius:50%;background:rgba(255,255,255,.04);"></div>
        <div style="position:absolute;right:60px;bottom:-40px;width:100px;height:100px;border-radius:50%;background:rgba(37,99,235,.15);"></div>

        {{-- Left: icon + title --}}
        <div style="display:flex;align-items:center;gap:16px;position:relative;z-index:1;">
            <div style="width:52px;height:52px;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.15);
                        border-radius:14px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg width="26" height="26" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p style="font-size:20px;font-weight:900;color:#fff;margin:0;letter-spacing:-.3px;">Nueva Cuenta Contable</p>
                <p style="font-size:12px;color:rgba(255,255,255,.55);margin:4px 0 0;font-weight:500;">
                    Plan Único de Cuentas (PUC) — Colombia
                </p>
            </div>
        </div>

        {{-- Right: tip chips --}}
        <div style="position:relative;z-index:1;display:flex;flex-direction:column;gap:6px;align-items:flex-end;">
            <div style="display:flex;align-items:center;gap:6px;background:rgba(255,255,255,.08);
                        border:1px solid rgba(255,255,255,.12);border-radius:8px;padding:6px 12px;">
                <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="#93c5fd" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 001.5-.189m-1.5.189a6.01 6.01 0 01-1.5-.189m3.75 7.478a12.06 12.06 0 01-4.5 0m3.75 2.355a10.5 10.5 0 01-3.75 0M12 3v1.5m0 0a9 9 0 100 13.5A9 9 0 0012 4.5z"/>
                </svg>
                <span style="font-size:10px;color:rgba(255,255,255,.7);font-weight:600;">Nivel 4 = acepta movimientos</span>
            </div>
            <div style="display:flex;align-items:center;gap:6px;background:rgba(255,255,255,.08);
                        border:1px solid rgba(255,255,255,.12);border-radius:8px;padding:6px 12px;">
                <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="#86efac" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75L22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3l-4.5 16.5"/>
                </svg>
                <span style="font-size:10px;color:rgba(255,255,255,.7);font-weight:600;">Código 6 dígitos = subcuenta</span>
            </div>
        </div>
    </div>

    {{-- Info strip: clases PUC --}}
    <div style="display:grid;grid-template-columns:repeat(6,1fr);gap:8px;margin-bottom:20px;">
        @php
        $clasesPuc = [
            ['num'=>'1','nombre'=>'Activo',    'color'=>'#16a34a','bg'=>'#f0fdf4','bdr'=>'#bbf7d0'],
            ['num'=>'2','nombre'=>'Pasivo',    'color'=>'#E11D48','bg'=>'#fff1f2','bdr'=>'#fecdd3'],
            ['num'=>'3','nombre'=>'Patrimonio','color'=>'#7c3aed','bg'=>'#f5f3ff','bdr'=>'#ddd6fe'],
            ['num'=>'4','nombre'=>'Ingreso',   'color'=>'#0284c7','bg'=>'#f0f9ff','bdr'=>'#bae6fd'],
            ['num'=>'5','nombre'=>'Gasto',     'color'=>'#d97706','bg'=>'#fffbeb','bdr'=>'#fde68a'],
            ['num'=>'6','nombre'=>'Costo',     'color'=>'#64748b','bg'=>'#f8fafc','bdr'=>'#e2e8f0'],
        ];
        @endphp
        @foreach($clasesPuc as $cl)
        <div style="background:{{ $cl['bg'] }};border:1px solid {{ $cl['bdr'] }};border-radius:10px;
                    padding:10px 8px;text-align:center;">
            <div style="font-size:18px;font-weight:900;color:{{ $cl['color'] }};line-height:1;">{{ $cl['num'] }}</div>
            <div style="font-size:9px;font-weight:700;color:{{ $cl['color'] }};text-transform:uppercase;
                        letter-spacing:.05em;margin-top:3px;">{{ $cl['nombre'] }}</div>
        </div>
        @endforeach
    </div>

</div>

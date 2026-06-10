<div style="font-family:'Plus Jakarta Sans',system-ui,sans-serif;">

    {{-- Top row: back + actions --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:8px;">

        <a href="{{ \App\Filament\Resources\Accounting\AccountingAccountResource::getUrl('index') }}"
           style="display:inline-flex;align-items:center;gap:6px;font-size:12px;font-weight:600;
                  color:#64748b;text-decoration:none;padding:6px 12px;border-radius:8px;
                  border:1px solid #e2e8f0;background:#fff;">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/>
            </svg>
            Plan de Cuentas
        </a>

        {{-- Nivel badge --}}
        @php
        $nivelLabels = [1=>'Clase',2=>'Grupo',3=>'Cuenta',4=>'Subcuenta'];
        $nivelColors = [1=>'#E11D48',2=>'#d97706',3=>'#0284c7',4=>'#16a34a'];
        $nivel = $record?->nivel ?? 1;
        $claseNames = ['1'=>'Activo','2'=>'Pasivo','3'=>'Patrimonio','4'=>'Ingreso','5'=>'Gasto','6'=>'Costo'];
        $claseColors = ['1'=>'#16a34a','2'=>'#E11D48','3'=>'#7c3aed','4'=>'#0284c7','5'=>'#d97706','6'=>'#64748b'];
        $clase = $record?->clase ?? '1';
        @endphp
        <div style="display:flex;align-items:center;gap:8px;">
            <span style="background:{{ $claseColors[$clase] ?? '#64748b' }}15;border:1px solid {{ $claseColors[$clase] ?? '#64748b' }}40;
                         color:{{ $claseColors[$clase] ?? '#64748b' }};border-radius:20px;
                         padding:4px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;">
                Clase {{ $clase }} — {{ $claseNames[$clase] ?? '' }}
            </span>
            <span style="background:{{ $nivelColors[$nivel] ?? '#64748b' }}15;border:1px solid {{ $nivelColors[$nivel] ?? '#64748b' }}40;
                         color:{{ $nivelColors[$nivel] ?? '#64748b' }};border-radius:20px;
                         padding:4px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;">
                Nivel {{ $nivel }} — {{ $nivelLabels[$nivel] ?? '' }}
            </span>
            <span style="background:{{ $record?->estado === 'activo' ? '#f0fdf4' : '#f8fafc' }};
                         border:1px solid {{ $record?->estado === 'activo' ? '#bbf7d0' : '#e2e8f0' }};
                         color:{{ $record?->estado === 'activo' ? '#16a34a' : '#64748b' }};
                         border-radius:20px;padding:4px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;">
                {{ ucfirst($record?->estado ?? 'activo') }}
            </span>
        </div>
    </div>

    {{-- Hero Banner --}}
    <div style="background:linear-gradient(135deg,#0F172A 0%,#1e3a8a 55%,#1e40af 100%);
                border-radius:18px;padding:22px 28px;margin-bottom:20px;
                display:flex;align-items:center;justify-content:space-between;
                box-shadow:0 8px 28px rgba(15,23,42,.22);position:relative;overflow:hidden;">

        <div style="position:absolute;right:-30px;top:-30px;width:180px;height:180px;border-radius:50%;background:rgba(255,255,255,.04);"></div>
        <div style="position:absolute;right:60px;bottom:-40px;width:100px;height:100px;border-radius:50%;background:rgba(37,99,235,.12);"></div>

        {{-- Left --}}
        <div style="display:flex;align-items:center;gap:16px;position:relative;z-index:1;">
            <div style="width:52px;height:52px;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.15);
                        border-radius:14px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg width="26" height="26" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/>
                </svg>
            </div>
            <div>
                <p style="font-size:11px;font-weight:600;color:rgba(255,255,255,.45);text-transform:uppercase;letter-spacing:.08em;margin:0 0 4px;">
                    Editando cuenta
                </p>
                <p style="font-size:22px;font-weight:900;color:#fff;margin:0;letter-spacing:-.3px;line-height:1.1;">
                    {{ $record?->codigo ?? '—' }}
                    <span style="font-size:16px;font-weight:600;color:rgba(255,255,255,.7);margin-left:8px;">
                        {{ $record?->nombre ?? '' }}
                    </span>
                </p>
            </div>
        </div>

        {{-- Right: KPIs --}}
        <div style="position:relative;z-index:1;display:flex;gap:12px;">
            <div style="background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.12);border-radius:12px;padding:12px 18px;text-align:center;">
                <div style="font-size:9px;font-weight:700;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.08em;margin-bottom:4px;">Naturaleza</div>
                <div style="font-size:15px;font-weight:800;color:#fff;">{{ ucfirst($record?->naturaleza ?? '—') }}</div>
            </div>
            <div style="background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.12);border-radius:12px;padding:12px 18px;text-align:center;">
                <div style="font-size:9px;font-weight:700;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.08em;margin-bottom:4px;">Acepta mov.</div>
                <div style="font-size:15px;font-weight:800;color:{{ $record?->acepta_movimiento ? '#86efac' : '#fca5a5' }};">
                    {{ $record?->acepta_movimiento ? 'Sí' : 'No' }}
                </div>
            </div>
            @if($record?->parent)
            <div style="background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.12);border-radius:12px;padding:12px 18px;text-align:center;">
                <div style="font-size:9px;font-weight:700;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.08em;margin-bottom:4px;">Cuenta padre</div>
                <div style="font-size:13px;font-weight:800;color:#fff;">{{ $record->parent->codigo }}</div>
            </div>
            @endif
        </div>
    </div>

    {{-- Delete action visible at top --}}
    <div style="display:flex;justify-content:flex-end;margin-bottom:8px;">
        <span style="font-size:11px;color:#94a3b8;font-weight:500;">
            El botón <strong>Eliminar cuenta</strong> aparece arriba a la derecha
        </span>
    </div>

</div>

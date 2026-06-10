<x-filament-panels::page>
<div style="font-family:'Plus Jakarta Sans',system-ui,sans-serif;">

{{-- ── Hero Banner ──────────────────────────────────────────────────── --}}
<div style="background:linear-gradient(135deg,#0F172A 0%,#1e3a8a 60%,#1e2d45 100%);
            border-radius:20px;padding:28px 32px;margin-bottom:24px;
            display:flex;align-items:center;justify-content:space-between;
            box-shadow:0 8px 32px rgba(15,23,42,.25);position:relative;overflow:hidden;">

    <div style="position:absolute;right:-40px;top:-40px;width:240px;height:240px;border-radius:50%;background:rgba(255,255,255,.03);"></div>
    <div style="position:absolute;right:80px;bottom:-60px;width:160px;height:160px;border-radius:50%;background:rgba(225,29,72,.06);"></div>

    <div style="position:relative;z-index:1;display:flex;align-items:center;gap:16px;">
        <div style="width:52px;height:52px;background:rgba(255,255,255,.1);border-radius:16px;
                    display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <svg width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
            </svg>
        </div>
        <div>
            <h1 style="font-size:22px;font-weight:900;color:#fff;margin:0;letter-spacing:-.3px;">
                Reportes Exportables
            </h1>
            <p style="font-size:12px;color:rgba(255,255,255,.5);margin:4px 0 0;">
                Selecciona el período y descarga en Excel o PDF
            </p>
        </div>
    </div>

    {{-- Indicador período activo --}}
    <div style="position:relative;z-index:1;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15);
                border-radius:14px;padding:14px 24px;text-align:center;">
        <div style="font-size:11px;color:rgba(255,255,255,.45);font-weight:600;text-transform:uppercase;letter-spacing:.08em;margin-bottom:4px;">Período activo</div>
        <div style="font-size:20px;font-weight:900;color:#fff;letter-spacing:-.5px;">
            {{ $this->getMeses()[$mes] ?? '' }} {{ $anio }}
        </div>
    </div>
</div>

{{-- ── Filtros ───────────────────────────────────────────────────────── --}}
<div style="background:#fff;border-radius:16px;border:1px solid #e5e7eb;
            box-shadow:0 2px 12px rgba(15,23,42,.06);padding:20px 24px;margin-bottom:24px;">

    <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;padding-bottom:14px;border-bottom:1px solid #f1f5f9;">
        <div style="width:32px;height:32px;background:#f1f5f9;border-radius:8px;display:flex;align-items:center;justify-content:center;">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#64748b" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z"/>
            </svg>
        </div>
        <span style="font-size:13px;font-weight:700;color:#0f172a;">Filtros del reporte</span>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr 2fr;gap:16px;align-items:end;">

        <div>
            <label style="display:block;font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.08em;margin-bottom:6px;">Mes</label>
            <select wire:model.live="mes"
                    style="width:100%;border-radius:10px;border:1.5px solid #e2e8f0;padding:10px 14px;
                           font-size:13px;font-weight:600;background:#f8fafc;color:#0f172a;
                           outline:none;cursor:pointer;">
                @foreach($this->getMeses() as $num => $nombre)
                    <option value="{{ $num }}">{{ $nombre }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label style="display:block;font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.08em;margin-bottom:6px;">Año</label>
            <select wire:model.live="anio"
                    style="width:100%;border-radius:10px;border:1.5px solid #e2e8f0;padding:10px 14px;
                           font-size:13px;font-weight:600;background:#f8fafc;color:#0f172a;
                           outline:none;cursor:pointer;">
                @foreach($this->getAnios() as $y)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label style="display:block;font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.08em;margin-bottom:6px;">Propietario <span style="font-weight:400;color:#cbd5e1;">(solo para liquidaciones)</span></label>
            <select wire:model.live="propietario_id"
                    style="width:100%;border-radius:10px;border:1.5px solid #e2e8f0;padding:10px 14px;
                           font-size:13px;font-weight:600;background:#f8fafc;color:#0f172a;
                           outline:none;cursor:pointer;">
                <option value="">— Todos los propietarios —</option>
                @foreach($this->getPropietarios() as $id => $nombre)
                    <option value="{{ $id }}">{{ $nombre }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>

{{-- ── Grid de reportes ─────────────────────────────────────────────── --}}
@php
$reportes = [
    [
        'titulo'  => 'Cartera General',
        'desc'    => 'Todas las facturas pendientes con saldo, estado y mora acumulada.',
        'color'   => '#1D4ED8',
        'colorBg' => '#eff6ff',
        'icono'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/>',
        'nota'    => 'Estado actual · sin importar período',
        'botones' => [['label'=>'Excel','r'=>'cartera','t'=>'excel','bg'=>'#16a34a','bg2'=>'#4ade80','icon'=>'M9 13.5l3 3m0 0l3-3m-3 3v-6m1.06-4.19l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.061-.44z']],
    ],
    [
        'titulo'  => 'Recaudo del Mes',
        'desc'    => 'Facturado vs recaudado con efectividad de cobro por período.',
        'color'   => '#059669',
        'colorBg' => '#f0fdf4',
        'icono'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/>',
        'nota'    => null,
        'botones' => [
            ['label'=>'Excel','r'=>'recaudo','t'=>'excel','bg'=>'#16a34a','bg2'=>'#4ade80','icon'=>'M9 13.5l3 3m0 0l3-3m-3 3v-6m1.06-4.19l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.061-.44z'],
            ['label'=>'PDF','r'=>'recaudo','t'=>'pdf','bg'=>'#E11D48','bg2'=>'#fb7185','icon'=>'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z'],
        ],
    ],
    [
        'titulo'  => 'Mora Detallada',
        'desc'    => 'Facturas en mora ordenadas por días, con valor mora y total a cobrar.',
        'color'   => '#DC2626',
        'colorBg' => '#fef2f2',
        'icono'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>',
        'nota'    => 'Estado actual · sin importar período',
        'botones' => [['label'=>'Excel','r'=>'mora','t'=>'excel','bg'=>'#16a34a','bg2'=>'#4ade80','icon'=>'M9 13.5l3 3m0 0l3-3m-3 3v-6m1.06-4.19l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.061-.44z']],
    ],
    [
        'titulo'  => 'Estado del Portafolio',
        'desc'    => 'Todos los inmuebles: ocupación, arrendatario, canon y vigencia.',
        'color'   => '#7C3AED',
        'colorBg' => '#f5f3ff',
        'icono'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z"/>',
        'nota'    => null,
        'botones' => [
            ['label'=>'Excel','r'=>'portafolio','t'=>'excel','bg'=>'#16a34a','bg2'=>'#4ade80','icon'=>'M9 13.5l3 3m0 0l3-3m-3 3v-6m1.06-4.19l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.061-.44z'],
            ['label'=>'PDF','r'=>'portafolio','t'=>'pdf','bg'=>'#E11D48','bg2'=>'#fb7185','icon'=>'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z'],
        ],
    ],
    [
        'titulo'  => 'Liquidaciones por Propietario',
        'desc'    => 'Giros del período: canon, comisión, IVA, retefuente y total a girar.',
        'color'   => '#D97706',
        'colorBg' => '#fffbeb',
        'span'    => 2,
        'icono'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 15.75V18m-7.5-6.75h.008v.008H8.25v-.008zm0 2.25h.008v.008H8.25V13.5zm0 2.25h.008v.008H8.25v-.008zm0 2.25h.008v.008H8.25V18zm2.498-6.75h.007v.008h-.007v-.008zm0 2.25h.007v.008h-.007V13.5zm0 2.25h.007v.008h-.007v-.008zm0 2.25h.007v.008h-.007V18zm2.504-6.75h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V13.5zm0 2.25h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V18zm2.498-6.75h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V13.5zM8.25 6h7.5v2.25h-7.5V6zM12 2.25c-1.892 0-3.758.11-5.593.322C5.307 2.7 4.5 3.65 4.5 4.757V19.5a2.25 2.25 0 002.25 2.25h10.5a2.25 2.25 0 002.25-2.25V4.757c0-1.108-.806-2.057-1.907-2.185A48.507 48.507 0 0012 2.25z"/>',
        'nota'    => null,
        'botones' => [
            ['label'=>'Excel','r'=>'liquidaciones','t'=>'excel','bg'=>'#16a34a','bg2'=>'#4ade80','icon'=>'M9 13.5l3 3m0 0l3-3m-3 3v-6m1.06-4.19l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.061-.44z'],
            ['label'=>'PDF','r'=>'liquidaciones','t'=>'pdf','bg'=>'#E11D48','bg2'=>'#fb7185','icon'=>'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z'],
        ],
    ],
];
@endphp

<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:24px;">

    @foreach($reportes as $rep)
    <div style="grid-column:span {{ $rep['span'] ?? 1 }};background:#fff;border-radius:16px;
                border:1px solid #e5e7eb;box-shadow:0 2px 12px rgba(15,23,42,.06);
                overflow:hidden;display:flex;flex-direction:column;">

        {{-- Card header con color --}}
        <div style="padding:18px 20px 14px;display:flex;align-items:flex-start;gap:14px;
                    border-bottom:1px solid #f1f5f9;">
            <div style="width:44px;height:44px;background:{{ $rep['colorBg'] }};border-radius:12px;
                        display:flex;align-items:center;justify-content:center;flex-shrink:0;
                        border:1px solid {{ $rep['color'] }}22;">
                <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="{{ $rep['color'] }}" stroke-width="1.6">
                    {!! $rep['icono'] !!}
                </svg>
            </div>
            <div style="flex:1;">
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;">
                    <div style="width:3px;height:18px;border-radius:99px;background:{{ $rep['color'] }};flex-shrink:0;"></div>
                    <p style="font-size:14px;font-weight:800;color:#0f172a;margin:0;line-height:1.2;">{{ $rep['titulo'] }}</p>
                </div>
                <p style="font-size:11px;color:#64748b;margin:0;line-height:1.6;padding-left:11px;">{{ $rep['desc'] }}</p>

                @if($rep['nota'] ?? false)
                <div style="margin-top:8px;padding-left:11px;">
                    <span style="font-size:9px;font-weight:700;color:{{ $rep['color'] }};background:{{ $rep['colorBg'] }};
                                 border:1px solid {{ $rep['color'] }}33;padding:2px 8px;border-radius:99px;
                                 text-transform:uppercase;letter-spacing:.06em;">
                        ⚡ {{ $rep['nota'] }}
                    </span>
                </div>
                @endif
            </div>
        </div>

        {{-- Botones --}}
        <div style="padding:14px 20px;display:flex;gap:10px;margin-top:auto;">
            @foreach($rep['botones'] as $btn)
            <a href="{{ $this->urlReporte($btn['r'], $btn['t']) }}" target="_blank"
               style="flex:1;display:inline-flex;align-items:center;justify-content:center;gap:7px;
                      border-radius:10px;padding:10px 14px;font-size:12px;font-weight:800;
                      color:#fff;text-decoration:none;letter-spacing:.02em;
                      background:linear-gradient(135deg,{{ $btn['bg'] }},{{ $btn['bg2'] }});
                      box-shadow:0 2px 8px {{ $btn['bg'] }}40;
                      transition:all .15s;">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2.2" style="flex-shrink:0;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $btn['icon'] }}"/>
                </svg>
                {{ $btn['label'] }}
            </a>
            @endforeach
        </div>

    </div>
    @endforeach

</div>

{{-- ── Nota informativa ─────────────────────────────────────────────── --}}
<div style="background:#f0f9ff;border:1px solid #bae6fd;border-radius:12px;
            padding:14px 18px;display:flex;align-items:flex-start;gap:12px;">
    <div style="width:32px;height:32px;background:#e0f2fe;border-radius:8px;
                display:flex;align-items:center;justify-content:center;flex-shrink:0;">
        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#0284c7" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/>
        </svg>
    </div>
    <div>
        <p style="font-size:12px;font-weight:700;color:#0369a1;margin:0 0 2px;">Nota importante</p>
        <p style="font-size:11px;color:#0284c7;margin:0;line-height:1.6;">
            Los reportes <strong>Cartera General</strong> y <strong>Mora Detallada</strong> muestran el estado actual del sistema
            sin importar el período seleccionado. Los demás reportes filtran por el mes y año indicado.
        </p>
    </div>
</div>

</div>
</x-filament-panels::page>

<x-filament-panels::page>
<div wire:poll.{{ $refreshInterval }}s style="font-family:'Plus Jakarta Sans',system-ui,sans-serif;">

@php
    $jobsData   = $this->getJobsData();
    $historial  = $this->getHistorialReciente();
    $totalJobs  = $jobsData->count();
    $completados = $jobsData->filter(fn($j) => $j['ultima_ejecucion']?->estado === 'completado')->count();
    $fallidos    = $jobsData->filter(fn($j) => $j['ultima_ejecucion']?->estado === 'fallido')->count();
    $sinRegistro = $jobsData->filter(fn($j) => !$j['ultima_ejecucion'])->count();
@endphp

{{-- ── Hero Banner ──────────────────────────────────────────────────── --}}
<div style="background:linear-gradient(135deg,#0F172A 0%,#1e3a8a 60%,#1e2d45 100%);
            border-radius:20px;padding:28px 32px;margin-bottom:24px;
            display:flex;align-items:center;justify-content:space-between;
            box-shadow:0 8px 32px rgba(15,23,42,.25);position:relative;overflow:hidden;">

    {{-- Decoración fondo --}}
    <div style="position:absolute;right:-40px;top:-40px;width:220px;height:220px;
                border-radius:50%;background:rgba(255,255,255,.03);"></div>
    <div style="position:absolute;right:60px;bottom:-60px;width:160px;height:160px;
                border-radius:50%;background:rgba(225,29,72,.06);"></div>

    <div style="position:relative;z-index:1;">
        <div style="display:flex;align-items:center;gap:14px;margin-bottom:8px;">
            <div style="width:48px;height:48px;background:rgba(255,255,255,.1);border-radius:14px;
                        display:flex;align-items:center;justify-content:center;backdrop-filter:blur(4px);">
                <svg width="26" height="26" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M8.25 3v1.5M4.5 8.25H3m18 0h-1.5M4.5 12H3m18 0h-1.5m-15 3.75H3m18 0h-1.5M8.25 19.5V21M12 3v1.5m0 15V21m3.75-18v1.5m0 15V21m-9-1.5h10.5a2.25 2.25 0 002.25-2.25V6.75a2.25 2.25 0 00-2.25-2.25H6.75A2.25 2.25 0 004.5 6.75v10.5a2.25 2.25 0 002.25 2.25zm.75-12h9v9h-9v-9z"/>
                </svg>
            </div>
            <div>
                <h1 style="font-size:22px;font-weight:900;color:#fff;margin:0;letter-spacing:-.3px;">
                    Monitor de Tareas Automáticas
                </h1>
                <p style="font-size:12px;color:rgba(255,255,255,.5);margin:3px 0 0;">
                    Supervisión en tiempo real · Actualiza cada {{ $refreshInterval }}s
                </p>
            </div>
        </div>
    </div>

    {{-- KPIs hero --}}
    <div style="display:flex;gap:12px;position:relative;z-index:1;">
        <div style="background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.12);
                    border-radius:14px;padding:14px 22px;text-align:center;min-width:80px;">
            <div style="font-size:28px;font-weight:900;color:#fff;line-height:1;">{{ $totalJobs }}</div>
            <div style="font-size:10px;color:rgba(255,255,255,.5);margin-top:3px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;">Total</div>
        </div>
        <div style="background:rgba(22,163,74,.15);border:1px solid rgba(22,163,74,.3);
                    border-radius:14px;padding:14px 22px;text-align:center;min-width:80px;">
            <div style="font-size:28px;font-weight:900;color:#4ade80;line-height:1;">{{ $completados }}</div>
            <div style="font-size:10px;color:rgba(74,222,128,.6);margin-top:3px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;">OK</div>
        </div>
        <div style="background:rgba(225,29,72,.15);border:1px solid rgba(225,29,72,.3);
                    border-radius:14px;padding:14px 22px;text-align:center;min-width:80px;">
            <div style="font-size:28px;font-weight:900;color:#fb7185;line-height:1;">{{ $fallidos }}</div>
            <div style="font-size:10px;color:rgba(251,113,133,.6);margin-top:3px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;">Error</div>
        </div>
        <div style="background:rgba(148,163,184,.1);border:1px solid rgba(148,163,184,.2);
                    border-radius:14px;padding:14px 22px;text-align:center;min-width:80px;">
            <div style="font-size:28px;font-weight:900;color:rgba(255,255,255,.4);line-height:1;">{{ $sinRegistro }}</div>
            <div style="font-size:10px;color:rgba(255,255,255,.3);margin-top:3px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;">Pendiente</div>
        </div>
        {{-- Indicador en vivo --}}
        <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;
                    padding:0 8px;gap:6px;">
            <div style="width:10px;height:10px;border-radius:50%;background:#4ade80;
                        animation:mjBlink 2s infinite;box-shadow:0 0 8px #4ade80;"></div>
            <span style="font-size:9px;color:rgba(255,255,255,.4);font-weight:700;text-transform:uppercase;letter-spacing:.06em;writing-mode:vertical-rl;">En vivo</span>
        </div>
    </div>
</div>

{{-- ── Tarjetas de jobs ──────────────────────────────────────────────── --}}
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:16px;margin-bottom:28px;">

    @foreach ($jobsData as $job)
    @php
        $ultima = $job['ultima_ejecucion'];

        [$borderColor, $badgeBg, $badgeColor, $badgeBorder, $estadoLabel, $dotColor] = match($ultima?->estado) {
            'completado' => ['#16a34a','#f0fdf4','#15803d','#bbf7d0','Completado','#22c55e'],
            'fallido'    => ['#E11D48','#fef2f2','#dc2626','#fecaca','Fallido','#f87171'],
            'ejecutando' => ['#d97706','#fffbeb','#d97706','#fde68a','Ejecutando…','#fbbf24'],
            default      => ['#cbd5e1','#f8fafc','#94a3b8','#e2e8f0','Sin registro','#cbd5e1'],
        };

        $iconSvg = match($job['icono']) {
            '📄' => '<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>',
            '⚠️' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>',
            '🔔' => '<path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>',
            '🔄' => '<path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/>',
            '📁' => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z"/>',
            default => '<path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>',
        };
    @endphp

    <div style="background:#fff;border-radius:16px;border:1px solid #e5e7eb;
                box-shadow:0 2px 12px rgba(15,23,42,.06);overflow:hidden;
                border-left:4px solid {{ $borderColor }};
                display:flex;flex-direction:column;">

        {{-- Header --}}
        <div style="padding:18px 20px 14px;display:flex;align-items:flex-start;justify-content:space-between;gap:10px;">
            <div style="display:flex;align-items:center;gap:12px;">
                <div style="width:42px;height:42px;background:{{ $badgeBg }};border-radius:12px;
                            display:flex;align-items:center;justify-content:center;flex-shrink:0;
                            border:1px solid {{ $badgeBorder }};">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="{{ $badgeColor }}" stroke-width="1.8">
                        {!! $iconSvg !!}
                    </svg>
                </div>
                <div>
                    <p style="font-size:13px;font-weight:800;color:#0f172a;line-height:1.3;margin:0;">
                        {{ $job['nombre'] }}
                    </p>
                    <p style="font-size:10px;color:#94a3b8;margin:3px 0 0;display:flex;align-items:center;gap:4px;">
                        <svg width="10" height="10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{ $job['frecuencia'] }}
                    </p>
                </div>
            </div>

            {{-- Badge estado --}}
            <div style="display:flex;flex-direction:column;align-items:flex-end;gap:4px;flex-shrink:0;">
                <span style="display:inline-flex;align-items:center;gap:5px;padding:4px 10px;
                             border-radius:20px;font-size:10px;font-weight:700;white-space:nowrap;
                             background:{{ $badgeBg }};color:{{ $badgeColor }};border:1px solid {{ $badgeBorder }};">
                    <span style="width:6px;height:6px;border-radius:50%;background:{{ $dotColor }};
                                 @if($ultima?->estado === 'ejecutando') animation:mjBlink 1s infinite; @endif"></span>
                    {{ $estadoLabel }}
                </span>
            </div>
        </div>

        {{-- Descripción --}}
        <div style="padding:0 20px 14px;">
            <p style="font-size:11px;color:#64748b;line-height:1.6;margin:0;">{{ $job['descripcion'] }}</p>
        </div>

        @if($ultima)
        {{-- Métricas --}}
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;
                    border-top:1px solid #f1f5f9;border-bottom:1px solid #f1f5f9;
                    background:#fafafa;">
            <div style="padding:12px;text-align:center;">
                <p style="font-size:9px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.08em;margin:0 0 4px;">Última vez</p>
                <p style="font-size:11px;font-weight:700;color:#334155;margin:0;line-height:1.4;">
                    {{ $ultima->started_at->format('d/m/Y') }}<br>
                    <span style="color:#94a3b8;font-weight:500;font-size:10px;">{{ $ultima->started_at->format('H:i') }}</span>
                </p>
            </div>
            <div style="padding:12px;text-align:center;border-left:1px solid #f1f5f9;border-right:1px solid #f1f5f9;">
                <p style="font-size:9px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.08em;margin:0 0 2px;">Registros</p>
                <p style="font-size:28px;font-weight:900;color:#0f172a;margin:0;line-height:1.1;">
                    {{ $ultima->registros_procesados }}
                </p>
            </div>
            <div style="padding:12px;text-align:center;">
                <p style="font-size:9px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.08em;margin:0 0 4px;">Duración</p>
                <p style="font-size:16px;font-weight:800;color:#334155;margin:0;">
                    {{ $ultima->duracionLabel() }}
                </p>
            </div>
        </div>

        {{-- Detalles --}}
        @if($ultima->detalles)
        <div style="padding:10px 20px;background:#f8fafc;border-bottom:1px solid #f1f5f9;">
            @foreach($ultima->detalles as $key => $val)
            <div style="display:flex;justify-content:space-between;align-items:center;padding:2px 0;
                        @if(!$loop->last)border-bottom:1px solid #f0f4f8;@endif">
                <span style="font-size:10px;color:#94a3b8;font-weight:500;">{{ str_replace('_',' ',$key) }}</span>
                <span style="font-size:11px;font-weight:700;color:#334155;">{{ $val }}</span>
            </div>
            @endforeach
        </div>
        @endif

        {{-- Error --}}
        @if($ultima->estado === 'fallido' && $ultima->errores)
        <div style="margin:0 16px 12px;background:#fef2f2;border:1px solid #fecaca;border-radius:8px;
                    padding:10px 12px;font-size:10px;color:#dc2626;font-family:monospace;
                    line-height:1.5;word-break:break-word;">
            {{ Str::limit($ultima->errores, 180) }}
        </div>
        @endif

        {{-- Footer --}}
        <div style="padding:10px 20px;display:flex;justify-content:space-between;align-items:center;
                    font-size:10px;color:#94a3b8;margin-top:auto;">
            <span>Por: <strong style="color:#64748b;">{{ $ultima->disparado_por }}</strong></span>
            <span style="background:#f1f5f9;padding:2px 8px;border-radius:99px;">{{ $ultima->started_at->diffForHumans() }}</span>
        </div>

        @else
        {{-- Sin ejecución --}}
        <div style="flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;
                    padding:24px 16px;gap:8px;border-top:1px solid #f1f5f9;">
            <div style="width:36px;height:36px;background:#f8fafc;border-radius:10px;border:1px dashed #e2e8f0;
                        display:flex;align-items:center;justify-content:center;">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#cbd5e1" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p style="font-size:11px;color:#cbd5e1;margin:0;font-weight:600;">Aún no ejecutado</p>
        </div>
        @endif

    </div>
    @endforeach

</div>

{{-- ── Historial de ejecuciones ─────────────────────────────────────── --}}
<div style="background:#fff;border-radius:16px;border:1px solid #e5e7eb;
            box-shadow:0 2px 12px rgba(15,23,42,.06);overflow:hidden;">

    {{-- Header tabla --}}
    <div style="background:linear-gradient(135deg,#0F172A,#1e2d45);padding:18px 24px;
                display:flex;align-items:center;justify-content:space-between;">
        <div>
            <h3 style="font-size:15px;font-weight:800;color:#fff;margin:0;letter-spacing:-.2px;">
                Historial de Ejecuciones
            </h3>
            <p style="font-size:11px;color:rgba(255,255,255,.45);margin:3px 0 0;">
                Últimas 50 ejecuciones registradas
            </p>
        </div>
        <div style="display:flex;align-items:center;gap:8px;background:rgba(255,255,255,.07);
                    border:1px solid rgba(255,255,255,.12);border-radius:99px;padding:6px 14px;">
            <span style="width:8px;height:8px;border-radius:50%;background:#4ade80;
                         display:inline-block;animation:mjBlink 2s infinite;
                         box-shadow:0 0 6px #4ade80;"></span>
            <span style="font-size:11px;color:rgba(255,255,255,.6);font-weight:600;">En vivo</span>
        </div>
    </div>

    <style>
        @keyframes mjBlink { 0%,100%{opacity:1} 50%{opacity:.2} }
        .mj-table th { padding:10px 18px;text-align:left;font-size:10px;font-weight:700;
                       color:#94a3b8;text-transform:uppercase;letter-spacing:.08em;
                       background:#f8fafc;border-bottom:1px solid #e5e7eb; }
        .mj-table td { padding:12px 18px;font-size:12px;color:#374151;
                       border-bottom:1px solid #f3f4f6;vertical-align:middle; }
        .mj-table tr:last-child td { border-bottom:none; }
        .mj-table tbody tr:hover td { background:#f8fafc; }
    </style>

    <div style="overflow-x:auto;">
        <table class="mj-table" style="width:100%;border-collapse:collapse;">
            <thead>
                <tr>
                    <th>Job</th>
                    <th>Estado</th>
                    <th>Inicio</th>
                    <th style="text-align:center;">Registros</th>
                    <th>Duración</th>
                    <th>Disparado</th>
                    <th>Detalle</th>
                </tr>
            </thead>
            <tbody>
                @forelse($historial as $ej)
                @php
                    [$bg, $color, $border, $label, $dot] = match($ej->estado) {
                        'completado' => ['#f0fdf4','#15803d','#bbf7d0','Completado','#22c55e'],
                        'fallido'    => ['#fef2f2','#dc2626','#fecaca','Fallido','#f87171'],
                        'ejecutando' => ['#fffbeb','#d97706','#fde68a','Ejecutando','#fbbf24'],
                        default      => ['#f8fafc','#94a3b8','#e2e8f0',$ej->estado,'#cbd5e1'],
                    };
                @endphp
                <tr>
                    <td>
                        <span style="font-weight:700;color:#0f172a;font-size:12px;">{{ $ej->job_name }}</span>
                    </td>
                    <td>
                        <span style="display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:20px;
                                     font-size:10px;font-weight:700;
                                     background:{{ $bg }};color:{{ $color }};border:1px solid {{ $border }};">
                            <span style="width:5px;height:5px;border-radius:50%;background:{{ $dot }};"></span>
                            {{ $label }}
                        </span>
                    </td>
                    <td style="white-space:nowrap;">
                        <span style="font-weight:600;color:#334155;">{{ $ej->started_at->format('d/m/Y H:i:s') }}</span><br>
                        <span style="color:#94a3b8;font-size:10px;">{{ $ej->started_at->diffForHumans() }}</span>
                    </td>
                    <td style="text-align:center;">
                        <span style="font-size:18px;font-weight:900;color:#0f172a;">{{ $ej->registros_procesados }}</span>
                    </td>
                    <td>
                        <span style="font-weight:700;color:#334155;">{{ $ej->duracionLabel() }}</span>
                    </td>
                    <td>
                        <span style="background:#f1f5f9;color:#64748b;padding:3px 9px;
                                     border-radius:6px;font-size:10px;font-weight:600;">
                            {{ $ej->disparado_por }}
                        </span>
                    </td>
                    <td style="max-width:260px;">
                        @if($ej->estado === 'fallido' && $ej->errores)
                            <span style="color:#dc2626;font-family:monospace;font-size:10px;line-height:1.5;">
                                {{ Str::limit($ej->errores, 100) }}
                            </span>
                        @elseif($ej->detalles)
                            <span style="font-size:10px;color:#64748b;">
                                @foreach($ej->detalles as $k => $v)
                                    <span style="color:#94a3b8;">{{ str_replace('_',' ',$k) }}:</span>
                                    <strong style="color:#334155;">{{ $v }}</strong>
                                    @if(!$loop->last) &nbsp;·&nbsp; @endif
                                @endforeach
                            </span>
                        @else
                            <span style="color:#e2e8f0;">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align:center;padding:48px;color:#94a3b8;font-size:13px;">
                        <div style="display:flex;flex-direction:column;align-items:center;gap:10px;">
                            <svg width="32" height="32" fill="none" viewBox="0 0 24 24" stroke="#e2e8f0" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Sin ejecuciones registradas aún.
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Footer historial --}}
    <div style="padding:12px 24px;background:#f8fafc;border-top:1px solid #e5e7eb;
                display:flex;justify-content:space-between;align-items:center;">
        <span style="font-size:10px;color:#94a3b8;font-weight:600;">
            {{ $historial->count() }} registro(s) mostrado(s)
        </span>
        <span style="font-size:10px;color:#94a3b8;">
            Auto-actualiza cada {{ $refreshInterval }}s
        </span>
    </div>

</div>

</div>
</x-filament-panels::page>

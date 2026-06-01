<x-filament-panels::page>
<div wire:poll.{{ $refreshInterval }}s style="font-family: system-ui, sans-serif;">

    {{-- ── Tarjetas de jobs ──────────────────────────────────────────── --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 16px; margin-bottom: 28px;">

        @foreach ($this->getJobsData() as $job)
        @php
            /** @var \App\Models\JobExecution|null $ultima */
            $ultima = $job['ultima_ejecucion'];

            [$badgeBg, $badgeColor, $badgeBorder, $estadoLabel] = match($ultima?->estado) {
                'completado' => ['#f0fdf4', '#15803d', '#bbf7d0', '✅ Completado'],
                'fallido'    => ['#fef2f2', '#dc2626', '#fecaca', '❌ Fallido'],
                'ejecutando' => ['#fffbeb', '#d97706', '#fde68a', '⏳ Ejecutando...'],
                default      => ['#f8fafc', '#94a3b8', '#e2e8f0', '— Sin registro'],
            };
        @endphp

        <div style="background: #fff; border: 1px solid #e5e7eb; border-radius: 16px;
                    box-shadow: 0 1px 6px rgba(0,0,0,0.06); padding: 20px 22px;
                    display: flex; flex-direction: column; gap: 14px;">

            {{-- Header --}}
            <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 12px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 28px; line-height: 1;">{{ $job['icono'] }}</span>
                    <div>
                        <p style="font-size: 14px; font-weight: 700; color: #111827; line-height: 1.2; margin: 0;">
                            {{ $job['nombre'] }}
                        </p>
                        <p style="font-size: 11px; color: #9ca3af; margin: 3px 0 0;">
                            🕐 {{ $job['frecuencia'] }}
                        </p>
                    </div>
                </div>
                <span style="display: inline-flex; align-items: center; padding: 3px 10px;
                             border-radius: 20px; font-size: 11px; font-weight: 600; white-space: nowrap;
                             background: {{ $badgeBg }}; color: {{ $badgeColor }}; border: 1px solid {{ $badgeBorder }};">
                    {{ $estadoLabel }}
                </span>
            </div>

            {{-- Descripción --}}
            <p style="font-size: 12px; color: #6b7280; line-height: 1.5; margin: 0;">
                {{ $job['descripcion'] }}
            </p>

            @if($ultima)

                {{-- Métricas --}}
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr;
                            border-top: 1px solid #f3f4f6; padding-top: 14px; gap: 8px;">

                    <div style="text-align: center;">
                        <p style="font-size: 10px; color: #9ca3af; text-transform: uppercase;
                                  letter-spacing: 0.4px; margin: 0 0 4px;">Última vez</p>
                        <p style="font-size: 12px; font-weight: 600; color: #374151; margin: 0; line-height: 1.4;">
                            {{ $ultima->started_at->format('d/m/Y') }}<br>
                            <span style="color: #9ca3af; font-weight: 400;">{{ $ultima->started_at->format('H:i') }}</span>
                        </p>
                    </div>

                    <div style="text-align: center; border-left: 1px solid #f3f4f6; border-right: 1px solid #f3f4f6;">
                        <p style="font-size: 10px; color: #9ca3af; text-transform: uppercase;
                                  letter-spacing: 0.4px; margin: 0 0 2px;">Registros</p>
                        <p style="font-size: 26px; font-weight: 800; color: #111827; margin: 0; line-height: 1.1;">
                            {{ $ultima->registros_procesados }}
                        </p>
                    </div>

                    <div style="text-align: center;">
                        <p style="font-size: 10px; color: #9ca3af; text-transform: uppercase;
                                  letter-spacing: 0.4px; margin: 0 0 4px;">Duración</p>
                        <p style="font-size: 15px; font-weight: 700; color: #374151; margin: 0;">
                            {{ $ultima->duracionLabel() }}
                        </p>
                    </div>
                </div>

                {{-- Detalles --}}
                @if($ultima->detalles)
                <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 10px;
                            padding: 10px 14px; font-size: 11px; font-family: 'SF Mono', 'Fira Code', monospace;">
                    @foreach($ultima->detalles as $key => $val)
                    <div style="display: flex; justify-content: space-between; padding: 2px 0;
                                @if(!$loop->last) border-bottom: 1px solid #f0f0f0; @endif">
                        <span style="color: #9ca3af;">{{ str_replace('_', ' ', $key) }}</span>
                        <span style="font-weight: 700; color: #374151;">{{ $val }}</span>
                    </div>
                    @endforeach
                </div>
                @endif

                {{-- Error --}}
                @if($ultima->estado === 'fallido' && $ultima->errores)
                <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px;
                            padding: 10px 14px; font-size: 11px; color: #dc2626; font-family: monospace;
                            line-height: 1.5; word-break: break-word;">
                    <strong>Error:</strong> {{ Str::limit($ultima->errores, 220) }}
                </div>
                @endif

                {{-- Footer --}}
                <div style="display: flex; justify-content: space-between; align-items: center;
                            font-size: 11px; color: #9ca3af; border-top: 1px solid #f3f4f6; padding-top: 10px;">
                    <span>Disparado por: <strong style="color: #6b7280;">{{ $ultima->disparado_por }}</strong></span>
                    <span>{{ $ultima->started_at->diffForHumans() }}</span>
                </div>

            @else
                <div style="text-align: center; padding: 20px 0; font-size: 12px; color: #d1d5db;">
                    Este job aún no ha sido ejecutado
                </div>
            @endif

        </div>
        @endforeach

    </div>

    {{-- ── Historial de ejecuciones ──────────────────────────────────── --}}
    <div style="background: #fff; border: 1px solid #e5e7eb; border-radius: 16px;
                box-shadow: 0 1px 6px rgba(0,0,0,0.06); overflow: hidden;">

        {{-- Header tabla --}}
        <div style="padding: 16px 22px; border-bottom: 1px solid #e5e7eb;
                    display: flex; align-items: center; justify-content: space-between;">
            <div>
                <h3 style="font-size: 15px; font-weight: 700; color: #111827; margin: 0;">
                    Historial de Ejecuciones
                </h3>
                <p style="font-size: 11px; color: #9ca3af; margin: 3px 0 0;">
                    Últimas 50 ejecuciones · Actualiza cada {{ $refreshInterval }}s
                </p>
            </div>
            <div style="display: flex; align-items: center; gap: 6px;">
                <span style="width: 8px; height: 8px; border-radius: 50%; background: #22c55e;
                             display: inline-block; animation: blink 2s infinite;"></span>
                <span style="font-size: 11px; color: #9ca3af;">En vivo</span>
            </div>
        </div>

        <style>
            @keyframes blink { 0%,100%{opacity:1} 50%{opacity:.2} }
            .je-table th, .je-table td { padding: 11px 18px; text-align: left; font-size: 12px; }
            .je-table th { background: #f9fafb; font-weight: 600; color: #6b7280;
                           text-transform: uppercase; letter-spacing: 0.4px; border-bottom: 1px solid #e5e7eb; }
            .je-table td { border-bottom: 1px solid #f3f4f6; color: #374151; vertical-align: top; }
            .je-table tr:last-child td { border-bottom: none; }
            .je-table tr:hover td { background: #f9fafb; }
        </style>

        <div style="overflow-x: auto;">
            <table class="je-table" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th>Job</th>
                        <th>Estado</th>
                        <th>Inicio</th>
                        <th>Duración</th>
                        <th style="text-align: center;">Registros</th>
                        <th>Disparado</th>
                        <th>Detalle</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($this->getHistorialReciente() as $ej)
                    @php
                        [$bg, $color, $border, $label] = match($ej->estado) {
                            'completado' => ['#f0fdf4', '#15803d', '#bbf7d0', '✅ Completado'],
                            'fallido'    => ['#fef2f2', '#dc2626', '#fecaca', '❌ Fallido'],
                            'ejecutando' => ['#fffbeb', '#d97706', '#fde68a', '⏳ Ejecutando'],
                            default      => ['#f8fafc', '#94a3b8', '#e2e8f0', $ej->estado],
                        };
                    @endphp
                    <tr>
                        <td>
                            <span style="font-weight: 600; color: #111827;">{{ $ej->job_name }}</span>
                        </td>
                        <td>
                            <span style="display: inline-block; padding: 2px 9px; border-radius: 20px;
                                         font-size: 11px; font-weight: 600;
                                         background: {{ $bg }}; color: {{ $color }}; border: 1px solid {{ $border }};">
                                {{ $label }}
                            </span>
                        </td>
                        <td style="white-space: nowrap;">
                            <span style="font-weight: 500;">{{ $ej->started_at->format('d/m/Y H:i:s') }}</span><br>
                            <span style="color: #9ca3af; font-size: 11px;">{{ $ej->started_at->diffForHumans() }}</span>
                        </td>
                        <td style="font-weight: 600;">{{ $ej->duracionLabel() }}</td>
                        <td style="text-align: center;">
                            <span style="font-size: 16px; font-weight: 800; color: #111827;">
                                {{ $ej->registros_procesados }}
                            </span>
                        </td>
                        <td>
                            <span style="background: #f3f4f6; color: #6b7280; padding: 2px 8px;
                                         border-radius: 6px; font-size: 11px; font-weight: 500;">
                                {{ $ej->disparado_por }}
                            </span>
                        </td>
                        <td style="max-width: 280px; line-height: 1.5;">
                            @if($ej->estado === 'fallido' && $ej->errores)
                                <span style="color: #dc2626; font-family: monospace; font-size: 11px;">
                                    {{ Str::limit($ej->errores, 120) }}
                                </span>
                            @elseif($ej->detalles)
                                <span style="color: #6b7280; font-size: 11px;">
                                    @foreach($ej->detalles as $k => $v)
                                        <span style="color: #9ca3af;">{{ str_replace('_', ' ', $k) }}:</span>
                                        <strong style="color: #374151;">{{ $v }}</strong>
                                        @if(!$loop->last) &nbsp;·&nbsp; @endif
                                    @endforeach
                                </span>
                            @else
                                <span style="color: #d1d5db;">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 40px; color: #9ca3af; font-size: 13px;">
                            Sin ejecuciones registradas aún. Los jobs aparecerán aquí cuando se ejecuten.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

</div>
</x-filament-panels::page>

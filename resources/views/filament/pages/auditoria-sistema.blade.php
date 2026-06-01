<x-filament-panels::page>
<div style="font-family: system-ui, sans-serif;">

    {{-- ── Filtros ──────────────────────────────────────────────────────── --}}
    <div style="background:#fff; border:1px solid #e5e7eb; border-radius:16px;
                box-shadow:0 1px 6px rgba(0,0,0,.06); padding:20px 24px; margin-bottom:20px;">

        <div style="font-size:12px; font-weight:700; color:#6b7280; text-transform:uppercase;
                    letter-spacing:.5px; margin-bottom:14px;">
            Filtros
        </div>

        <div style="display:grid; grid-template-columns:2fr 1fr 1fr 1fr 1fr; gap:12px; align-items:end;">

            {{-- Búsqueda --}}
            <div>
                <label style="font-size:11px; font-weight:600; color:#6b7280; display:block; margin-bottom:5px;">
                    Buscar en eventos
                </label>
                <input wire:model.live.debounce.400ms="buscar"
                       type="text" placeholder="Acción, campo, valor..."
                       style="width:100%; padding:8px 12px; border:1px solid #d1d5db; border-radius:8px;
                              font-size:13px; color:#111827; background:#f9fafb; outline:none;
                              box-sizing:border-box;">
            </div>

            {{-- Usuario --}}
            <div>
                <label style="font-size:11px; font-weight:600; color:#6b7280; display:block; margin-bottom:5px;">
                    Usuario
                </label>
                <select wire:model.live="usuario_id"
                        style="width:100%; padding:8px 12px; border:1px solid #d1d5db; border-radius:8px;
                               font-size:13px; color:#111827; background:#f9fafb; outline:none;
                               box-sizing:border-box;">
                    <option value="">Todos</option>
                    @foreach($this->getUsuarios() as $u)
                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Fecha desde --}}
            <div>
                <label style="font-size:11px; font-weight:600; color:#6b7280; display:block; margin-bottom:5px;">
                    Desde
                </label>
                <input wire:model.live="fecha_desde" type="date"
                       style="width:100%; padding:8px 12px; border:1px solid #d1d5db; border-radius:8px;
                              font-size:13px; color:#111827; background:#f9fafb; outline:none;
                              box-sizing:border-box;">
            </div>

            {{-- Fecha hasta --}}
            <div>
                <label style="font-size:11px; font-weight:600; color:#6b7280; display:block; margin-bottom:5px;">
                    Hasta
                </label>
                <input wire:model.live="fecha_hasta" type="date"
                       style="width:100%; padding:8px 12px; border:1px solid #d1d5db; border-radius:8px;
                              font-size:13px; color:#111827; background:#f9fafb; outline:none;
                              box-sizing:border-box;">
            </div>

            {{-- Limpiar --}}
            <div>
                <button wire:click="$set('buscar',''); $set('usuario_id',''); $set('fecha_desde',''); $set('fecha_hasta','')"
                        style="width:100%; padding:8px 12px; border:1px solid #e5e7eb; border-radius:8px;
                               font-size:12px; font-weight:600; color:#6b7280; background:#f9fafb;
                               cursor:pointer; white-space:nowrap;">
                    Limpiar filtros
                </button>
            </div>

        </div>
    </div>

    {{-- ── Tabla de registros ───────────────────────────────────────────── --}}
    @php $registros = $this->getRegistros(); @endphp

    <div style="background:#fff; border:1px solid #e5e7eb; border-radius:16px;
                box-shadow:0 1px 6px rgba(0,0,0,.06); overflow:hidden;">

        <div style="padding:16px 24px; border-bottom:1px solid #e5e7eb;
                    display:flex; align-items:center; justify-content:space-between;">
            <div>
                <h3 style="font-size:15px; font-weight:700; color:#111827; margin:0;">
                    Registro de Actividad
                </h3>
                <p style="font-size:11px; color:#9ca3af; margin:3px 0 0;">
                    {{ number_format($registros->total()) }} eventos registrados
                </p>
            </div>
            <span style="background:#f0fdf4; color:#15803d; border:1px solid #bbf7d0;
                         padding:3px 12px; border-radius:20px; font-size:11px; font-weight:600;">
                🔒 Solo lectura · Inalterable
            </span>
        </div>

        <style>
            .aud-table th, .aud-table td { padding:11px 16px; text-align:left; font-size:12px; }
            .aud-table th { background:#f9fafb; font-weight:600; color:#6b7280; text-transform:uppercase;
                            letter-spacing:.4px; border-bottom:1px solid #e5e7eb; white-space:nowrap; }
            .aud-table td { border-bottom:1px solid #f3f4f6; color:#374151; vertical-align:top; }
            .aud-table tr:last-child td { border-bottom:none; }
            .aud-table tr:hover td { background:#fafafa; }
            .aud-detail { font-family:'SF Mono','Fira Code',monospace; font-size:10.5px; }
            .aud-pill { display:inline-block; padding:2px 8px; border-radius:20px; font-size:10px; font-weight:600; }
        </style>

        <div style="overflow-x:auto;">
            <table class="aud-table" style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr>
                        <th style="width:140px;">Fecha / Hora</th>
                        <th style="width:160px;">Usuario</th>
                        <th style="width:130px;">Módulo</th>
                        <th>Acción</th>
                        <th>Registro</th>
                        <th>Cambios</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($registros as $log)
                    @php
                        $accionColor = match(true) {
                            str_contains($log->description, 'creado') || str_contains($log->description, 'creada') => ['#f0fdf4','#15803d','#bbf7d0'],
                            str_contains($log->description, 'actualizado') || str_contains($log->description, 'actualizada') => ['#eff6ff','#1d4ed8','#bfdbfe'],
                            str_contains($log->description, 'eliminado') || str_contains($log->description, 'eliminada') => ['#fef2f2','#dc2626','#fecaca'],
                            default => ['#f8fafc','#64748b','#e2e8f0'],
                        };
                        $old  = $log->properties['old'] ?? [];
                        $new  = $log->properties['attributes'] ?? [];
                        $modulo = \App\Filament\Pages\AuditoriaSistema::moduloLabel($log->subject_type ?? '');
                    @endphp
                    <tr>
                        {{-- Fecha --}}
                        <td style="white-space:nowrap;">
                            <span style="font-weight:600; color:#111827;">
                                {{ $log->created_at->format('d/m/Y') }}
                            </span><br>
                            <span style="color:#9ca3af;">{{ $log->created_at->format('H:i:s') }}</span>
                        </td>

                        {{-- Usuario --}}
                        <td>
                            @if($log->causer)
                                <span style="font-weight:600; color:#374151;">{{ $log->causer->name }}</span><br>
                                <span style="font-size:10px; color:#9ca3af;">{{ $log->causer->email }}</span>
                            @else
                                <span style="color:#9ca3af; font-style:italic;">Sistema / Job</span>
                            @endif
                        </td>

                        {{-- Módulo --}}
                        <td>
                            <span style="background:#f3f4f6; color:#374151; padding:3px 10px;
                                         border-radius:8px; font-size:11px; font-weight:600;">
                                {{ $modulo }}
                            </span>
                        </td>

                        {{-- Acción --}}
                        <td>
                            <span class="aud-pill"
                                  style="background:{{ $accionColor[0] }}; color:{{ $accionColor[1] }};
                                         border:1px solid {{ $accionColor[2] }};">
                                {{ $log->description }}
                            </span>
                        </td>

                        {{-- Registro afectado --}}
                        <td>
                            @if($log->subject)
                                @php
                                    $label = $log->subject->numero_contrato
                                          ?? $log->subject->numero
                                          ?? $log->subject->codigo
                                          ?? $log->subject->nombre_completo
                                          ?? $log->subject->name
                                          ?? "ID #{$log->subject_id}";
                                @endphp
                                <span style="font-weight:600; color:#374151;">{{ $label }}</span>
                            @else
                                <span style="color:#9ca3af;">ID #{{ $log->subject_id }}</span>
                            @endif
                        </td>

                        {{-- Cambios --}}
                        <td style="max-width:320px;">
                            @if(!empty($old) || !empty($new))
                                <div class="aud-detail" style="display:flex; flex-direction:column; gap:3px;">
                                    @foreach($new as $campo => $valNuevo)
                                        @php $valAnterior = $old[$campo] ?? null; @endphp
                                        <div style="display:flex; gap:6px; align-items:baseline; flex-wrap:wrap;">
                                            <span style="color:#9ca3af; min-width:80px; flex-shrink:0;">
                                                {{ str_replace('_', ' ', $campo) }}:
                                            </span>
                                            @if($valAnterior !== null && $valAnterior !== $valNuevo)
                                                <span style="color:#dc2626; text-decoration:line-through; opacity:.7;">
                                                    {{ Str::limit((string)$valAnterior, 30) }}
                                                </span>
                                                <span style="color:#9ca3af;">→</span>
                                                <span style="color:#15803d; font-weight:700;">
                                                    {{ Str::limit((string)$valNuevo, 30) }}
                                                </span>
                                            @else
                                                <span style="color:#374151; font-weight:600;">
                                                    {{ Str::limit((string)$valNuevo, 40) }}
                                                </span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <span style="color:#d1d5db; font-style:italic; font-size:11px;">sin detalle</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align:center; padding:50px; color:#9ca3af; font-size:13px;">
                            Sin registros de actividad con los filtros seleccionados.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginación --}}
        @if($registros->hasPages())
        <div style="padding:14px 20px; border-top:1px solid #f3f4f6;">
            {{ $registros->links() }}
        </div>
        @endif

    </div>

</div>
</x-filament-panels::page>

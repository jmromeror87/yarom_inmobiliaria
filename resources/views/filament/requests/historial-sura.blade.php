<div style="font-family:'Plus Jakarta Sans',sans-serif;">

    @if($estudios->isEmpty())
    <div style="text-align:center;padding:48px 20px;">
        <div style="width:56px;height:56px;background:#f1f5f9;border-radius:16px;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;">
            <svg width="26" height="26" fill="none" viewBox="0 0 24 24" stroke="#94a3b8" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
        </div>
        <div style="font-weight:800;color:#0f172a;font-size:14px;margin-bottom:4px;">Sin envíos registrados</div>
        <div style="font-size:12px;color:#94a3b8;">Use los botones de WhatsApp o correo para enviar a Sura.</div>
    </div>
    @else

    {{-- Timeline --}}
    <div style="display:flex;flex-direction:column;gap:14px;">
    @foreach($estudios as $i => $estudio)
    @php
        $canal = $estudio->canal_envio;
        $resultado = $estudio->resultado_sura ?? 'pendiente';
        $resColor = match($resultado) {
            'aprobada'    => ['c'=>'#16a34a','bg'=>'#dcfce7','bc'=>'#bbf7d0','lbl'=>'✅ Aprobada'],
            'rechazada'   => ['c'=>'#E11D48','bg'=>'#ffe4e6','bc'=>'#fecaca','lbl'=>'❌ Rechazada'],
            'condicional' => ['c'=>'#d97706','bg'=>'#fef3c7','bc'=>'#fcd34d','lbl'=>'⚠️ Condicional'],
            default       => ['c'=>'#64748b','bg'=>'#f1f5f9','bc'=>'#e2e8f0','lbl'=>'⏳ Pendiente'],
        };
        $canalIcon = match($canal) {
            'whatsapp' => '📱',
            'email'    => '📧',
            default    => '🤝',
        };
    @endphp
    <div style="background:#fff;border-radius:14px;border:1px solid #e2e8f0;overflow:hidden;box-shadow:0 2px 8px rgba(15,23,42,.05);">

        {{-- Header card --}}
        <div style="background:linear-gradient(135deg,#0F172A,#1e2d45);padding:14px 18px;display:flex;align-items:center;justify-content:space-between;">
            <div style="display:flex;align-items:center;gap:12px;">
                <div style="width:38px;height:38px;background:rgba(255,255,255,.1);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;">
                    {{ $canalIcon }}
                </div>
                <div>
                    <div style="font-weight:800;font-size:13px;color:#fff;">
                        {{ $canal === 'whatsapp' ? 'Whatsapp' : ($canal === 'email' ? 'Correo electrónico' : 'Presencial') }}
                        @if($estudio->contacto_sura) — <span style="color:rgba(255,255,255,.7);">{{ $estudio->contacto_sura }}</span> @endif
                    </div>
                    <div style="font-size:11px;color:rgba(255,255,255,.45);margin-top:2px;">
                        Enviado {{ $estudio->fecha_envio?->format('d/m/Y H:i') ?? 'N/A' }}
                        · por {{ $estudio->enviadoPor?->name ?? 'Sistema' }}
                    </div>
                </div>
            </div>
            <span style="background:{{ $resColor['bg'] }};color:{{ $resColor['c'] }};border:1.5px solid {{ $resColor['bc'] }};font-size:11px;font-weight:800;padding:4px 12px;border-radius:99px;white-space:nowrap;">
                {{ $resColor['lbl'] }}
            </span>
        </div>

        <div style="padding:16px 18px;">

            {{-- Mensaje enviado --}}
            @if($estudio->mensaje_enviado)
            <div style="margin-bottom:14px;">
                <div style="font-size:9px;font-weight:800;text-transform:uppercase;letter-spacing:.1em;color:#94a3b8;margin-bottom:6px;">Mensaje enviado</div>
                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:12px 14px;font-size:12px;color:#334155;line-height:1.7;white-space:pre-wrap;max-height:160px;overflow-y:auto;font-family:monospace;">{{ $estudio->mensaje_enviado }}</div>
            </div>
            @endif

            {{-- Respuesta Sura --}}
            @if($estudio->numero_solicitud_sura)
            <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:14px;margin-bottom:10px;">
                <div style="font-size:9px;font-weight:800;text-transform:uppercase;letter-spacing:.1em;color:#16a34a;margin-bottom:10px;">Respuesta de Suramericana</div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                    <div>
                        <div style="font-size:10px;color:#94a3b8;font-weight:600;">N° Solicitud Sura</div>
                        <div style="font-size:13px;font-weight:800;color:#0f172a;">{{ $estudio->numero_solicitud_sura }}</div>
                    </div>
                    <div>
                        <div style="font-size:10px;color:#94a3b8;font-weight:600;">Analista</div>
                        <div style="font-size:13px;font-weight:700;color:#0f172a;">{{ $estudio->analista_sura ?? 'N/A' }}</div>
                    </div>
                    <div>
                        <div style="font-size:10px;color:#94a3b8;font-weight:600;">Fecha respuesta</div>
                        <div style="font-size:13px;font-weight:700;color:#0f172a;">{{ $estudio->fecha_respuesta?->format('d/m/Y H:i') ?? 'N/A' }}</div>
                    </div>
                </div>
                @if($estudio->observaciones_sura)
                <div style="margin-top:10px;font-size:12px;color:#374151;border-top:1px solid #bbf7d0;padding-top:10px;">{{ $estudio->observaciones_sura }}</div>
                @endif
                @if($estudio->path_respuesta)
                <div style="margin-top:10px;">
                    <a href="{{ asset('storage/'.$estudio->path_respuesta) }}" target="_blank"
                       style="display:inline-flex;align-items:center;gap:6px;font-size:12px;color:#2563EB;font-weight:700;text-decoration:none;background:#dbeafe;padding:5px 12px;border-radius:8px;">
                        <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m.75 12l3 3m0 0l3-3m-3 3v-6m-1.5-9H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                        Ver documento Sura
                    </a>
                </div>
                @endif
            </div>
            @endif

            @if($estudio->notas)
            <div style="font-size:12px;color:#94a3b8;font-style:italic;border-top:1px solid #f1f5f9;padding-top:10px;">
                📝 {{ $estudio->notas }}
            </div>
            @endif

        </div>
    </div>
    @endforeach
    </div>

    <div style="text-align:center;font-size:11px;color:#94a3b8;padding:14px 0 4px;font-weight:600;">
        {{ $estudios->count() }} envío(s) registrado(s) para esta solicitud
    </div>
    @endif
</div>

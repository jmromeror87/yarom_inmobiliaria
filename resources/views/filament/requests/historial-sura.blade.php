<div style="font-family:'Plus Jakarta Sans',sans-serif;padding:8px 0;">

    @if($estudios->isEmpty())
    <div style="text-align:center;padding:40px;color:#94a3b8;">
        <p style="font-weight:700;color:#475569;">Sin envíos registrados aún</p>
        <p style="font-size:0.85rem;">Use los botones de WhatsApp o correo para enviar a Sura.</p>
    </div>
    @else

    @foreach($estudios as $estudio)
    <div style="border:1px solid #e2e8f0;border-radius:16px;padding:20px;margin-bottom:14px;background:#fff;">

        {{-- Header --}}
        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:14px;">
            <div style="display:flex;align-items:center;gap:10px;">
                <span style="font-size:1.4rem;">
                    {{ $estudio->canal_envio === 'whatsapp' ? '📱' : ($estudio->canal_envio === 'email' ? '📧' : '🤝') }}
                </span>
                <div>
                    <div style="font-weight:800;font-size:0.9rem;color:#0f172a;">
                        {{ ucfirst($estudio->canal_envio) }}
                        @if($estudio->contacto_sura) — {{ $estudio->contacto_sura }} @endif
                    </div>
                    <div style="font-size:0.75rem;color:#94a3b8;">
                        Enviado: {{ $estudio->fecha_envio?->format('d/m/Y H:i') ?? 'N/A' }}
                        por {{ $estudio->enviadoPor?->name ?? 'Sistema' }}
                    </div>
                </div>
            </div>
            <div>
                @php
                    $color = match($estudio->resultado_sura) {
                        'aprobada'    => ['bg'=>'#f0fdf4','border'=>'#bbf7d0','text'=>'#15803d'],
                        'rechazada'   => ['bg'=>'#fef2f2','border'=>'#fecaca','text'=>'#dc2626'],
                        'condicional' => ['bg'=>'#fffbeb','border'=>'#fcd34d','text'=>'#d97706'],
                        default       => ['bg'=>'#f8fafc','border'=>'#e2e8f0','text'=>'#64748b'],
                    };
                @endphp
                <span style="background:{{ $color['bg'] }};border:1px solid {{ $color['border'] }};color:{{ $color['text'] }};font-size:0.75rem;font-weight:800;padding:4px 12px;border-radius:99px;">
                    {{ match($estudio->resultado_sura) {
                        'aprobada'    => '✅ Aprobada',
                        'rechazada'   => '❌ Rechazada',
                        'condicional' => '⚠️ Condicional',
                        default       => '⏳ Pendiente',
                    } }}
                </span>
            </div>
        </div>

        {{-- Mensaje enviado --}}
        @if($estudio->mensaje_enviado)
        <div style="background:#f8fafc;border-radius:10px;padding:12px;margin-bottom:12px;font-size:0.8rem;color:#475569;line-height:1.6;white-space:pre-wrap;">{{ $estudio->mensaje_enviado }}</div>
        @endif

        {{-- Respuesta Sura --}}
        @if($estudio->numero_solicitud_sura)
        <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:12px;margin-bottom:10px;">
            <div style="font-size:0.75rem;font-weight:800;text-transform:uppercase;letter-spacing:0.08em;color:#15803d;margin-bottom:8px;">
                Respuesta de Suramericana
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;font-size:0.82rem;">
                <div><span style="color:#64748b;">N° Solicitud Sura:</span> <strong>{{ $estudio->numero_solicitud_sura }}</strong></div>
                <div><span style="color:#64748b;">Analista:</span> {{ $estudio->analista_sura ?? 'N/A' }}</div>
                <div><span style="color:#64748b;">Fecha respuesta:</span> {{ $estudio->fecha_respuesta?->format('d/m/Y H:i') ?? 'N/A' }}</div>
            </div>
            @if($estudio->observaciones_sura)
            <div style="margin-top:8px;font-size:0.8rem;color:#374151;">{{ $estudio->observaciones_sura }}</div>
            @endif
            @if($estudio->path_respuesta)
            <div style="margin-top:8px;">
                <a href="{{ asset('storage/' . $estudio->path_respuesta) }}" target="_blank"
                   style="font-size:0.78rem;color:#2563eb;font-weight:700;">
                    📄 Ver documento respuesta Sura
                </a>
            </div>
            @endif
        </div>
        @endif

        @if($estudio->notas)
        <div style="font-size:0.78rem;color:#94a3b8;font-style:italic;">Notas: {{ $estudio->notas }}</div>
        @endif
    </div>
    @endforeach

    <div style="text-align:center;font-size:0.75rem;color:#94a3b8;padding:8px 0;">
        {{ $estudios->count() }} envío(s) registrado(s) para esta solicitud
    </div>
    @endif
</div>

<x-filament-panels::page>

@php
    $record  = $this->record->load(['property.tipo','property.municipio','thirds.third','suraStudies.enviadoPor','asesor']);
    $estado  = $record->estado;
    $color   = match($estado) {
        'aprobada'  => ['bg'=>'#f0fdf4','border'=>'#16a34a','text'=>'#15803d','marca'=>'#16a34a'],
        'rechazada' => ['bg'=>'#fef2f2','border'=>'#dc2626','text'=>'#dc2626','marca'=>'#dc2626'],
        'desistida' => ['bg'=>'#f8fafc','border'=>'#64748b','text'=>'#64748b','marca'=>'#94a3b8'],
        default     => ['bg'=>'#f8fafc','border'=>'#64748b','text'=>'#64748b','marca'=>'#94a3b8'],
    };
    $label   = match($estado) {
        'aprobada'  => 'APROBADA',
        'rechazada' => 'RECHAZADA',
        'desistida' => 'DESISTIDA',
        default     => strtoupper($estado),
    };
    $icon    = match($estado) {
        'aprobada'  => '✅',
        'rechazada' => '❌',
        'desistida' => '🚫',
        default     => '🔒',
    };
@endphp

<style>
    .readonly-wrap { position:relative; }
    .marca-agua {
        position:fixed; top:50%; left:50%;
        transform: translate(-50%,-50%) rotate(-35deg);
        font-size:96px; font-weight:900; letter-spacing:-2px;
        color:{{ $color['marca'] }}; opacity:0.06;
        pointer-events:none; z-index:0; white-space:nowrap;
        user-select:none;
    }
    .info-card { background:{{ $color['bg'] }}; border:1.5px solid {{ $color['border'] }}; border-radius:16px; padding:20px 24px; margin-bottom:20px; }
    .info-grid { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
    .info-item label { font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:0.08em; color:#94a3b8; display:block; margin-bottom:3px; }
    .info-item span { font-size:14px; font-weight:600; color:#0f172a; }
    .tercero-card { background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:16px; margin-bottom:10px; }
    .badge { display:inline-block; padding:3px 10px; border-radius:99px; font-size:11px; font-weight:800; text-transform:uppercase; }
    .sura-card { background:#f0f9ff; border:1px solid #bae6fd; border-radius:12px; padding:16px; margin-bottom:10px; }
    .section-title { font-size:13px; font-weight:800; text-transform:uppercase; letter-spacing:0.1em; color:#64748b; margin:20px 0 10px; }
</style>

<div class="readonly-wrap">
    <div class="marca-agua">{{ $label }}</div>

    {{-- Header de estado --}}
    <div class="info-card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
            <div>
                <div style="font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:0.1em;color:#94a3b8;margin-bottom:4px;">Solicitud</div>
                <div style="font-size:22px;font-weight:900;color:#0f172a;">{{ $record->numero }}</div>
            </div>
            <div style="text-align:right;">
                <div style="font-size:36px;">{{ $icon }}</div>
                <div style="font-size:18px;font-weight:900;color:{{ $color['text'] }};">{{ $label }}</div>
                @if($record->fecha_decision)
                <div style="font-size:12px;color:#94a3b8;">{{ $record->fecha_decision->format('d/m/Y') }}</div>
                @endif
            </div>
        </div>

        <div class="info-grid">
            <div class="info-item">
                <label>Tipo de solicitud</label>
                <span>{{ match($record->tipo) {
                    'estudio_propietario'  => '🏠 Estudio propietario',
                    'estudio_arrendatario' => '🔑 Estudio arrendatario',
                    'estudio_comprador'    => '🛒 Estudio comprador',
                    default                => $record->tipo,
                } }}</span>
            </div>
            <div class="info-item">
                <label>Inmueble</label>
                <span>{{ $record->property?->codigo }} — {{ $record->property?->direccion }}</span>
            </div>
            <div class="info-item">
                <label>Canon evaluado</label>
                <span>{{ $record->canon_evaluar ? '$' . number_format($record->canon_evaluar, 0, ',', '.') . ' COP' : 'N/A' }}</span>
            </div>
            <div class="info-item">
                <label>Asesor</label>
                <span>{{ $record->asesor?->name ?? 'N/A' }}</span>
            </div>
            <div class="info-item">
                <label>Fecha radicación</label>
                <span>{{ $record->fecha_radicacion?->format('d/m/Y') ?? 'N/A' }}</span>
            </div>
            <div class="info-item">
                <label>Decidido por</label>
                <span>{{ $record->decidido_por ?? 'N/A' }}</span>
            </div>
        </div>

        @if($record->concepto_evaluacion)
        <div style="margin-top:14px;padding-top:14px;border-top:1px solid {{ $color['border'] }};">
            <div style="font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:0.08em;color:#94a3b8;margin-bottom:6px;">Concepto de evaluación</div>
            <div style="font-size:14px;color:#374151;line-height:1.6;">{{ $record->concepto_evaluacion }}</div>
        </div>
        @endif
    </div>

    {{-- Terceros --}}
    @if($record->thirds->isNotEmpty())
    <div class="section-title">👥 Terceros evaluados</div>
    @foreach($record->thirds as $t)
    <div class="tercero-card">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;">
            <div>
                <div style="font-weight:800;font-size:15px;color:#0f172a;">{{ $t->third?->nombre_completo }}</div>
                <div style="font-size:12px;color:#64748b;">{{ $t->third?->tipo_documento }} {{ $t->third?->numero_documento }}</div>
            </div>
            <div style="display:flex;gap:8px;align-items:center;">
                <span class="badge" style="background:#e0f2fe;color:#0369a1;">{{ ucfirst($t->rol) }}</span>
                <span class="badge" style="background:{{ match($t->resultado_individual) {
                    'aprobado'    => '#f0fdf4',
                    'rechazado'   => '#fef2f2',
                    'condicional' => '#fffbeb',
                    default       => '#f8fafc',
                } }};color:{{ match($t->resultado_individual) {
                    'aprobado'    => '#15803d',
                    'rechazado'   => '#dc2626',
                    'condicional' => '#d97706',
                    default       => '#64748b',
                } }};">
                    {{ match($t->resultado_individual) {
                        'aprobado'    => '✅ Aprobado',
                        'rechazado'   => '❌ Rechazado',
                        'condicional' => '⚠️ Condicional',
                        default       => '⏳ Pendiente',
                    } }}
                </span>
            </div>
        </div>
        @if($t->ingresos_verificados || $t->score_datacredito)
        <div style="display:flex;gap:20px;margin-top:10px;font-size:12px;color:#64748b;">
            @if($t->ingresos_verificados)
            <span>💰 Ingresos: <strong>${{ number_format($t->ingresos_verificados, 0, ',', '.') }}</strong></span>
            @endif
            @if($t->score_datacredito)
            <span>📊 Score: <strong>{{ $t->score_datacredito }}</strong></span>
            @endif
            @if($t->reporte_negativo)
            <span style="color:#dc2626;">⚠️ Reporte negativo</span>
            @endif
        </div>
        @endif
    </div>
    @endforeach
    @endif

    {{-- Historial Sura --}}
    @if($record->suraStudies->isNotEmpty())
    <div class="section-title">📋 Historial Suramericana</div>
    @foreach($record->suraStudies as $sura)
    <div class="sura-card">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;">
            <div>
                <div style="font-weight:800;font-size:14px;color:#0f172a;">
                    {{ $sura->canal_envio === 'whatsapp' ? '📱' : ($sura->canal_envio === 'email' ? '📧' : '🤝') }}
                    {{ ucfirst($sura->canal_envio) }}
                    @if($sura->numero_solicitud_sura) — Sura N° {{ $sura->numero_solicitud_sura }} @endif
                </div>
                <div style="font-size:12px;color:#94a3b8;">
                    {{ $sura->fecha_envio?->format('d/m/Y H:i') }} · {{ $sura->enviadoPor?->name }}
                </div>
            </div>
            <span class="badge" style="background:{{ match($sura->resultado_sura) {
                'aprobada'    => '#f0fdf4',
                'rechazada'   => '#fef2f2',
                'condicional' => '#fffbeb',
                default       => '#f8fafc',
            } }};color:{{ match($sura->resultado_sura) {
                'aprobada'    => '#15803d',
                'rechazada'   => '#dc2626',
                'condicional' => '#d97706',
                default       => '#64748b',
            } }};">
                {{ match($sura->resultado_sura) {
                    'aprobada'    => '✅ Aprobada',
                    'rechazada'   => '❌ Rechazada',
                    'condicional' => '⚠️ Condicional',
                    default       => '⏳ Pendiente',
                } }}
            </span>
        </div>
        @if($sura->analista_sura)
        <div style="font-size:12px;color:#64748b;margin-top:6px;">Analista: {{ $sura->analista_sura }} · {{ $sura->fecha_respuesta?->format('d/m/Y H:i') }}</div>
        @endif
        @if($sura->observaciones_sura)
        <div style="font-size:13px;color:#374151;margin-top:8px;background:#fff;border-radius:8px;padding:10px;">{{ $sura->observaciones_sura }}</div>
        @endif
        @if($sura->path_respuesta)
        <div style="margin-top:8px;">
            <a href="{{ asset('storage/' . $sura->path_respuesta) }}" target="_blank"
               style="font-size:12px;color:#2563eb;font-weight:700;">📄 Ver documento respuesta Sura</a>
        </div>
        @endif
    </div>
    @endforeach
    @endif

</div>
</x-filament-panels::page>

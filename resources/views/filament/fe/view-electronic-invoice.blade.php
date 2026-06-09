<x-filament-panels::page>
    @php
        $fe = $this->record;
        $bill = $fe->rentBill;
        $estadoColor = match($fe->estado) {
            'aceptada', 'aceptada_con_notificacion' => '#16a34a',
            'rechazada', 'error' => '#dc2626',
            'anulada'   => '#d97706',
            default     => '#2563eb',
        };
        $estadoIcon = match($fe->estado) {
            'aceptada', 'aceptada_con_notificacion' => '✅',
            'rechazada' => '❌', 'error' => '⚠️',
            'anulada'   => '🚫', default => '🕐',
        };
    @endphp

    {{-- Header --}}
    <div style="background:linear-gradient(135deg,#0f172a,#1e3a8a);border-radius:16px;padding:28px 32px;color:#fff;margin-bottom:24px;">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:16px;">
            <div>
                <div style="font-size:12px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:rgba(255,255,255,.5);margin-bottom:6px;">
                    Factura Electrónica DIAN · {{ strtoupper($fe->operador_label) }} · {{ strtoupper($fe->ambiente) }}
                </div>
                <div style="font-size:28px;font-weight:900;font-family:monospace;letter-spacing:-.02em;">
                    {{ $fe->numero_factura_dian ?? '—' }}
                </div>
                <div style="font-size:14px;color:rgba(255,255,255,.7);margin-top:6px;">
                    Factura interna: <strong>{{ $bill?->numero }}</strong>
                    &nbsp;·&nbsp; {{ $bill?->arrendatario?->nombre_completo }}
                </div>
            </div>
            <div style="text-align:right;">
                <div style="font-size:36px;font-weight:900;color:{{ $estadoColor }};">
                    {{ $estadoIcon }}
                </div>
                <div style="font-size:14px;font-weight:800;color:{{ $estadoColor }};">
                    {{ $fe->estado_label }}
                </div>
                @if($fe->aceptada_en)
                    <div style="font-size:11px;color:rgba(255,255,255,.5);margin-top:4px;">
                        Aceptada: {{ $fe->aceptada_en->format('d/m/Y H:i') }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;">
        {{-- CUFE --}}
        <div style="background:#fff;border-radius:12px;border:1px solid #e2e8f0;padding:20px;">
            <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#64748b;margin-bottom:8px;">CUFE</div>
            <div style="font-family:monospace;font-size:12px;word-break:break-all;color:#0f172a;font-weight:600;">
                {{ $fe->cufe ?? 'Pendiente de emisión' }}
            </div>
        </div>

        {{-- Mensaje DIAN --}}
        <div style="background:#fff;border-radius:12px;border:1px solid #e2e8f0;padding:20px;">
            <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#64748b;margin-bottom:8px;">Respuesta DIAN</div>
            <div style="font-size:13px;color:#0f172a;">{{ $fe->mensaje_dian ?? 'Sin respuesta aún' }}</div>
            @if($fe->codigo_dian)
                <div style="font-size:11px;color:#64748b;margin-top:4px;">Código: {{ $fe->codigo_dian }}</div>
            @endif
        </div>
    </div>

    {{-- Datos de la factura --}}
    <div style="background:#fff;border-radius:12px;border:1px solid #e2e8f0;padding:20px;margin-bottom:20px;">
        <div style="font-size:13px;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:#0f172a;margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid #f1f5f9;">
            📄 Datos de la Factura
        </div>
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;">
            <div>
                <div style="font-size:11px;color:#64748b;font-weight:600;">Período</div>
                <div style="font-weight:700;">{{ $bill?->periodo_inicio?->format('d/m/Y') }} al {{ $bill?->periodo_fin?->format('d/m/Y') }}</div>
            </div>
            <div>
                <div style="font-size:11px;color:#64748b;font-weight:600;">Canon base</div>
                <div style="font-weight:700;">$ {{ number_format($bill?->canon_base ?? 0, 0, ',', '.') }}</div>
            </div>
            <div>
                <div style="font-size:11px;color:#64748b;font-weight:600;">Total factura</div>
                <div style="font-weight:700;color:#16a34a;">$ {{ number_format($bill?->total_factura ?? 0, 0, ',', '.') }}</div>
            </div>
            <div>
                <div style="font-size:11px;color:#64748b;font-weight:600;">Arrendatario</div>
                <div style="font-weight:700;">{{ $bill?->arrendatario?->nombre_completo }}</div>
            </div>
            <div>
                <div style="font-size:11px;color:#64748b;font-weight:600;">Documento</div>
                <div style="font-weight:700;font-family:monospace;">{{ $bill?->arrendatario?->tipo_documento }} {{ $bill?->arrendatario?->numero_documento }}</div>
            </div>
            <div>
                <div style="font-size:11px;color:#64748b;font-weight:600;">Consecutivo DIAN</div>
                <div style="font-weight:700;font-family:monospace;">{{ $fe->prefijo }}{{ $fe->consecutivo }}</div>
            </div>
        </div>
    </div>

    {{-- QR y documentos --}}
    @if($fe->qr_data || $fe->xml_url || $fe->pdf_url)
    <div style="background:#fff;border-radius:12px;border:1px solid #e2e8f0;padding:20px;margin-bottom:20px;">
        <div style="font-size:13px;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:#0f172a;margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid #f1f5f9;">
            📎 Documentos y QR
        </div>
        <div style="display:flex;gap:16px;align-items:flex-start;flex-wrap:wrap;">
            @if($fe->qr_data)
                <div style="text-align:center;">
                    <div style="font-size:11px;color:#64748b;margin-bottom:8px;font-weight:600;">Código QR DIAN</div>
                    {!! QrCode::size(140)->generate($fe->qr_data) !!}
                </div>
            @endif
            <div style="display:flex;flex-direction:column;gap:10px;">
                @if($fe->pdf_url)
                    <a href="{{ $fe->pdf_url }}" target="_blank" style="display:inline-flex;align-items:center;gap:8px;background:#0f172a;color:#fff;padding:10px 18px;border-radius:8px;text-decoration:none;font-size:13px;font-weight:700;">
                        📄 Representación Gráfica (PDF)
                    </a>
                @endif
                @if($fe->xml_url)
                    <a href="{{ $fe->xml_url }}" target="_blank" style="display:inline-flex;align-items:center;gap:8px;background:#1e3a8a;color:#fff;padding:10px 18px;border-radius:8px;text-decoration:none;font-size:13px;font-weight:700;">
                        📋 XML UBL 2.1
                    </a>
                @endif
                @if($fe->attached_document_url)
                    <a href="{{ $fe->attached_document_url }}" target="_blank" style="display:inline-flex;align-items:center;gap:8px;background:#374151;color:#fff;padding:10px 18px;border-radius:8px;text-decoration:none;font-size:13px;font-weight:700;">
                        📦 Documento Adjunto
                    </a>
                @endif
            </div>
        </div>
    </div>
    @endif

    {{-- Error técnico --}}
    @if($fe->ultimo_error)
    <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:12px;padding:16px;margin-bottom:20px;">
        <div style="font-size:12px;font-weight:800;color:#dc2626;text-transform:uppercase;margin-bottom:8px;">⚠️ Último error</div>
        <div style="font-family:monospace;font-size:12px;color:#7f1d1d;">{{ $fe->ultimo_error }}</div>
        <div style="font-size:11px;color:#dc2626;margin-top:8px;">Intentos: {{ $fe->intentos }} / {{ config('fe.reintentos.max', 3) }}</div>
    </div>
    @endif

    {{-- Anulación --}}
    @if($fe->estado === 'anulada')
    <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:12px;padding:16px;margin-bottom:20px;">
        <div style="font-size:12px;font-weight:800;color:#d97706;text-transform:uppercase;margin-bottom:8px;">🚫 Anulación</div>
        <div style="font-size:13px;color:#78350f;">{{ $fe->razon_anulacion }}</div>
        @if($fe->cufe_nota_credito)
            <div style="font-size:11px;color:#92400e;margin-top:8px;font-family:monospace;">CUFE NC: {{ $fe->cufe_nota_credito }}</div>
        @endif
        <div style="font-size:11px;color:#92400e;margin-top:4px;">Por: {{ $fe->anuladoPor?->name }} — {{ $fe->anulado_en?->format('d/m/Y H:i') }}</div>
    </div>
    @endif

    {{-- Trazabilidad --}}
    <div style="background:#fff;border-radius:12px;border:1px solid #e2e8f0;padding:20px;">
        <div style="font-size:13px;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:#0f172a;margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid #f1f5f9;">
            🕐 Trazabilidad
        </div>
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;">
            <div style="padding:12px;background:#f8fafc;border-radius:8px;">
                <div style="font-size:11px;color:#64748b;font-weight:600;">Emitido por</div>
                <div style="font-weight:700;font-size:13px;">{{ $fe->emitidoPor?->name ?? 'Sistema automático' }}</div>
                <div style="font-size:11px;color:#64748b;">{{ $fe->emitido_en?->format('d/m/Y H:i') }}</div>
            </div>
            <div style="padding:12px;background:#f8fafc;border-radius:8px;">
                <div style="font-size:11px;color:#64748b;font-weight:600;">Intentos</div>
                <div style="font-weight:700;font-size:20px;color:{{ $fe->intentos >= 3 ? '#dc2626' : '#0f172a' }};">{{ $fe->intentos }}</div>
            </div>
            <div style="padding:12px;background:#f8fafc;border-radius:8px;">
                <div style="font-size:11px;color:#64748b;font-weight:600;">Próximo reintento</div>
                <div style="font-weight:700;font-size:13px;">{{ $fe->proximo_reintento?->format('d/m/Y H:i') ?? 'N/A' }}</div>
            </div>
        </div>
    </div>
</x-filament-panels::page>

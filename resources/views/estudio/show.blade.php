<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estudio de Crédito {{ $study->request->numero }} — {{ $company?->razon_social ?? 'Serviarrendar' }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
               background: #f1f5f9; min-height: 100vh; display: flex; flex-direction: column; }

        .topbar { background: #0f172a; padding: 14px 24px; display: flex; align-items: center; gap: 12px; }
        .topbar-logo { width: 36px; height: 36px; background: linear-gradient(135deg,#0f172a,#2563EB);
                       border-radius: 10px; display: flex; align-items: center; justify-content: center; border: 2px solid #334155; }
        .topbar-name { color: #fff; font-size: 15px; font-weight: 900; letter-spacing: -.03em; text-transform: uppercase; }
        .topbar-name span { color: #E11D48; }
        .topbar-sub { color: #64748b; font-size: 10px; font-weight: 600; letter-spacing: .05em; text-transform: uppercase; }

        .container { max-width: 640px; margin: 32px auto; padding: 0 16px 40px; width: 100%; }

        .card { background: #fff; border-radius: 20px; border: 1px solid #e2e8f0;
                box-shadow: 0 4px 24px rgba(0,0,0,.06); overflow: hidden; margin-bottom: 16px; }

        .card-header { padding: 20px 24px 16px; border-bottom: 1px solid #f1f5f9; }
        .card-label { font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: .06em; margin-bottom: 4px; }
        .card-title { font-size: 22px; font-weight: 900; color: #0f172a; }
        .card-sub   { font-size: 13px; color: #64748b; margin-top: 4px; }

        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0; }
        .info-item { padding: 14px 24px; border-bottom: 1px solid #f8fafc; }
        .info-item:nth-child(odd) { border-right: 1px solid #f8fafc; }
        .info-label { font-size: 11px; color: #94a3b8; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; margin-bottom: 3px; }
        .info-value { font-size: 14px; font-weight: 600; color: #0f172a; }

        .terceros { padding: 16px 24px; }
        .tercero-row { display: flex; align-items: center; gap: 12px; padding: 10px 14px;
                       background: #f8fafc; border-radius: 10px; margin-bottom: 8px; }
        .tercero-avatar { width: 36px; height: 36px; background: linear-gradient(135deg,#2563EB,#7c3aed);
                          border-radius: 50%; display: flex; align-items: center; justify-content: center;
                          color: #fff; font-size: 14px; font-weight: 800; flex-shrink: 0; }
        .tercero-name  { font-size: 13px; font-weight: 700; color: #0f172a; }
        .tercero-doc   { font-size: 11px; color: #64748b; }
        .tercero-rol   { font-size: 10px; font-weight: 700; text-transform: uppercase;
                         color: #7c3aed; background: #f3e8ff; padding: 3px 8px; border-radius: 20px; flex-shrink: 0; }

        .docs-list { padding: 0 24px 16px; display: flex; flex-direction: column; gap: 6px; }
        .doc-type-title { font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase;
                          letter-spacing: .05em; margin: 10px 0 6px; }
        .doc-item { display: flex; align-items: center; gap: 10px; padding: 9px 14px;
                    background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px;
                    text-decoration: none; transition: background .15s; }
        .doc-item:hover { background: #eff6ff; border-color: #bfdbfe; }
        .doc-icon { width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center;
                    justify-content: center; flex-shrink: 0; font-size: 16px; }
        .doc-icon.pdf  { background: #fef2f2; }
        .doc-icon.img  { background: #f0fdf4; }
        .doc-icon.other{ background: #f8fafc; }
        .doc-name { font-size: 13px; font-weight: 600; color: #0f172a; flex: 1; min-width: 0;
                    white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .doc-label{ font-size: 11px; color: #64748b; }
        .doc-badge { font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 20px;
                     text-transform: uppercase; flex-shrink: 0; }
        .doc-badge.verificado { background:#f0fdf4; color:#15803d; }
        .doc-badge.recibido   { background:#eff6ff; color:#1d4ed8; }
        .doc-badge.pendiente  { background:#fefce8; color:#92400e; }
        .doc-badge.rechazado  { background:#fef2f2; color:#dc2626; }
        .no-docs { padding: 12px 24px; font-size: 13px; color: #94a3b8; text-align: center; }
        .person-section { border-top: 2px solid #f1f5f9; margin-top: 8px; padding-top: 8px; }

        .section-title { font-size: 12px; font-weight: 800; color: #374151; text-transform: uppercase;
                         letter-spacing: .06em; padding: 16px 24px 8px; }

        /* Form */
        .form-body { padding: 20px 24px; display: flex; flex-direction: column; gap: 16px; }
        .form-group label { display: block; font-size: 12px; font-weight: 700; color: #374151;
                            text-transform: uppercase; letter-spacing: .04em; margin-bottom: 6px; }
        .form-group input, .form-group textarea, .form-group select {
            width: 100%; padding: 10px 14px; border: 1.5px solid #e2e8f0; border-radius: 10px;
            font-size: 14px; color: #0f172a; background: #fff; outline: none; font-family: inherit;
            transition: border-color .15s;
        }
        .form-group input:focus, .form-group textarea:focus, .form-group select:focus {
            border-color: #2563EB;
        }
        .form-group textarea { resize: vertical; min-height: 90px; }

        .resultado-grid { display: grid; grid-template-columns: repeat(3,1fr); gap: 10px; }
        .resultado-opt  { position: relative; }
        .resultado-opt input[type=radio] { position: absolute; opacity: 0; width: 0; height: 0; }
        .resultado-opt label { display: flex; flex-direction: column; align-items: center; justify-content: center;
                               padding: 14px 8px; border: 2px solid #e2e8f0; border-radius: 12px;
                               cursor: pointer; text-align: center; transition: all .15s; gap: 6px; }
        .resultado-opt label .ico { font-size: 24px; }
        .resultado-opt label .lbl { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; }
        .resultado-opt input:checked + label.aprobada  { border-color: #16a34a; background: #f0fdf4; color: #15803d; }
        .resultado-opt input:checked + label.condicional{ border-color: #d97706; background: #fffbeb; color: #92400e; }
        .resultado-opt input:checked + label.rechazada  { border-color: #dc2626; background: #fef2f2; color: #991b1b; }
        .resultado-opt label:hover { border-color: #94a3b8; background: #f8fafc; }

        .upload-zone { border: 2px dashed #e2e8f0; border-radius: 12px; padding: 20px;
                       text-align: center; cursor: pointer; transition: border-color .15s; background: #f8fafc; }
        .upload-zone:hover { border-color: #2563EB; background: #eff6ff; }
        .upload-zone svg { width: 32px; height: 32px; color: #94a3b8; margin: 0 auto 8px; display: block; }
        .upload-zone .upload-text { font-size: 13px; color: #64748b; }
        .upload-zone .upload-hint { font-size: 11px; color: #94a3b8; margin-top: 4px; }
        .upload-zone input[type=file] { display: none; }

        .btn-submit { display: flex; align-items: center; justify-content: center; gap: 8px;
                      background: linear-gradient(135deg,#0f172a,#1e3a5f); color: #fff;
                      border: none; border-radius: 12px; padding: 15px 24px; font-size: 15px;
                      font-weight: 800; cursor: pointer; width: 100%; letter-spacing: -.01em;
                      transition: opacity .15s, transform .15s; }
        .btn-submit:hover { opacity: .9; transform: translateY(-1px); }

        /* Status cards */
        .status-card { padding: 40px 28px; text-align: center; }
        .status-icon  { font-size: 56px; margin-bottom: 16px; }
        .status-title { font-size: 20px; font-weight: 900; color: #0f172a; margin-bottom: 8px; }
        .status-sub   { font-size: 14px; color: #64748b; line-height: 1.6; }

        .alert { padding: 12px 16px; border-radius: 10px; font-size: 13px; font-weight: 600; margin-bottom: 12px; }
        .alert-success { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
        .alert-error   { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }

        .footer { text-align: center; padding: 20px; font-size: 11px; color: #94a3b8; margin-top: auto; }

        @media (max-width: 480px) {
            .info-grid { grid-template-columns: 1fr; }
            .info-item:nth-child(odd) { border-right: none; }
        }
    </style>
</head>
<body>

<div class="topbar">
    <div class="topbar-logo">
        <svg viewBox="0 0 32 32" fill="none"><path d="M4 28V14l12-9 12 9v14H20v-7h-8v7H4z" fill="#fff"/></svg>
    </div>
    <div>
        <div class="topbar-name">YAROM <span>INMO</span>BILIARIA</div>
        <div class="topbar-sub">{{ $company?->razon_social ?? 'Serviarrendar S.A.S' }}</div>
    </div>
</div>

<div class="container">

    @if(session('success'))
        <div class="alert alert-success">✅ {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-error">❌ {{ session('error') }}</div>
    @endif

    {{-- ── YA RESPONDIDO ── --}}
    @if($status === 'respondido')
    <div class="card">
        <div class="status-card">
            <div class="status-icon">
                @if($study->resultado_sura === 'aprobada') ✅
                @elseif($study->resultado_sura === 'condicional') ⚠️
                @else ❌ @endif
            </div>
            <div class="status-title">Estudio ya registrado</div>
            <div class="status-sub">
                La solicitud <strong>{{ $study->request->numero }}</strong> fue respondida
                el {{ $study->estudio_token_used_at?->format('d/m/Y \a \l\a\s H:i') }}.<br>
                Resultado: <strong>{{ ucfirst($study->resultado_sura) }}</strong>
            </div>
        </div>
    </div>

    {{-- ── PENDIENTE ── --}}
    @else

    {{-- Info de la solicitud --}}
    <div class="card">
        <div class="card-header">
            <div class="card-label">Estudio de Crédito — Confidencial</div>
            <div class="card-title">Solicitud {{ $study->request->numero }}</div>
            <div class="card-sub">
                Enviada el {{ $study->fecha_envio?->format('d/m/Y') ?? now()->format('d/m/Y') }}
                · Por favor registre la decisión y cargue el documento
            </div>
        </div>

        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">Inmueble</div>
                <div class="info-value">{{ $study->request->property?->codigo ?? '—' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Dirección</div>
                <div class="info-value">{{ $study->request->property?->direccion ?? '—' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Canon a evaluar</div>
                <div class="info-value">${{ number_format($study->request->canon_evaluar ?? 0, 0, ',', '.') }} COP</div>
            </div>
            <div class="info-item">
                <div class="info-label">Tipo de estudio</div>
                <div class="info-value">{{ $study->request->tipo_label }}</div>
            </div>
        </div>
    </div>

    {{-- Terceros con documentos --}}
    @if($study->request->thirds->count())
    <div class="card">
        <div class="section-title">Personas a evaluar — Documentos adjuntos</div>

        @php
        $tipoLabels = [
            'cedula'                => 'Cédula de ciudadanía',
            'desprendible_nomina'   => 'Desprendible de nómina',
            'extracto_bancario'     => 'Extracto bancario',
            'certificado_ingresos'  => 'Certificado de ingresos',
            'declaracion_renta'     => 'Declaración de renta',
            'carta_laboral'         => 'Carta laboral',
            'camara_comercio'       => 'Cámara de comercio',
            'rut'                   => 'RUT',
            'referencia_personal'   => 'Referencia personal',
            'referencia_comercial'  => 'Referencia comercial',
            'promesa_compraventa'   => 'Promesa de compraventa',
            'otro'                  => 'Otro documento',
        ];
        @endphp

        @foreach($study->request->thirds as $rt)
        @php $t = $rt->third; @endphp

        <div class="person-section">
            <div class="terceros" style="padding-bottom:0;">
                <div class="tercero-row">
                    <div class="tercero-avatar">{{ strtoupper(substr($t?->nombre ?? 'X', 0, 1)) }}</div>
                    <div style="flex:1;">
                        <div class="tercero-name">{{ $t?->nombre_completo ?? '—' }}</div>
                        <div class="tercero-doc">{{ $t?->tipo_documento ?? '' }} {{ $t?->numero_documento ?? '' }}</div>
                    </div>
                    <div class="tercero-rol">{{ $rt->rol }}</div>
                </div>
            </div>

            @if($rt->documents->count())
            <div class="docs-list">
                @foreach($rt->documents as $doc)
                @php
                    $ext   = strtolower($doc->extension ?? '');
                    $esPdf = $ext === 'pdf';
                    $esImg = in_array($ext, ['jpg','jpeg','png','webp']);
                    $iconClass = $esPdf ? 'pdf' : ($esImg ? 'img' : 'other');
                    $icono = $esPdf ? '📄' : ($esImg ? '🖼️' : '📎');
                @endphp
                <a href="{{ asset('storage/' . $doc->path) }}" target="_blank" class="doc-item">
                    <div class="doc-icon {{ $iconClass }}">{{ $icono }}</div>
                    <div style="flex:1;min-width:0;">
                        <div class="doc-name">{{ $doc->nombre_original }}</div>
                        <div class="doc-label">{{ $tipoLabels[$doc->tipo_documento] ?? $doc->tipo_documento }}</div>
                    </div>
                    <span class="doc-badge {{ $doc->estado_documento }}">{{ $doc->estado_documento }}</span>
                    <svg style="width:16px;height:16px;color:#94a3b8;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                </a>
                @endforeach
            </div>
            @else
            <div class="no-docs">Sin documentos cargados para esta persona</div>
            @endif

        @endforeach
    </div>
    </div>
    @endif

    {{-- Formulario de respuesta --}}
    <div class="card">
        <div class="section-title">Registrar decisión</div>
        <form method="POST" action="{{ route('estudio.store', $token) }}" enctype="multipart/form-data">
            @csrf
            <div class="form-body">

                {{-- Resultado --}}
                <div class="form-group">
                    <label>Resultado del estudio *</label>
                    <div class="resultado-grid">
                        <div class="resultado-opt">
                            <input type="radio" name="resultado_sura" id="r_aprobada" value="aprobada"
                                {{ old('resultado_sura') === 'aprobada' ? 'checked' : '' }}>
                            <label for="r_aprobada" class="aprobada">
                                <span class="ico">✅</span>
                                <span class="lbl">Aprobada</span>
                            </label>
                        </div>
                        <div class="resultado-opt">
                            <input type="radio" name="resultado_sura" id="r_condicional" value="condicional"
                                {{ old('resultado_sura') === 'condicional' ? 'checked' : '' }}>
                            <label for="r_condicional" class="condicional">
                                <span class="ico">⚠️</span>
                                <span class="lbl">Condicional</span>
                            </label>
                        </div>
                        <div class="resultado-opt">
                            <input type="radio" name="resultado_sura" id="r_rechazada" value="rechazada"
                                {{ old('resultado_sura') === 'rechazada' ? 'checked' : '' }}>
                            <label for="r_rechazada" class="rechazada">
                                <span class="ico">❌</span>
                                <span class="lbl">Rechazada</span>
                            </label>
                        </div>
                    </div>
                    @error('resultado_sura')
                        <p style="color:#dc2626;font-size:12px;margin-top:6px;">{{ $message }}</p>
                    @enderror
                </div>

                {{-- N° Solicitud Sura --}}
                <div class="form-group">
                    <label>N° Solicitud Sudamericana</label>
                    <input type="text" name="numero_solicitud_sura" placeholder="Ej: SURA-2026-00123"
                        value="{{ old('numero_solicitud_sura') }}">
                </div>

                {{-- Analista --}}
                <div class="form-group">
                    <label>Nombre del analista</label>
                    <input type="text" name="analista_sura" placeholder="Nombre completo del analista"
                        value="{{ old('analista_sura') }}">
                </div>

                {{-- Observaciones --}}
                <div class="form-group">
                    <label>Observaciones / Condiciones</label>
                    <textarea name="observaciones_sura"
                        placeholder="Ingrese las condiciones, restricciones o cualquier observación relevante...">{{ old('observaciones_sura') }}</textarea>
                </div>

                {{-- Documento --}}
                <div class="form-group">
                    <label>Documento de respuesta (PDF)</label>
                    <div class="upload-zone" onclick="document.getElementById('documento').click()">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <div class="upload-text" id="upload-label">Haz click para seleccionar el archivo</div>
                        <div class="upload-hint">PDF, JPG o PNG · Máx. 10 MB</div>
                        <input type="file" id="documento" name="documento" accept=".pdf,.jpg,.jpeg,.png"
                            onchange="document.getElementById('upload-label').textContent = this.files[0]?.name || 'Archivo seleccionado'">
                    </div>
                </div>

                <button type="submit" class="btn-submit">
                    <svg style="width:18px;height:18px" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Registrar decisión del estudio
                </button>

            </div>
        </form>
    </div>

    @endif

</div>

<div class="footer">
    © {{ date('Y') }} {{ $company?->razon_social ?? 'Serviarrendar S.A.S' }} · YarOM ERP
    · Este link es de uso exclusivo del analista de Sudamericana
</div>

</body>
</html>

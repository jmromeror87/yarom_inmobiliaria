<x-filament-panels::page>
<div style="font-family:'Plus Jakarta Sans',system-ui,sans-serif;max-width:860px;">

    {{-- Hero Banner --}}
    <div style="background:linear-gradient(135deg,#0F172A 0%,#1e3a8a 60%,#1e40af 100%);
                border-radius:18px;padding:24px 32px;margin-bottom:24px;
                display:flex;align-items:center;justify-content:space-between;
                box-shadow:0 8px 28px rgba(15,23,42,.22);position:relative;overflow:hidden;">

        <div style="position:absolute;right:-20px;top:-20px;width:160px;height:160px;border-radius:50%;background:rgba(255,255,255,.03);"></div>

        <div style="display:flex;align-items:center;gap:16px;position:relative;z-index:1;">
            <div style="width:52px;height:52px;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.15);
                        border-radius:14px;display:flex;align-items:center;justify-content:center;">
                <svg width="26" height="26" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/>
                </svg>
            </div>
            <div>
                <p style="font-size:20px;font-weight:900;color:#fff;margin:0;">Servicio de Correo Electrónico</p>
                <p style="font-size:12px;color:rgba(255,255,255,.5);margin:4px 0 0;font-weight:500;">
                    Configuración activa del servidor de envío de correos
                </p>
            </div>
        </div>

        {{-- Estado --}}
        <div style="position:relative;z-index:1;">
            <div style="background:rgba(134,239,172,.15);border:1px solid rgba(134,239,172,.3);
                        border-radius:12px;padding:12px 20px;text-align:center;">
                <div style="display:flex;align-items:center;gap:8px;justify-content:center;">
                    <div style="width:10px;height:10px;border-radius:50%;background:#86efac;
                                box-shadow:0 0 8px #86efac;animation:pulse 2s infinite;"></div>
                    <span style="font-size:13px;font-weight:800;color:#86efac;">Servicio Activo</span>
                </div>
                <div style="font-size:10px;color:rgba(255,255,255,.4);margin-top:4px;font-weight:600;">
                    Resend API — Transaccional
                </div>
            </div>
        </div>
    </div>

    {{-- Config Cards --}}
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:24px;">

        {{-- Remitente --}}
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:20px 24px;
                    box-shadow:0 2px 8px rgba(15,23,42,.06);">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;">
                <div style="width:36px;height:36px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;
                            display:flex;align-items:center;justify-content:center;">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="#1e3a8a" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
                    </svg>
                </div>
                <span style="font-size:13px;font-weight:800;color:#1e293b;">Remitente</span>
            </div>
            <div style="margin-bottom:12px;">
                <div style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px;">Nombre</div>
                <div style="font-size:14px;font-weight:700;color:#1e293b;background:#f8fafc;border:1px solid #e2e8f0;
                            border-radius:8px;padding:8px 12px;font-family:monospace;">
                    {{ $from_name }}
                </div>
            </div>
            <div>
                <div style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px;">Dirección</div>
                <div style="font-size:13px;font-weight:700;color:#0284c7;background:#f0f9ff;border:1px solid #bae6fd;
                            border-radius:8px;padding:8px 12px;font-family:monospace;">
                    {{ $from_address }}
                </div>
            </div>
        </div>

        {{-- Proveedor --}}
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:20px 24px;
                    box-shadow:0 2px 8px rgba(15,23,42,.06);">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;">
                <div style="width:36px;height:36px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;
                            display:flex;align-items:center;justify-content:center;">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="#16a34a" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 14.25h13.5m-13.5 0a3 3 0 01-3-3m3 3a3 3 0 100 6h13.5a3 3 0 100-6m-16.5-3a3 3 0 013-3h13.5a3 3 0 013 3m-19.5 0a4.5 4.5 0 01.9-2.7L5.737 5.1a3.375 3.375 0 012.7-1.35h7.126c1.062 0 2.062.5 2.7 1.35l2.587 3.45a4.5 4.5 0 01.9 2.7m0 0a3 3 0 01-3 3m0 3h.008v.008h-.008v-.008zm0-6h.008v.008h-.008v-.008zm-3 6h.008v.008h-.008v-.008zm0-6h.008v.008h-.008v-.008z"/>
                    </svg>
                </div>
                <span style="font-size:13px;font-weight:800;color:#1e293b;">Proveedor</span>
            </div>
            <div style="margin-bottom:12px;">
                <div style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px;">Driver</div>
                <div style="display:inline-flex;align-items:center;gap:6px;background:#f0fdf4;border:1px solid #bbf7d0;
                            border-radius:8px;padding:6px 14px;">
                    <div style="width:8px;height:8px;border-radius:50%;background:#16a34a;"></div>
                    <span style="font-size:13px;font-weight:800;color:#16a34a;text-transform:uppercase;">{{ $mailer }}</span>
                </div>
            </div>
            <div>
                <div style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px;">API Key</div>
                <div style="font-size:12px;font-weight:700;color:#475569;background:#f8fafc;border:1px solid #e2e8f0;
                            border-radius:8px;padding:8px 12px;font-family:monospace;letter-spacing:.05em;">
                    {{ $resend_key_masked }}
                </div>
            </div>
        </div>
    </div>

    {{-- Dónde se usa --}}
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:20px 24px;
                box-shadow:0 2px 8px rgba(15,23,42,.06);margin-bottom:24px;">
        <div style="font-size:11px;font-weight:800;color:#64748b;text-transform:uppercase;letter-spacing:.08em;margin-bottom:16px;">
            Dónde se usa el correo en el sistema
        </div>
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;">
            @foreach([
                ['Invitación de usuarios','heroicon-o-user-plus','Cuando se crea un nuevo usuario','#0284c7'],
                ['Servicios / Mantenimientos','heroicon-o-wrench-screwdriver','Notificación al proveedor asignado','#16a34a'],
                ['Facturación','heroicon-o-document-text','Envío de facturas al arrendatario','#d97706'],
                ['Liquidaciones','heroicon-o-banknotes','Notificación al propietario','#7c3aed'],
                ['Contratos vencidos','heroicon-o-calendar-days','Alerta de vencimiento próximo','#E11D48'],
                ['Facturas vencidas','heroicon-o-exclamation-circle','Recordatorio de mora','#dc2626'],
            ] as [$titulo, $icono, $desc, $color])
            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:14px 16px;">
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;">
                    <div style="width:28px;height:28px;background:{{ $color }}15;border:1px solid {{ $color }}30;border-radius:8px;
                                display:flex;align-items:center;justify-content:center;">
                        <x-filament::icon :icon="$icono" style="width:14px;height:14px;color:{{ $color }};"/>
                    </div>
                    <span style="font-size:12px;font-weight:700;color:#1e293b;">{{ $titulo }}</span>
                </div>
                <p style="font-size:11px;color:#64748b;margin:0;line-height:1.4;">{{ $desc }}</p>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Info Resend --}}
    <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:12px;padding:16px 20px;
                display:flex;align-items:flex-start;gap:12px;">
        <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="#1e3a8a" stroke-width="1.8" style="flex-shrink:0;margin-top:2px;">
            <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/>
        </svg>
        <div>
            <p style="font-size:13px;font-weight:700;color:#1e3a8a;margin:0 0 4px;">Servicio gestionado por Resend</p>
            <p style="font-size:12px;color:#1e40af;margin:0;line-height:1.5;">
                Para cambiar el remitente, la API Key o el dominio, edite el archivo <code style="background:#dbeafe;padding:1px 6px;border-radius:4px;">.env</code>
                en el servidor y ejecute <code style="background:#dbeafe;padding:1px 6px;border-radius:4px;">php artisan config:clear</code>.
                Administre su cuenta en <strong>resend.com</strong>.
            </p>
        </div>
    </div>

</div>
</x-filament-panels::page>

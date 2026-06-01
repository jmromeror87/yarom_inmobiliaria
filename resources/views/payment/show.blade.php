<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagar Factura {{ $bill->numero }} — {{ $company?->razon_social ?? 'Serviarrendar' }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
               background: #f1f5f9; min-height: 100vh; display: flex; flex-direction: column; }

        .topbar { background: #0f172a; padding: 14px 24px; display: flex; align-items: center; gap: 12px; }
        .topbar-logo { width: 36px; height: 36px; background: linear-gradient(135deg,#0f172a,#2563EB);
                       border-radius: 10px; display: flex; align-items: center; justify-content: center; border: 2px solid #334155; }
        .topbar-logo svg { width: 20px; height: 20px; }
        .topbar-name { color: #fff; font-size: 15px; font-weight: 900; letter-spacing: -.03em; text-transform: uppercase; }
        .topbar-name span { color: #E11D48; }
        .topbar-sub { color: #64748b; font-size: 10px; font-weight: 600; letter-spacing: .05em; text-transform: uppercase; }

        .container { max-width: 560px; margin: 32px auto; padding: 0 16px 40px; width: 100%; }

        .card { background: #fff; border-radius: 20px; border: 1px solid #e2e8f0;
                box-shadow: 0 4px 24px rgba(0,0,0,.06); overflow: hidden; }

        .card-header { padding: 24px 28px 20px; border-bottom: 1px solid #f1f5f9; }
        .bill-number { font-size: 13px; font-weight: 700; color: #94a3b8; text-transform: uppercase;
                       letter-spacing: .05em; margin-bottom: 4px; }
        .bill-amount { font-size: 36px; font-weight: 900; color: #0f172a; letter-spacing: -.03em; }
        .bill-amount span { font-size: 18px; font-weight: 700; color: #64748b; }
        .bill-period { font-size: 13px; color: #64748b; margin-top: 4px; }

        .due-badge { display: inline-flex; align-items: center; gap: 6px; margin-top: 10px;
                     padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; }
        .due-badge.ok  { background: #f0fdf4; color: #15803d; }
        .due-badge.warn{ background: #fffbeb; color: #92400e; }
        .due-badge.red { background: #fef2f2; color: #dc2626; }

        .details { padding: 20px 28px; border-bottom: 1px solid #f1f5f9; }
        .detail-row { display: flex; justify-content: space-between; align-items: center;
                      padding: 7px 0; font-size: 14px; }
        .detail-row + .detail-row { border-top: 1px solid #f8fafc; }
        .detail-label { color: #64748b; }
        .detail-value { font-weight: 600; color: #0f172a; text-align: right; }
        .detail-total { font-size: 16px; font-weight: 900; color: #0f172a; }

        .actions { padding: 24px 28px; display: flex; flex-direction: column; gap: 12px; }

        .btn-wompi { display: flex; align-items: center; justify-content: center; gap: 10px;
                     background: linear-gradient(135deg, #2563EB, #1d4ed8); color: #fff;
                     border: none; border-radius: 14px; padding: 16px 24px; font-size: 16px;
                     font-weight: 800; cursor: pointer; text-decoration: none; width: 100%;
                     transition: opacity .15s, transform .15s; }
        .btn-wompi:hover { opacity: .9; transform: translateY(-1px); }
        .btn-wompi svg { width: 22px; height: 22px; }

        .divider { display: flex; align-items: center; gap: 12px; color: #94a3b8; font-size: 12px; }
        .divider::before, .divider::after { content: ''; flex: 1; height: 1px; background: #e2e8f0; }

        .oficina-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; padding: 18px 20px; }
        .oficina-title { font-size: 14px; font-weight: 800; color: #374151; margin-bottom: 12px;
                         display: flex; align-items: center; gap: 8px; }
        .oficina-row { display: flex; align-items: flex-start; gap: 10px; font-size: 13px;
                       color: #64748b; margin-bottom: 8px; }
        .oficina-row svg { width: 16px; height: 16px; flex-shrink: 0; margin-top: 1px; color: #94a3b8; }
        .ref-box { background: #fff; border: 2px dashed #e2e8f0; border-radius: 10px;
                   padding: 10px 16px; text-align: center; margin-top: 12px; }
        .ref-label { font-size: 11px; color: #94a3b8; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; }
        .ref-value { font-size: 18px; font-weight: 900; color: #0f172a; letter-spacing: .05em; font-family: monospace; }

        .status-card { padding: 40px 28px; text-align: center; }
        .status-icon { font-size: 56px; margin-bottom: 16px; }
        .status-title { font-size: 22px; font-weight: 900; color: #0f172a; margin-bottom: 8px; }
        .status-sub { font-size: 14px; color: #64748b; }

        .footer { text-align: center; padding: 20px; font-size: 11px; color: #94a3b8; }
        .secure { display: flex; align-items: center; justify-content: center; gap: 6px;
                  font-size: 11px; color: #94a3b8; margin-top: 12px; }
        .secure svg { width: 14px; height: 14px; }
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
    <div class="card">

        @if($status === 'pagada')
        {{-- ── FACTURA YA PAGADA ── --}}
        <div class="status-card">
            <div class="status-icon">✅</div>
            <div class="status-title">¡Factura ya está pagada!</div>
            <div class="status-sub">
                La factura <strong>{{ $bill->numero }}</strong> fue pagada el
                {{ $bill->fecha_pago?->format('d/m/Y') }}.<br>
                Si tienes dudas, comunícate con nosotros.
            </div>
        </div>

        @elseif($status === 'expirado')
        {{-- ── TOKEN EXPIRADO ── --}}
        <div class="status-card">
            <div class="status-icon">⏰</div>
            <div class="status-title">Este link ha expirado</div>
            <div class="status-sub">
                El link de pago para <strong>{{ $bill->numero }}</strong> venció.<br>
                Comunícate con la inmobiliaria para recibir un nuevo link.
            </div>
        </div>

        @else
        {{-- ── PAGO ACTIVO ── --}}
        <div class="card-header">
            <div class="bill-number">Factura de arrendamiento</div>
            <div class="bill-amount">
                <span>$</span>{{ number_format($bill->saldo_pendiente, 0, ',', '.') }}
                <span>COP</span>
            </div>
            <div class="bill-period">
                Período: {{ $bill->periodo_inicio->format('d/m/Y') }} — {{ $bill->periodo_fin->format('d/m/Y') }}
            </div>
            @php
                $hoy = now()->startOfDay();
                $vence = $bill->fecha_limite_pago;
                $dias = $hoy->diffInDays($vence, false);
            @endphp
            @if($dias > 3)
                <span class="due-badge ok">✓ Vence el {{ $vence->format('d/m/Y') }}</span>
            @elseif($dias >= 0)
                <span class="due-badge warn">⚠ Vence en {{ $dias }} día{{ $dias !== 1 ? 's' : '' }}</span>
            @else
                <span class="due-badge red">⚠ Venció el {{ $vence->format('d/m/Y') }}</span>
            @endif
        </div>

        <div class="details">
            <div class="detail-row">
                <span class="detail-label">Arrendatario</span>
                <span class="detail-value">{{ $bill->arrendatario?->nombre_completo ?? '—' }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Inmueble</span>
                <span class="detail-value">{{ $bill->property?->direccion ?? '—' }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Canon base</span>
                <span class="detail-value">${{ number_format($bill->canon_base, 0, ',', '.') }}</span>
            </div>
            @if($bill->cuota_administracion > 0)
            <div class="detail-row">
                <span class="detail-label">Administración</span>
                <span class="detail-value">${{ number_format($bill->cuota_administracion, 0, ',', '.') }}</span>
            </div>
            @endif
            @if($bill->mora_acumulada > 0)
            <div class="detail-row">
                <span class="detail-label" style="color:#dc2626;">Mora acumulada</span>
                <span class="detail-value" style="color:#dc2626;">${{ number_format($bill->mora_acumulada, 0, ',', '.') }}</span>
            </div>
            @endif
            @if($bill->descuentos > 0)
            <div class="detail-row">
                <span class="detail-label" style="color:#16a34a;">Descuento</span>
                <span class="detail-value" style="color:#16a34a;">-${{ number_format($bill->descuentos, 0, ',', '.') }}</span>
            </div>
            @endif
            <div class="detail-row" style="border-top: 2px solid #e2e8f0; margin-top: 4px; padding-top: 12px;">
                <span class="detail-label detail-total">Total a pagar</span>
                <span class="detail-value detail-total">${{ number_format($bill->saldo_pendiente, 0, ',', '.') }} COP</span>
            </div>
        </div>

        <div class="actions">

            {{-- Botón Wompi --}}
            <a href="{{ $wompiUrl }}" class="btn-wompi">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
                Pagar en línea
            </a>
            <div style="font-size:11px;color:#94a3b8;text-align:center;margin-top:-4px;">
                PSE · Nequi · Tarjeta débito/crédito · Bancolombia
            </div>

            <div class="divider">o paga en oficina</div>

            {{-- Pago en oficina --}}
            <div class="oficina-card">
                <div class="oficina-title">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:18px;height:18px;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    Paga presencialmente
                </div>
                @if($company?->direccion)
                <div class="oficina-row">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    {{ $company->direccion }}
                </div>
                @endif
                @if($company?->telefono)
                <div class="oficina-row">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.948V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                    {{ $company->telefono }}
                </div>
                @endif
                <div class="ref-box">
                    <div class="ref-label">Tu referencia de pago</div>
                    <div class="ref-value">{{ $bill->numero }}</div>
                </div>
            </div>

            <div class="secure">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                Pago seguro procesado por Wompi · Bancolombia
            </div>
        </div>
        @endif

    </div>
</div>

<div class="footer">
    © {{ date('Y') }} {{ $company?->razon_social ?? 'Serviarrendar S.A.S' }} · YarOM ERP
</div>

</body>
</html>

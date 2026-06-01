<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultado del pago — Serviarrendar</title>
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
        .container { max-width: 500px; margin: 40px auto; padding: 0 16px; width: 100%; }
        .card { background: #fff; border-radius: 20px; border: 1px solid #e2e8f0;
                box-shadow: 0 4px 24px rgba(0,0,0,.06); padding: 40px 32px; text-align: center; }
        .icon { font-size: 64px; margin-bottom: 20px; }
        .title { font-size: 24px; font-weight: 900; margin-bottom: 10px; }
        .sub { font-size: 14px; color: #64748b; line-height: 1.6; margin-bottom: 24px; }
        .detail-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px;
                      padding: 16px 20px; margin-bottom: 24px; text-align: left; }
        .detail-row { display: flex; justify-content: space-between; font-size: 13px;
                      padding: 5px 0; }
        .detail-row + .detail-row { border-top: 1px solid #f1f5f9; }
        .detail-label { color: #64748b; }
        .detail-value { font-weight: 700; color: #0f172a; }
        .btn { display: inline-flex; align-items: center; justify-content: center;
               background: #0f172a; color: #fff; border-radius: 12px; padding: 12px 24px;
               font-size: 14px; font-weight: 700; text-decoration: none; }
        .footer { text-align: center; padding: 20px; font-size: 11px; color: #94a3b8; margin-top: auto; }
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
        @php
            $status = $transaction['status'] ?? 'PENDING';
            $amount = isset($transaction['amount_in_cents']) ? $transaction['amount_in_cents'] / 100 : 0;
        @endphp

        @if($status === 'APPROVED')
            <div class="icon">✅</div>
            <div class="title" style="color:#15803d;">¡Pago exitoso!</div>
            <div class="sub">
                Tu pago fue procesado correctamente.<br>
                Recibirás confirmación por WhatsApp.
            </div>
            @if($transaction)
            <div class="detail-box">
                <div class="detail-row">
                    <span class="detail-label">Factura</span>
                    <span class="detail-value">{{ $bill?->numero ?? '—' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Valor pagado</span>
                    <span class="detail-value">${{ number_format($amount, 0, ',', '.') }} COP</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Método</span>
                    <span class="detail-value">{{ $transaction['payment_method_type'] ?? '—' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Referencia Wompi</span>
                    <span class="detail-value" style="font-family:monospace;font-size:12px;">{{ $transaction['id'] ?? '—' }}</span>
                </div>
            </div>
            @endif

        @elseif($status === 'DECLINED' || $status === 'ERROR')
            <div class="icon">❌</div>
            <div class="title" style="color:#dc2626;">Pago rechazado</div>
            <div class="sub">
                Tu pago no pudo procesarse.<br>
                Verifica tus datos o intenta con otro método de pago.
            </div>

        @else
            <div class="icon">⏳</div>
            <div class="title" style="color:#d97706;">Pago en proceso</div>
            <div class="sub">
                Tu transacción está siendo procesada.<br>
                Te notificaremos por WhatsApp cuando se confirme.
            </div>
        @endif

        <a href="/" class="btn">Volver al inicio</a>
    </div>
</div>

<div class="footer">
    © {{ date('Y') }} {{ $company?->razon_social ?? 'Serviarrendar S.A.S' }} · YarOM ERP
</div>

</body>
</html>

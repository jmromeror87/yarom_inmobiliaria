<x-filament-panels::page>
@php
    $ready   = $wapStatus['ready']  ?? false;
    $qr      = $wapStatus['qr']     ?? null;
    $estado  = $wapStatus['estado'] ?? null;
    $error   = $wapStatus['error']  ?? null;
    $numero  = $wapStatus['numero'] ?? null;
    $desde   = $wapStatus['conectado_desde'] ?? null;
    $desdeTexto = $desde ? \Carbon\Carbon::parse($desde)->setTimezone(config('app.timezone'))->translatedFormat('d/m/Y, h:i a') : null;

    $estadoLabel = match($estado) {
        'conectado'    => 'Conectado',
        'esperando_qr' => 'Esperando escaneo',
        'autenticando' => 'Autenticando...',
        'iniciando'    => 'Iniciando...',
        'desconectado' => 'Desconectado',
        'error_auth'   => 'Error de autenticación',
        'error_init'   => 'Error de inicio',
        null           => 'Servicio apagado',
        default        => $estado,
    };
    $estadoColor = match($estado) {
        'conectado'    => '#16a34a',
        'esperando_qr' => '#d97706',
        'autenticando' => '#2563eb',
        'iniciando'    => '#6b7280',
        default        => '#dc2626',
    };
    $bgColor = $ready ? '#f0fdf4' : ($qr ? '#fffbeb' : ($estado === null ? '#f8fafc' : '#fef2f2'));
    $borderColor = $ready ? '#bbf7d0' : ($qr ? '#fde68a' : ($estado === null ? '#e2e8f0' : '#fecaca'));
@endphp

{{-- Polling automático cada 4 segundos --}}
<div wire:poll.4000ms="poll" style="max-width: 680px; margin: 0 auto; font-family: system-ui, sans-serif;">

    {{-- ── Indicador de actualización automática ── --}}
    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 16px; font-size: 12px; color: #94a3b8;">
        <span style="width: 8px; height: 8px; border-radius: 50%; background: #22c55e;
                     display: inline-block; animation: pulse 2s infinite;"></span>
        Actualizando automáticamente cada 4 segundos
    </div>
    <style>
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.3; }
        }
    </style>

    {{-- ── CONECTADO ────────────────────────────── --}}
    @if($ready)
    <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 14px 18px;
                display:flex; align-items:center; justify-content:space-between; margin-bottom: 16px;">
        <div style="display:flex; align-items:center; gap:10px; font-size:14px; font-weight:700; color:#0f172a;">
            📶 Estado
        </div>
        <span style="background:#dcfce7; color:#15803d; font-weight:700; font-size:12px; padding:5px 14px; border-radius:99px;">
            ✓ Conectado
        </span>
    </div>

    <div style="background: #f0fdf4; border: 2px solid #bbf7d0; border-radius: 20px;
                padding: 36px 32px; text-align: center; margin-bottom: 24px;">
        <div style="width:64px;height:64px;border-radius:50%;background:#dcfce7;display:flex;align-items:center;
                    justify-content:center;margin:0 auto 16px;font-size:32px;">✅</div>
        <div style="font-size: 20px; font-weight: 900; color: #15803d; margin-bottom: 8px;">
            Sesión activa
        </div>
        <div style="font-size: 13px; color: #16a34a; margin-bottom: 18px;">
            WhatsApp conectado y listo para enviar mensajes a clientes.
        </div>

        <div style="background:#fff; border:1px solid #bbf7d0; border-radius:10px; padding:14px 18px; text-align:left; max-width:360px; margin:0 auto;">
            <div style="display:flex; justify-content:space-between; padding:4px 0; font-size:13px;">
                <span style="color:#64748b;">📱 Número conectado</span>
                <span style="font-weight:700; color:#0f172a;">{{ $numero ? '+' . $numero : '—' }}</span>
            </div>
            <div style="display:flex; justify-content:space-between; padding:4px 0; font-size:13px;">
                <span style="color:#64748b;">🕒 Vinculado el</span>
                <span style="font-weight:700; color:#0f172a;">{{ $desdeTexto ?? '—' }}</span>
            </div>
        </div>

        <button
            wire:click="reiniciar"
            wire:confirm="¿Reiniciar la sesión de WhatsApp? Se cerrará la conexión actual y tendrás que escanear un nuevo QR."
            style="margin-top:20px; background:#16a34a; color:#fff; border:none; border-radius:10px;
                   padding:10px 22px; font-size:13px; font-weight:700; cursor:pointer;">
            🔄 Reiniciar y generar QR
        </button>
    </div>

    {{-- ── QR VISIBLE — escanear ───────────────── --}}
    @elseif($qr)
    <div style="background: #fff; border: 2px solid #fde68a; border-radius: 20px;
                padding: 36px 32px; text-align: center; margin-bottom: 24px;">
        <div style="font-size: 20px; font-weight: 900; color: #92400e; margin-bottom: 6px;">
            Escanea el código QR con WhatsApp
        </div>
        <div style="font-size: 13px; color: #78716c; margin-bottom: 28px;">
            Abre WhatsApp en tu celular → <strong>Dispositivos vinculados</strong> → <strong>Vincular dispositivo</strong>
        </div>

        <div style="display: inline-block; padding: 16px; background: #fff;
                    border-radius: 16px; box-shadow: 0 8px 40px rgba(0,0,0,0.15); margin-bottom: 20px;">
            <img src="{{ $qr }}" width="260" height="260" style="display: block; border-radius: 8px;">
        </div>

        <div style="font-size: 12px; color: #a16207; background: #fefce8;
                    border: 1px solid #fde68a; border-radius: 8px; padding: 8px 16px; display: inline-block;">
            El QR se actualiza solo — no necesitas recargar la página
        </div>
    </div>

    {{-- ── SERVICIO APAGADO o ERROR ─────────────── --}}
    @else
    <div style="background: {{ $bgColor }}; border: 2px solid {{ $borderColor }};
                border-radius: 20px; padding: 36px 32px; text-align: center; margin-bottom: 24px;">
        <div style="font-size: 48px; margin-bottom: 16px;">
            @if($estado === null) ⚪ @else ⚠️ @endif
        </div>
        <div style="font-size: 20px; font-weight: 900; color: {{ $estadoColor }}; margin-bottom: 8px;">
            {{ $estadoLabel }}
        </div>
        @if($error)
        <div style="font-size: 12px; color: #dc2626; margin-top: 8px; font-style: italic;">
            {{ $error }}
        </div>
        @endif
        <div style="font-size: 13px; color: #64748b; margin-top: 12px;">
            @if($estado === null)
                El servicio WhatsApp no está activo en este momento
            @else
                Esperando conexión... la página se actualizará automáticamente
            @endif
        </div>
    </div>
    @endif

    {{-- ── Dónde se usa WhatsApp en el ERP ──────── --}}
    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px 24px;">
        <div style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase;
                    letter-spacing: 0.5px; margin-bottom: 14px;">
            Dónde se usa WhatsApp en el sistema
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 10px;">
            @foreach([
                ['💰', 'Liquidaciones',       'Envío al propietario al aprobar o pagar'],
                ['📄', 'Contratos CAD',        'Envío al propietario para revisión'],
                ['🔑', 'Contratos Arriendo',   'Envío al arrendatario'],
                ['📋', 'Solicitudes',          'Notificación de estado al cliente'],
            ] as [$icon, $titulo, $desc])
            <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 14px;">
                <div style="font-size: 20px; margin-bottom: 4px;">{{ $icon }}</div>
                <div style="font-size: 13px; font-weight: 700; color: #374151;">{{ $titulo }}</div>
                <div style="font-size: 11px; color: #9ca3af; margin-top: 2px; line-height: 1.4;">{{ $desc }}</div>
            </div>
            @endforeach
        </div>
    </div>

</div>
</x-filament-panels::page>

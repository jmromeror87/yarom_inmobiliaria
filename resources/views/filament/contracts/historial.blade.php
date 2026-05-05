<div style="font-family:'Plus Jakarta Sans',sans-serif;padding:8px 0;">
    @foreach($historial as $item)
    <div style="border:1px solid #e2e8f0;border-radius:16px;padding:20px;margin-bottom:16px;background:#fff;">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:14px;">
            <div>
                <span style="font-size:0.7rem;font-weight:800;text-transform:uppercase;letter-spacing:0.1em;color:#E11D48;background:#FEE2E2;padding:3px 10px;border-radius:99px;">
                    {{ $item->clause->numero }}
                </span>
                <span style="font-size:0.9rem;font-weight:700;color:#0f172a;margin-left:8px;">
                    {{ $item->clause->titulo }}
                </span>
            </div>
            <div style="text-align:right;font-size:0.75rem;color:#94a3b8;">
                <div style="font-weight:700;color:#475569;">{{ $item->editor?->name ?? 'Sistema' }}</div>
                <div>{{ $item->editado_en?->format('d/m/Y H:i:s') }}</div>
                @if($item->ip_address)
                <div style="font-size:0.68rem;">IP: {{ $item->ip_address }}</div>
                @endif
            </div>
        </div>
        @if($item->razon_cambio)
        <div style="background:#fffbeb;border:1px solid #fcd34d;border-radius:8px;padding:8px 12px;margin-bottom:12px;font-size:0.8rem;color:#92400e;">
            <strong>Razón:</strong> {{ $item->razon_cambio }}
        </div>
        @endif
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <div>
                <div style="font-size:0.68rem;font-weight:800;text-transform:uppercase;letter-spacing:0.08em;color:#ef4444;margin-bottom:6px;">✕ Texto anterior</div>
                <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:10px;padding:12px;font-size:0.8rem;color:#7f1d1d;line-height:1.6;max-height:200px;overflow-y:auto;white-space:pre-wrap;">{{ $item->contenido_anterior }}</div>
            </div>
            <div>
                <div style="font-size:0.68rem;font-weight:800;text-transform:uppercase;letter-spacing:0.08em;color:#22c55e;margin-bottom:6px;">✓ Texto nuevo</div>
                <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:12px;font-size:0.8rem;color:#14532d;line-height:1.6;max-height:200px;overflow-y:auto;white-space:pre-wrap;">{{ $item->contenido_nuevo }}</div>
            </div>
        </div>
    </div>
    @endforeach
    <div style="text-align:center;font-size:0.75rem;color:#94a3b8;padding:8px 0;">
        {{ $historial->count() }} modificación(es) registrada(s)
    </div>
</div>

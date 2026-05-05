<div style="font-family:'Plus Jakarta Sans',sans-serif;padding:8px 0;">
@foreach($pagos as $p)
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:16px;margin-bottom:10px;">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;">
        <div>
            <div style="font-weight:800;font-size:14px;color:#0f172a;">{{ $p->numero }}</div>
            <div style="font-size:12px;color:#64748b;">{{ $p->fecha_pago?->format('d/m/Y') }} · {{ ucfirst(str_replace('_',' ',$p->forma_pago)) }}</div>
            @if($p->banco_origen)<div style="font-size:12px;color:#64748b;">🏦 {{ $p->banco_origen }}</div>@endif
            @if($p->referencia_pago)<div style="font-size:12px;color:#64748b;">Ref: {{ $p->referencia_pago }}</div>@endif
        </div>
        <div style="text-align:right;">
            <div style="font-size:18px;font-weight:900;color:#15803d;">${{ number_format($p->total_pagado,0,',','.') }}</div>
            <div style="font-size:11px;color:#94a3b8;">{{ $p->registradoPor?->name ?? 'Sistema' }}</div>
        </div>
    </div>
    @if($p->valor_mora > 0)
    <div style="margin-top:8px;display:flex;gap:12px;font-size:12px;">
        <span>Canon: ${{ number_format($p->valor_canon,0,',','.') }}</span>
        <span style="color:#dc2626;">Mora: ${{ number_format($p->valor_mora,0,',','.') }}</span>
    </div>
    @endif
    @if($p->comprobante_path)
    <div style="margin-top:8px;">
        <a href="{{ asset('storage/'.$p->comprobante_path) }}" target="_blank"
           style="font-size:12px;color:#2563eb;font-weight:700;">📄 Ver comprobante</a>
    </div>
    @endif
</div>
@endforeach
<div style="text-align:center;font-size:11px;color:#94a3b8;padding:6px 0;">
    Total pagado: ${{ number_format($pagos->sum('total_pagado'),0,',','.') }} COP
</div>
</div>

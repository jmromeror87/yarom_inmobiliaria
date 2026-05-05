<div style="font-family:'Plus Jakarta Sans',sans-serif;padding:8px 0;">
@foreach($clausulas as $c)
<div style="background:#fffbeb;border:1.5px solid #fcd34d;border-radius:12px;padding:16px;margin-bottom:12px;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
        <div style="font-weight:800;font-size:13px;color:#0f172a;">{{ $c->numero }}: {{ $c->titulo }}</div>
        <span style="background:#fef3c7;color:#d97706;font-size:10px;font-weight:800;padding:2px 10px;border-radius:99px;border:1px solid #fcd34d;">⚠️ MODIFICADA</span>
    </div>
    <div style="margin-bottom:8px;">
        <div style="font-size:10px;font-weight:700;text-transform:uppercase;color:#94a3b8;margin-bottom:4px;">Contenido actual:</div>
        <div style="font-size:12px;color:#374151;background:#fff;border-radius:8px;padding:10px;line-height:1.6;">{{ Str::limit($c->contenido_actual, 400) }}</div>
    </div>
    <div>
        <div style="font-size:10px;font-weight:700;text-transform:uppercase;color:#94a3b8;margin-bottom:4px;">Contenido original:</div>
        <div style="font-size:12px;color:#94a3b8;background:#f8fafc;border-radius:8px;padding:10px;line-height:1.6;">{{ Str::limit($c->contenido_original, 400) }}</div>
    </div>
</div>
@endforeach
<div style="text-align:center;font-size:11px;color:#94a3b8;padding:6px 0;">{{ $clausulas->count() }} cláusula(s) modificada(s)</div>
</div>

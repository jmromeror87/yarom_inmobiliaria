<x-filament-panels::page>
<style>
    .sign-wrap { max-width:680px; margin:0 auto; }
    .card { background:#fff; border:1px solid #e2e8f0; border-radius:16px; padding:20px 24px; margin-bottom:14px; }
    .stitle { font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:0.1em; color:#64748b; margin-bottom:12px; }
    .canvas-box { border:2px dashed #cbd5e1; border-radius:12px; background:#f8fafc; overflow:hidden; }
    canvas { display:block; touch-action:none; cursor:crosshair; width:100%; height:180px; }
    .btn-grad { background:linear-gradient(135deg,#E11D48,#2563EB); color:#fff; border:none; padding:14px; border-radius:10px; font-size:14px; font-weight:800; cursor:pointer; width:100%; margin-top:10px; }
    .btn-gray { background:#f1f5f9; color:#475569; border:none; padding:8px 16px; border-radius:8px; font-size:12px; font-weight:700; cursor:pointer; margin-top:6px; width:100%; }
    .firma-ok { background:#f0fdf4; border:1.5px solid #16a34a; border-radius:12px; padding:16px; text-align:center; }
    .irow { display:flex; justify-content:space-between; padding:7px 0; border-bottom:1px solid #f1f5f9; font-size:13px; }
    .irow:last-child { border-bottom:none; }
    .irow label { color:#94a3b8; font-weight:600; }
    .irow span { font-weight:700; color:#0f172a; }
</style>

<div class="sign-wrap">

    <div class="card">
        <div class="stitle">📋 {{ $this->record->numero }}</div>
        <div class="irow"><label>Inmueble</label><span>{{ $this->record->property?->codigo }} — {{ $this->record->property?->direccion }}</span></div>
        <div class="irow"><label>Arrendatario</label><span>{{ $this->record->arrendatario?->nombre_completo }}</span></div>
        <div class="irow"><label>Fecha</label><span>{{ $this->record->fecha_acta?->format('d/m/Y') }} {{ $this->record->hora_acta }}</span></div>
    </div>

    {{-- Firma Arrendatario --}}
    <div class="card" id="card-arrendatario">
        <div class="stitle">👤 Firma del Arrendatario — {{ $this->record->arrendatario?->nombre_completo }}</div>
        @if($this->record->firma_digital_arrendatario)
        <div class="firma-ok">
            <div style="font-size:28px;">✅</div>
            <div style="font-weight:800;color:#15803d;font-size:14px;margin:6px 0;">Firma registrada</div>
            <img src="{{ $this->record->firma_digital_arrendatario }}" style="max-height:80px;border:1px solid #bbf7d0;border-radius:8px;padding:4px;">
        </div>
        @else
        <p style="font-size:12px;color:#94a3b8;margin-bottom:8px;">Firme con el dedo o stylus:</p>
        <div class="canvas-box"><canvas id="canvas-arrendatario"></canvas></div>
        <button class="btn-gray" onclick="limpiar('arrendatario')">🗑️ Limpiar</button>
        <button class="btn-grad" id="btn-arrendatario" onclick="guardar('arrendatario')">✍️ Guardar firma arrendatario</button>
        @endif
    </div>

    {{-- Firma Asesor --}}
    <div class="card" id="card-asesor">
        <div class="stitle">🏢 Firma del Asesor</div>
        @if($this->record->firma_digital_asesor)
        <div class="firma-ok">
            <div style="font-size:28px;">✅</div>
            <div style="font-weight:800;color:#15803d;font-size:14px;margin:6px 0;">Firma registrada</div>
            <img src="{{ $this->record->firma_digital_asesor }}" style="max-height:80px;border:1px solid #bbf7d0;border-radius:8px;padding:4px;">
        </div>
        @else
        <p style="font-size:12px;color:#94a3b8;margin-bottom:8px;">Firme con el dedo o stylus:</p>
        <div class="canvas-box"><canvas id="canvas-asesor"></canvas></div>
        <button class="btn-gray" onclick="limpiar('asesor')">🗑️ Limpiar</button>
        <button class="btn-grad" id="btn-asesor" onclick="guardar('asesor')">✍️ Guardar firma asesor</button>
        @endif
    </div>

    @if($this->record->firma_digital_arrendatario && $this->record->firma_digital_asesor)
    <div style="background:#f0fdf4;border:1.5px solid #16a34a;border-radius:14px;padding:20px;text-align:center;">
        <div style="font-size:36px;">🎉</div>
        <div style="font-weight:900;font-size:16px;color:#15803d;margin:6px 0;">Ambas partes firmaron</div>
        <a href="{{ \App\Filament\Resources\PropertyHandovers\PropertyHandoverResource::getUrl('edit', ['record' => $this->record]) }}"
           style="display:inline-block;margin-top:10px;background:linear-gradient(135deg,#E11D48,#2563EB);color:#fff;padding:12px 28px;border-radius:10px;font-weight:800;text-decoration:none;font-size:14px;">
            Ir al acta →
        </a>
    </div>
    @endif

</div>

<script>
const canvases = {};
const ctxs = {};
const isDrawing = {};

function init(id) {
    const c = document.getElementById('canvas-' + id);
    if (!c) return;
    c.width  = c.offsetWidth  || c.parentElement.offsetWidth || 600;
    c.height = 180;
    const ctx = c.getContext('2d');
    ctx.strokeStyle = '#1e293b';
    ctx.lineWidth   = 2.5;
    ctx.lineCap     = 'round';
    ctx.lineJoin    = 'round';
    ctx.fillStyle   = '#f8fafc';
    ctx.fillRect(0, 0, c.width, c.height);
    canvases[id] = c;
    ctxs[id]     = ctx;
    isDrawing[id] = false;

    function pos(e) {
        const r   = c.getBoundingClientRect();
        const src = e.touches ? e.touches[0] : e;
        return {
            x: (src.clientX - r.left) * (c.width  / r.width),
            y: (src.clientY - r.top)  * (c.height / r.height),
        };
    }

    c.onmousedown  = e => { isDrawing[id]=true; const p=pos(e); ctx.beginPath(); ctx.moveTo(p.x,p.y); };
    c.onmousemove  = e => { if(!isDrawing[id])return; const p=pos(e); ctx.lineTo(p.x,p.y); ctx.stroke(); };
    c.onmouseup    = () => isDrawing[id]=false;
    c.onmouseleave = () => isDrawing[id]=false;
    c.addEventListener('touchstart', e => { e.preventDefault(); isDrawing[id]=true; const p=pos(e); ctx.beginPath(); ctx.moveTo(p.x,p.y); }, {passive:false});
    c.addEventListener('touchmove',  e => { e.preventDefault(); if(!isDrawing[id])return; const p=pos(e); ctx.lineTo(p.x,p.y); ctx.stroke(); }, {passive:false});
    c.addEventListener('touchend',   () => isDrawing[id]=false);
}

function limpiar(id) {
    const c=canvases[id], ctx=ctxs[id];
    if(c&&ctx){ ctx.clearRect(0,0,c.width,c.height); ctx.fillStyle='#f8fafc'; ctx.fillRect(0,0,c.width,c.height); }
}

async function guardar(id) {
    const c = canvases[id];
    if (!c) return;
    const btn = document.getElementById('btn-'+id);
    if (btn) { btn.disabled=true; btn.textContent='Guardando...'; }

    const data = c.toDataURL('image/png');
    const csrf = '{{ csrf_token() }}';

    try {
        const formData = new FormData();
        formData.append('firmante', id);
        formData.append('firma', data);
        formData.append('_token', csrf);

        const res = await fetch('{{ route("acta.firma", $this->record) }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: formData,
        });
        const json = await res.json();
        if (json.ok) {
            window.location.reload();
        } else {
            alert('Error al guardar la firma');
            if (btn) { btn.disabled=false; btn.textContent='✍️ Guardar firma '+id; }
        }
    } catch(e) {
        alert('Error de conexión: ' + e.message);
        if (btn) { btn.disabled=false; btn.textContent='✍️ Guardar firma '+id; }
    }
}

window.addEventListener('load', () => {
    setTimeout(() => { init('arrendatario'); init('asesor'); }, 200);
});
</script>
</x-filament-panels::page>

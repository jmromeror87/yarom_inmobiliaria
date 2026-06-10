<x-filament-panels::page>
<style>
    .gallery-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:16px; padding:8px 0; }
    .gallery-card { border-radius:16px; overflow:hidden; background:#fff; box-shadow:0 4px 20px rgba(0,0,0,0.08); transition:transform 0.2s; cursor:pointer; position:relative; }
    .gallery-card:hover { transform:translateY(-4px); }
    .gallery-img { width:100%; height:220px; object-fit:cover; display:block; }
    .gallery-info { padding:12px 14px; }
    .gallery-cat { font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:0.08em; color:#E11D48; margin-bottom:4px; }
    .gallery-titulo { font-size:13px; font-weight:600; color:#0f172a; }
    .portada-badge { position:absolute; top:10px; left:10px; background:linear-gradient(135deg,#E11D48,#2563EB); color:#fff; font-size:10px; font-weight:800; padding:3px 10px; border-radius:99px; z-index:2; }
    .cat-filter { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:16px; }
    .cat-btn { background:#f1f5f9; border:none; padding:6px 14px; border-radius:99px; font-size:12px; font-weight:700; cursor:pointer; color:#475569; }
    .cat-btn.active { background:linear-gradient(135deg,#E11D48,#2563EB); color:#fff; }
    .header-inm { background:linear-gradient(135deg,#0F172A 0%,#1e2d45 55%,#1a1f3a 100%); border-radius:1.25rem; padding:24px 32px; margin-bottom:20px; color:#fff; display:flex; justify-content:space-between; align-items:center; position:relative; overflow:hidden; box-shadow:0 4px 24px rgba(15,23,42,.15); }
    .header-inm::before { content:''; position:absolute; right:-40px; top:-40px; width:200px; height:200px; border-radius:50%; background:radial-gradient(circle,rgba(225,29,72,.12),transparent 70%); pointer-events:none; }
    .cat-btn.active { background:linear-gradient(135deg,#1e3a8a,#E11D48)!important; color:#fff!important; box-shadow:0 3px 10px rgba(225,29,72,.28); }
</style>

@php
    $images = $this->record->images;
    $imagenesJs = [];
    foreach ($images as $img) {
        $imagenesJs[] = [
            'url'       => asset('storage/' . $img->path),
            'titulo'    => $img->titulo ?? 'Sin descripción',
            'categoria' => $img->categoria,
        ];
    }
    $categorias = $images->pluck('categoria')->unique()->values();
    $catLabels = [
        'fachada'    => '🏠 Fachada',
        'sala'       => '🛋️ Sala',
        'cocina'     => '🍳 Cocina',
        'habitacion' => '🛏️ Habitación',
        'bano'       => '🚿 Baño',
        'zona_comun' => '🏊 Zona común',
        'vista'      => '🌅 Vista',
        'plano'      => '📐 Plano',
        'otro'       => '📷 Otro',
    ];
@endphp

<div class="header-inm">
    {{-- Ícono --}}
    <div style="width:60px;height:60px;border-radius:16px;background:linear-gradient(135deg,#1e3a8a,#E11D48);display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 8px 20px rgba(225,29,72,.28);margin-right:20px;z-index:1;">
        <svg width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="#fff" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
    </div>
    <div style="flex:1;z-index:1;">
        <div style="font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.1em;color:rgba(255,255,255,.45);margin-bottom:4px;">{{ $this->record->tipo?->nombre ?? 'Inmueble' }}</div>
        <div style="font-size:20px;font-weight:900;color:#fff;letter-spacing:-.02em;">{{ $this->record->codigo }}</div>
        <div style="font-size:13px;color:rgba(255,255,255,.55);margin-top:4px;font-weight:500;">{{ $this->record->direccion }}@if($this->record->municipio) · {{ $this->record->municipio->nombre }}@endif</div>
        @if($this->record->propietario)<div style="font-size:11px;color:rgba(255,255,255,.35);margin-top:3px;">{{ $this->record->propietario->nombre_completo }}</div>@endif
    </div>
    <div style="z-index:1;">
        <div style="background:rgba(225,29,72,.2);border:1px solid rgba(225,29,72,.4);color:#fca5a5;font-size:12px;font-weight:800;padding:5px 14px;border-radius:99px;text-align:center;">{{ $images->count() }} fotos</div>
    </div>
</div>

@if($images->isEmpty())
<div style="text-align:center;padding:80px 20px;color:#94a3b8;">
    <p style="font-weight:700;font-size:1rem;color:#475569;">Sin fotos aún</p>
    <p>Agrega fotos desde la sección Galería al editar el inmueble.</p>
</div>
@else

<div x-data="{
    imgs: {{ json_encode($imagenesJs) }},
    open: false,
    cur: 0,
    filtro: 'todas',
    abrir(i) { this.cur = i; this.open = true; },
    cerrar() { this.open = false; },
    prev() { this.cur = (this.cur - 1 + this.imgs.length) % this.imgs.length; },
    next() { this.cur = (this.cur + 1) % this.imgs.length; },
}" @keydown.escape.window="cerrar()" @keydown.arrow-left.window="prev()" @keydown.arrow-right.window="next()">

    {{-- Filtros --}}
    <div class="cat-filter">
        <button class="cat-btn" :class="filtro==='todas' ? 'active' : ''" @click="filtro='todas'">📷 Todas</button>
        @foreach($categorias as $cat)
        <button class="cat-btn" :class="filtro==='{{ $cat }}' ? 'active' : ''" @click="filtro='{{ $cat }}'">{{ $catLabels[$cat] ?? $cat }}</button>
        @endforeach
    </div>

    {{-- Grid --}}
    <div class="gallery-grid">
        @foreach($images as $index => $image)
        <div class="gallery-card"
             x-show="filtro === 'todas' || filtro === '{{ $image->categoria }}'"
             @click="abrir({{ $index }})">
            @if($image->es_portada)
            <div class="portada-badge">⭐ Portada</div>
            @endif
            <img class="gallery-img"
                 src="{{ asset('storage/' . $image->path) }}"
                 alt="{{ $image->titulo ?? '' }}"
                 loading="lazy">
            <div class="gallery-info">
                <div class="gallery-cat">{{ $catLabels[$image->categoria] ?? $image->categoria }}</div>
                <div class="gallery-titulo">{{ $image->titulo ?? 'Sin descripción' }}</div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Lightbox Alpine --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click.self="cerrar()"
         style="position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.92);z-index:9999;align-items:center;justify-content:center;flex-direction:column;">

        <div @click="cerrar()"
             style="position:absolute;top:20px;right:24px;color:#fff;font-size:36px;cursor:pointer;line-height:1;font-weight:300;">&times;</div>

        <img :src="imgs[cur].url"
             :alt="imgs[cur].titulo"
             style="max-width:90vw;max-height:78vh;border-radius:12px;object-fit:contain;">

        <div x-text="imgs[cur].titulo"
             style="color:#e2e8f0;font-size:14px;margin-top:12px;font-weight:600;"></div>

        <div style="display:flex;gap:16px;margin-top:14px;">
            <button @click="prev()"
                    style="background:rgba(255,255,255,0.15);border:none;color:#fff;padding:10px 28px;border-radius:99px;font-size:14px;font-weight:700;cursor:pointer;">
                ← Anterior
            </button>
            <button @click="next()"
                    style="background:rgba(255,255,255,0.15);border:none;color:#fff;padding:10px 28px;border-radius:99px;font-size:14px;font-weight:700;cursor:pointer;">
                Siguiente →
            </button>
        </div>

        <div x-text="(cur+1) + ' / ' + imgs.length"
             style="color:rgba(255,255,255,0.4);font-size:12px;margin-top:8px;"></div>
    </div>

</div>
@endif
</x-filament-panels::page>

<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Acta {{ $acta->numero }} — Serviarrendar</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
<style>
  body { font-family: 'Plus Jakarta Sans', sans-serif; background: #F0F4F8; }
  .btn-primary { background: #E11D48; color: #fff; border-radius: 14px; padding: 14px 24px; font-weight: 700; font-size: 15px; border: none; width: 100%; cursor: pointer; transition: all .2s; }
  .btn-primary:hover { background: #be123c; transform: translateY(-1px); }
  .btn-secondary { background: #1e3a8a; color: #fff; border-radius: 14px; padding: 14px 24px; font-weight: 700; font-size: 15px; border: none; width: 100%; cursor: pointer; }
  .card { background: #fff; border-radius: 20px; box-shadow: 0 4px 20px rgba(0,0,0,.07); padding: 20px; margin-bottom: 16px; }
  .input-field { width: 100%; border: 1.5px solid #E2E8F0; border-radius: 12px; padding: 12px 14px; font-size: 16px; font-family: inherit; outline: none; transition: border .2s; background: #FAFBFC; }
  .input-field:focus { border-color: #E11D48; box-shadow: 0 0 0 3px rgba(225,29,72,.1); }
  .estado-btn { border: 2px solid; border-radius: 12px; padding: 10px 14px; font-size: 13px; font-weight: 700; cursor: pointer; transition: all .2s; flex: 1; text-align: center; }
  .estado-btn.excelente { border-color: #16a34a; color: #16a34a; }
  .estado-btn.excelente.active { background: #16a34a; color: #fff; }
  .estado-btn.bueno { border-color: #2563eb; color: #2563eb; }
  .estado-btn.bueno.active { background: #2563eb; color: #fff; }
  .estado-btn.regular { border-color: #d97706; color: #d97706; }
  .estado-btn.regular.active { background: #d97706; color: #fff; }
  .estado-btn.malo { border-color: #dc2626; color: #dc2626; }
  .estado-btn.malo.active { background: #dc2626; color: #fff; }
  .tab-btn { padding: 10px 16px; border-radius: 12px; font-size: 13px; font-weight: 700; cursor: pointer; transition: all .2s; border: none; background: transparent; color: #64748b; white-space: nowrap; }
  .tab-btn.active { background: #E11D48; color: #fff; }
  .sig-canvas { border: 2px dashed #CBD5E1; border-radius: 12px; background: #FAFBFC; touch-action: none; }
  .toast { position: fixed; bottom: 24px; left: 50%; transform: translateX(-50%); background: #1e3a8a; color: #fff; padding: 12px 24px; border-radius: 20px; font-weight: 700; font-size: 14px; z-index: 9999; animation: fadeup .3s ease; white-space: nowrap; }
  .toast.error { background: #dc2626; }
  .toast.success { background: #16a34a; }
  @keyframes fadeup { from { opacity:0; transform: translateX(-50%) translateY(10px); } to { opacity:1; transform: translateX(-50%) translateY(0); } }
  .foto-thumb { width: 80px; height: 80px; object-fit: cover; border-radius: 10px; border: 2px solid #E2E8F0; }
  .section-title { font-size: 15px; font-weight: 800; color: #0F172A; margin-bottom: 12px; display: flex; align-items: center; gap: 8px; }
  .badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; }
  .badge-red { background: #fef2f2; color: #dc2626; }
  .badge-blue { background: #eff6ff; color: #1d4ed8; }
  .progress-bar { height: 4px; background: #E2E8F0; border-radius: 4px; overflow: hidden; }
  .progress-fill { height: 100%; background: linear-gradient(90deg, #E11D48, #1e3a8a); border-radius: 4px; transition: width .5s ease; }
</style>
</head>
<body x-data="actaApp()" x-init="init()">

{{-- ── HEADER ── --}}
<div style="background: linear-gradient(135deg, #1e3a8a, #0d1b4b); padding: 20px 20px 28px; position: sticky; top: 0; z-index: 100;">
  <div style="display:flex; align-items:center; gap:12px; margin-bottom:12px;">
    <div style="width:38px;height:38px;background:linear-gradient(135deg,#E11D48,#be123c);border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
      <svg viewBox="0 0 32 32" fill="none" width="20" height="20"><path d="M4 28V14l12-9 12 9v14H20v-7h-8v7H4z" fill="#fff"/></svg>
    </div>
    <div>
      <div style="font-size:16px;font-weight:900;color:#fff;letter-spacing:-.02em;">YAROM INMOBILIARIA</div>
      <div style="font-size:11px;color:rgba(255,255,255,.6);font-weight:600;">Serviarrendar S.A.S</div>
    </div>
  </div>
  <div style="background:rgba(255,255,255,.1);border-radius:12px;padding:12px 14px;">
    <div style="font-size:11px;color:rgba(255,255,255,.6);font-weight:700;text-transform:uppercase;letter-spacing:.08em;">Acta de {{ $acta->tipo === 'devolucion' ? 'Devolución' : 'Entrega' }}</div>
    <div style="font-size:18px;font-weight:900;color:#fff;margin-top:2px;">{{ $acta->numero }}</div>
    <div style="font-size:12px;color:rgba(255,255,255,.7);margin-top:4px;">{{ $acta->property?->direccion }}</div>
    <div style="font-size:12px;color:rgba(255,255,255,.6);margin-top:2px;">📅 {{ $acta->fecha_acta?->format('d/m/Y') }} @if($acta->hora_acta) · ⏰ {{ $acta->hora_acta }} @endif</div>
  </div>

  {{-- Barra de progreso --}}
  <div style="margin-top:12px;">
    <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
      <span style="font-size:11px;color:rgba(255,255,255,.6);font-weight:600;">Progreso del acta</span>
      <span style="font-size:11px;color:#fff;font-weight:700;" x-text="progreso + '%'"></span>
    </div>
    <div class="progress-bar">
      <div class="progress-fill" :style="'width:' + progreso + '%'"></div>
    </div>
  </div>
</div>

{{-- ── TABS DE NAVEGACIÓN ── --}}
<div style="background:#fff;padding:10px 16px;overflow-x:auto;display:flex;gap:6px;border-bottom:1px solid #F1F5F9;position:sticky;top:118px;z-index:99;">
  <button class="tab-btn" :class="tab === 'info' && 'active'" @click="tab='info'">ℹ️ Info</button>
  <button class="tab-btn" :class="tab === 'medidores' && 'active'" @click="tab='medidores'">📊 Medidores</button>
  <button class="tab-btn" :class="tab === 'llaves' && 'active'" @click="tab='llaves'">🔑 Llaves</button>
  <button class="tab-btn" :class="tab === 'inventario' && 'active'" @click="tab='inventario'">🏠 Inventario</button>
  <button class="tab-btn" :class="tab === 'firmas' && 'active'" @click="tab='firmas'">✍️ Firmas</button>
</div>

<div style="padding: 16px; max-width: 640px; margin: 0 auto; padding-bottom: 100px;">

  {{-- ══════════ TAB: INFO ══════════ --}}
  <div x-show="tab === 'info'">
    <div class="card">
      <div class="section-title">🏠 Datos del inmueble</div>
      <div style="display:grid;gap:8px;">
        <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #F1F5F9;">
          <span style="font-size:13px;color:#64748b;">Código</span>
          <span style="font-size:13px;font-weight:700;">{{ $acta->property?->codigo }}</span>
        </div>
        <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #F1F5F9;">
          <span style="font-size:13px;color:#64748b;">Tipo</span>
          <span style="font-size:13px;font-weight:700;">{{ $acta->property?->tipo?->nombre }}</span>
        </div>
        <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #F1F5F9;">
          <span style="font-size:13px;color:#64748b;">Dirección</span>
          <span style="font-size:13px;font-weight:700;text-align:right;max-width:200px;">{{ $acta->property?->direccion }}</span>
        </div>
        <div style="display:flex;justify-content:space-between;padding:8px 0;">
          <span style="font-size:13px;color:#64748b;">Estrato</span>
          <span style="font-size:13px;font-weight:700;">{{ $acta->property?->estrato }}</span>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="section-title">👤 Arrendatario</div>
      <div style="display:grid;gap:8px;">
        <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #F1F5F9;">
          <span style="font-size:13px;color:#64748b;">Nombre</span>
          <span style="font-size:13px;font-weight:700;text-align:right;max-width:200px;">{{ $acta->arrendatario?->nombre_completo }}</span>
        </div>
        <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #F1F5F9;">
          <span style="font-size:13px;color:#64748b;">Cédula</span>
          <span style="font-size:13px;font-weight:700;">{{ $acta->arrendatario?->numero_documento }}</span>
        </div>
        <div style="display:flex;justify-content:space-between;padding:8px 0;">
          <span style="font-size:13px;color:#64748b;">Celular</span>
          <a :href="'tel:' + '{{ $acta->arrendatario?->celular }}'" style="font-size:13px;font-weight:700;color:#E11D48;">{{ $acta->arrendatario?->celular }}</a>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="section-title">👔 Asesor responsable</div>
      <div style="font-size:14px;font-weight:700;color:#0F172A;">{{ $acta->asesor?->name }}</div>
      <div style="font-size:12px;color:#64748b;margin-top:2px;">{{ $acta->asesor?->email }}</div>
    </div>

    <button class="btn-primary" @click="tab='medidores'">
      Continuar → Medidores 📊
    </button>
  </div>

  {{-- ══════════ TAB: MEDIDORES ══════════ --}}
  <div x-show="tab === 'medidores'">
    <div class="card">
      <div class="section-title">📊 Lecturas de medidores</div>
      <p style="font-size:13px;color:#64748b;margin-bottom:16px;">Anote el número exacto que aparece en el medidor al momento de la entrega.</p>

      <div style="display:grid;gap:14px;">
        <div>
          <label style="font-size:13px;font-weight:700;color:#374151;display:block;margin-bottom:6px;">💧 Agua (m³)</label>
          <input type="number" inputmode="numeric" class="input-field" placeholder="000000"
            x-model="form.lectura_agua" @change="autoguardar()">
          <span style="font-size:11px;color:#94a3b8;margin-top:4px;display:block;">Ingrese solo números del medidor de acueducto</span>
        </div>

        <div>
          <label style="font-size:13px;font-weight:700;color:#374151;display:block;margin-bottom:6px;">⚡ Energía (kWh)</label>
          <input type="number" inputmode="numeric" class="input-field" placeholder="000000"
            x-model="form.lectura_energia" @change="autoguardar()">
          <span style="font-size:11px;color:#94a3b8;margin-top:4px;display:block;">Número del medidor eléctrico</span>
        </div>

        <div>
          <label style="font-size:13px;font-weight:700;color:#374151;display:block;margin-bottom:6px;">🔥 Gas (m³)</label>
          <input type="number" inputmode="numeric" class="input-field" placeholder="000000"
            x-model="form.lectura_gas" @change="autoguardar()">
          <span style="font-size:11px;color:#94a3b8;margin-top:4px;display:block;">Solo si el inmueble tiene gas natural</span>
        </div>
      </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
      <button class="btn-secondary" @click="tab='info'">← Atrás</button>
      <button class="btn-primary" @click="tab='llaves'">Llaves 🔑 →</button>
    </div>
  </div>

  {{-- ══════════ TAB: LLAVES ══════════ --}}
  <div x-show="tab === 'llaves'">
    <div class="card">
      <div class="section-title">🔑 Llaves entregadas</div>
      <p style="font-size:13px;color:#64748b;margin-bottom:16px;">Cuente físicamente las llaves y anote la cantidad. Use 0 si no aplica.</p>

      <div style="display:grid;gap:14px;">
        <template x-for="llave in llaves" :key="llave.key">
          <div style="display:flex;align-items:center;justify-content:space-between;padding:12px;background:#F8FAFC;border-radius:12px;border:1.5px solid #E2E8F0;">
            <div>
              <div style="font-size:14px;font-weight:700;" x-text="llave.label"></div>
              <div style="font-size:11px;color:#94a3b8;" x-text="llave.hint"></div>
            </div>
            <div style="display:flex;align-items:center;gap:10px;">
              <button @click="llave.value = Math.max(0, llave.value - 1); autoguardar()"
                style="width:36px;height:36px;border-radius:10px;border:2px solid #E2E8F0;background:#fff;font-size:20px;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;">−</button>
              <span style="font-size:22px;font-weight:900;min-width:32px;text-align:center;" x-text="llave.value"></span>
              <button @click="llave.value++; autoguardar()"
                style="width:36px;height:36px;border-radius:10px;border:2px solid #E11D48;background:#E11D48;font-size:20px;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;color:#fff;">+</button>
            </div>
          </div>
        </template>

        <div>
          <label style="font-size:13px;font-weight:700;color:#374151;display:block;margin-bottom:6px;">📝 Observaciones sobre llaves</label>
          <textarea class="input-field" rows="3" placeholder="Ej: Se entregan 2 llaves originales y 1 copia. La llave del garaje es magnética."
            x-model="form.notas_llaves" @change="autoguardar()" style="resize:none;"></textarea>
        </div>
      </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
      <button class="btn-secondary" @click="tab='medidores'">← Atrás</button>
      <button class="btn-primary" @click="tab='inventario'">Inventario 🏠 →</button>
    </div>
  </div>

  {{-- ══════════ TAB: INVENTARIO ══════════ --}}
  <div x-show="tab === 'inventario'">

    {{-- Estado general --}}
    <div class="card">
      <div class="section-title">🏠 Estado general del inmueble</div>
      <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <template x-for="est in estados" :key="est.key">
          <button class="estado-btn" :class="[est.key, form.estado_general === est.key && 'active']"
            @click="form.estado_general = est.key; autoguardar()"
            x-text="est.label" style="min-width:70px;"></button>
        </template>
      </div>
    </div>

    {{-- Ítems por ambiente --}}
    <template x-for="(item, index) in items" :key="item.id">
      <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px;">
          <div>
            <div style="font-size:14px;font-weight:800;color:#0F172A;" x-text="ambienteLabel(item.ambiente)"></div>
            <div style="font-size:12px;color:#64748b;margin-top:2px;" x-text="item.elemento"></div>
          </div>
          <span class="badge" :class="estadoBadge(item.estado)" x-text="estadoLabel(item.estado)"></span>
        </div>

        {{-- Botones de estado --}}
        <div style="display:flex;gap:6px;margin-bottom:12px;flex-wrap:wrap;">
          <template x-for="est in estados" :key="est.key">
            <button class="estado-btn" :class="[est.key, item.estado === est.key && 'active']"
              @click="item.estado = est.key; actualizarItem(item)"
              x-text="est.label" style="font-size:12px;padding:7px 10px;"></button>
          </template>
        </div>

        {{-- Observaciones del ambiente --}}
        <textarea class="input-field" rows="2" placeholder="Observaciones de este ambiente (daños, novedades...)"
          x-model="item.descripcion" @change="actualizarItem(item)"
          style="resize:none;font-size:14px;margin-bottom:10px;"></textarea>

        {{-- Foto de evidencia --}}
        <div style="display:flex;align-items:center;gap:10px;">
          <template x-if="item.foto_path">
            <img :src="item.foto_path" class="foto-thumb" @click="verFoto(item.foto_path)">
          </template>
          <label :for="'foto_' + item.id"
            style="display:flex;align-items:center;gap:8px;padding:10px 16px;background:#F0F4F8;border-radius:12px;cursor:pointer;font-size:13px;font-weight:700;color:#374151;border:2px dashed #CBD5E1;">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20" style="flex-shrink:0;">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <span x-text="item.foto_path ? '🔄 Cambiar foto' : '📷 Tomar foto'"></span>
          </label>
          <input type="file" :id="'foto_' + item.id" accept="image/*" capture="environment"
            class="hidden" @change="subirFoto($event, item)">
          <span x-show="item.subiendo" style="font-size:12px;color:#64748b;">Subiendo...</span>
        </div>
      </div>
    </template>

    {{-- Observaciones generales --}}
    <div class="card">
      <div class="section-title">📝 Observaciones generales</div>
      <textarea class="input-field" rows="4"
        placeholder="Anote aquí cualquier observación importante sobre el estado general del inmueble, daños existentes, acuerdos especiales, etc."
        x-model="form.observaciones_generales" @change="autoguardar()" style="resize:none;"></textarea>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
      <button class="btn-secondary" @click="tab='llaves'">← Atrás</button>
      <button class="btn-primary" @click="tab='firmas'">Firmas ✍️ →</button>
    </div>
  </div>

  {{-- ══════════ TAB: FIRMAS ══════════ --}}
  <div x-show="tab === 'firmas'">

    {{-- Firma Arrendatario --}}
    <div class="card">
      <div class="section-title">✍️ Firma del Arrendatario</div>
      <p style="font-size:13px;color:#64748b;margin-bottom:12px;">
        <strong>{{ $acta->arrendatario?->nombre_completo }}</strong><br>
        CC: {{ $acta->arrendatario?->numero_documento }}
      </p>
      <canvas id="sig-arrendatario" class="sig-canvas" width="340" height="160" style="width:100%;"></canvas>
      <div style="display:flex;gap:8px;margin-top:10px;">
        <button @click="limpiarFirma('arrendatario')"
          style="flex:1;padding:10px;border-radius:10px;border:1.5px solid #E2E8F0;background:#fff;font-size:13px;font-weight:700;cursor:pointer;">
          🗑️ Limpiar
        </button>
        <button @click="guardarFirma('arrendatario')"
          style="flex:2;padding:10px;border-radius:10px;background:#1e3a8a;color:#fff;font-size:13px;font-weight:700;border:none;cursor:pointer;">
          ✅ Confirmar firma
        </button>
      </div>
      <div x-show="firmaArrendatarioOk" style="margin-top:10px;padding:8px 12px;background:#f0fdf4;border-radius:10px;color:#16a34a;font-size:13px;font-weight:700;">
        ✅ Firma del arrendatario registrada
      </div>
    </div>

    {{-- Firma Asesor --}}
    <div class="card">
      <div class="section-title">✍️ Firma del Asesor</div>
      <p style="font-size:13px;color:#64748b;margin-bottom:12px;">
        <strong>{{ $acta->asesor?->name }}</strong><br>
        Representante Serviarrendar S.A.S
      </p>
      <canvas id="sig-asesor" class="sig-canvas" width="340" height="160" style="width:100%;"></canvas>
      <div style="display:flex;gap:8px;margin-top:10px;">
        <button @click="limpiarFirma('asesor')"
          style="flex:1;padding:10px;border-radius:10px;border:1.5px solid #E2E8F0;background:#fff;font-size:13px;font-weight:700;cursor:pointer;">
          🗑️ Limpiar
        </button>
        <button @click="guardarFirma('asesor')"
          style="flex:2;padding:10px;border-radius:10px;background:#1e3a8a;color:#fff;font-size:13px;font-weight:700;border:none;cursor:pointer;">
          ✅ Confirmar firma
        </button>
      </div>
      <div x-show="firmaAsesorOk" style="margin-top:10px;padding:8px 12px;background:#f0fdf4;border-radius:10px;color:#16a34a;font-size:13px;font-weight:700;">
        ✅ Firma del asesor registrada
      </div>
    </div>

    {{-- Estado firma --}}
    <div class="card" x-show="firmaArrendatarioOk && firmaAsesorOk"
      style="background:linear-gradient(135deg,#f0fdf4,#dcfce7);border:2px solid #16a34a;">
      <div style="text-align:center;">
        <div style="font-size:32px;margin-bottom:8px;">🎉</div>
        <div style="font-size:16px;font-weight:900;color:#16a34a;">¡Acta firmada por ambas partes!</div>
        <div style="font-size:13px;color:#4ade80;margin-top:4px;">El acta quedó registrada en el sistema.</div>
      </div>
    </div>

    {{-- Botón guardar todo --}}
    <button class="btn-primary" @click="guardarTodo()" :disabled="guardando"
      style="margin-top:8px;" :style="guardando && 'opacity:.7'">
      <span x-show="!guardando">💾 Guardar acta completa</span>
      <span x-show="guardando">⏳ Guardando...</span>
    </button>

    <button class="btn-secondary" @click="tab='inventario'" style="margin-top:10px;">← Atrás</button>
  </div>
</div>

{{-- ── TOAST ── --}}
<div x-show="toast.visible" x-text="toast.msg" class="toast" :class="toast.type" x-transition></div>

{{-- ── MODAL FOTO ── --}}
<div x-show="fotoModal" @click="fotoModal=null"
  style="position:fixed;inset:0;background:rgba(0,0,0,.9);z-index:9998;display:flex;align-items:center;justify-content:center;padding:20px;">
  <img :src="fotoModal" style="max-width:100%;max-height:90vh;border-radius:12px;">
</div>

<script>
function actaApp() {
  return {
    tab: 'info',
    guardando: false,
    fotoModal: null,
    firmaArrendatarioOk: {{ $acta->firma_digital_arrendatario ? 'true' : 'false' }},
    firmaAsesorOk: {{ $acta->firma_digital_asesor ? 'true' : 'false' }},
    sigArrendatario: null,
    sigAsesor: null,
    toast: { visible: false, msg: '', type: '' },

    form: {
      lectura_agua:            '{{ $acta->lectura_agua }}',
      lectura_energia:         '{{ $acta->lectura_energia }}',
      lectura_gas:             '{{ $acta->lectura_gas }}',
      notas_llaves:            @json($acta->notas_llaves ?? ''),
      estado_general:          '{{ $acta->estado_general ?? 'bueno' }}',
      observaciones_generales: @json($acta->observaciones_generales ?? ''),
    },

    llaves: [
      { key: 'llaves_entregadas',     label: '🔑 Llaves del inmueble',    hint: 'Puerta principal',             value: {{ $acta->llaves_entregadas ?? 1 }} },
      { key: 'llaves_control_acceso', label: '📟 Control de acceso',       hint: 'Portero / citófono',           value: {{ $acta->llaves_control_acceso ?? 0 }} },
      { key: 'llaves_parqueadero',    label: '🚗 Llaves parqueadero',      hint: '0 si no tiene',               value: {{ $acta->llaves_parqueadero ?? 0 }} },
      { key: 'llaves_deposito',       label: '📦 Llaves depósito',         hint: '0 si no tiene',               value: {{ $acta->llaves_deposito ?? 0 }} },
    ],

    items: @json($itemsJson),

    estados: [
      { key: 'excelente', label: '🟢 Excelente' },
      { key: 'bueno',     label: '🔵 Bueno' },
      { key: 'regular',   label: '🟡 Regular' },
      { key: 'malo',      label: '🔴 Malo' },
    ],

    get progreso() {
      let pts = 0;
      if (this.form.lectura_agua || this.form.lectura_energia) pts += 20;
      if (this.llaves[0].value > 0) pts += 15;
      const itemsConEstado = this.items.filter(i => i.estado !== 'bueno' || i.descripcion).length;
      if (this.items.length > 0) pts += Math.min(30, Math.round(itemsConEstado / this.items.length * 30));
      if (this.firmaArrendatarioOk) pts += 17;
      if (this.firmaAsesorOk) pts += 18;
      return Math.min(100, pts);
    },

    init() {
      this.$nextTick(() => {
        const canvasA = document.getElementById('sig-arrendatario');
        const canvasAs = document.getElementById('sig-asesor');
        if (canvasA) this.sigArrendatario = new SignaturePad(canvasA, { penColor: '#1e3a8a', backgroundColor: 'rgba(0,0,0,0)' });
        if (canvasAs) this.sigAsesor = new SignaturePad(canvasAs, { penColor: '#1e3a8a', backgroundColor: 'rgba(0,0,0,0)' });
        this.resizePads();
        window.addEventListener('resize', () => this.resizePads());
      });
    },

    resizePads() {
      ['sig-arrendatario','sig-asesor'].forEach(id => {
        const canvas = document.getElementById(id);
        if (!canvas) return;
        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        canvas.width  = canvas.offsetWidth * ratio;
        canvas.height = canvas.offsetHeight * ratio;
        canvas.getContext('2d').scale(ratio, ratio);
      });
    },

    ambienteLabel(amb) {
      const map = {
        'sala':'🛋️ Sala','comedor':'🪑 Comedor','cocina':'🍳 Cocina',
        'habitacion_principal':'🛏️ Habitación Principal','habitacion_2':'🛏️ Habitación 2','habitacion_3':'🛏️ Habitación 3',
        'bano_principal':'🚿 Baño Principal','bano_secundario':'🚿 Baño Secundario','bano_social':'🚿 Baño Social',
        'garaje':'🚗 Garaje','deposito':'📦 Depósito','patio':'🌿 Patio','balcon':'🏠 Balcón',
        'zona_lavanderia':'👕 Lavandería','estudio':'💻 Estudio','otro':'📍 Otro',
      };
      return map[amb] || amb;
    },

    estadoLabel(e) {
      return { excelente:'Excelente', bueno:'Bueno', regular:'Regular', malo:'Malo', no_aplica:'N/A' }[e] || e;
    },

    estadoBadge(e) {
      return { excelente:'badge badge-blue', bueno:'badge badge-blue', regular:'badge', malo:'badge badge-red', no_aplica:'badge' }[e] || 'badge';
    },

    async autoguardar() {
      const body = {
        ...this.form,
        ...Object.fromEntries(this.llaves.map(l => [l.key, l.value])),
      };
      await this.post('{{ route('acta.publica.guardar', $token) }}', body);
    },

    async actualizarItem(item) {
      const body = { items: [{ id: item.id, estado: item.estado, descripcion: item.descripcion }] };
      await this.post('{{ route('acta.publica.guardar', $token) }}', body);
    },

    async guardarFirma(rol) {
      const pad = rol === 'asesor' ? this.sigAsesor : this.sigArrendatario;
      if (!pad || pad.isEmpty()) {
        this.showToast('Por favor firme antes de confirmar', 'error');
        return;
      }
      const firma = pad.toDataURL('image/png');
      const res = await this.post('{{ route('acta.publica.firma', $token) }}', { rol, firma });
      if (res?.ok) {
        if (rol === 'asesor') this.firmaAsesorOk = true;
        else this.firmaArrendatarioOk = true;
        this.showToast('✅ Firma registrada correctamente', 'success');
      }
    },

    limpiarFirma(rol) {
      const pad = rol === 'asesor' ? this.sigAsesor : this.sigArrendatario;
      if (pad) pad.clear();
      if (rol === 'asesor') this.firmaAsesorOk = false;
      else this.firmaArrendatarioOk = false;
    },

    async guardarTodo() {
      this.guardando = true;
      await this.autoguardar();
      this.guardando = false;
      this.showToast('💾 Acta guardada correctamente', 'success');
    },

    async subirFoto(event, item) {
      const file = event.target.files[0];
      if (!file) return;
      item.subiendo = true;
      const fd = new FormData();
      fd.append('foto', file);
      fd.append('item_id', item.id);
      fd.append('_token', document.querySelector('meta[name=csrf-token]').content);
      try {
        const res = await fetch('{{ route('acta.publica.foto', $token) }}', { method: 'POST', body: fd });
        const data = await res.json();
        if (data.ok) {
          item.foto_path = data.path;
          this.showToast('📷 Foto guardada', 'success');
        }
      } catch(e) {
        this.showToast('Error subiendo foto', 'error');
      }
      item.subiendo = false;
    },

    verFoto(path) { this.fotoModal = path; },

    async post(url, body) {
      try {
        const res = await fetch(url, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
            'Accept': 'application/json',
          },
          body: JSON.stringify(body),
        });
        return await res.json();
      } catch(e) {
        this.showToast('Error de conexión — intente de nuevo', 'error');
        return null;
      }
    },

    showToast(msg, type = '') {
      this.toast = { visible: true, msg, type };
      setTimeout(() => this.toast.visible = false, 3000);
    },
  }
}
</script>

@push('scripts')
<script>
// Polyfill Storage.url para las fotos ya cargadas
</script>
@endpush
</body>
</html>

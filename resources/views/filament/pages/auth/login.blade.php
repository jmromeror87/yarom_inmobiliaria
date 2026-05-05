<div>
<x-filament-panels::page.simple>
<style>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap');

body { font-family:'Plus Jakarta Sans',sans-serif!important; margin:0!important; padding:0!important; overflow:hidden; }

.fi-simple-layout,
.fi-simple-main-ctn,
.fi-simple-header,
.fi-simple-main,
.fi-simple-page,
.fi-simple-page-content { all:unset!important; display:block!important; }

.yr-wrap { position:fixed; top:0; left:0; width:100vw; height:100vh; display:flex; z-index:9999; font-family:'Plus Jakarta Sans',sans-serif; }

/* ── Panel izquierdo — formulario ── */
.yr-left {
    flex:0 0 460px;
    background:#fff;
    padding:52px 52px;
    display:flex;
    flex-direction:column;
    justify-content:center;
    box-shadow:20px 0 60px rgba(0,0,0,0.06);
    overflow-y:auto;
}

/* ── Panel derecho — visual ── */
.yr-right {
    flex:1;
    background:
        linear-gradient(160deg, rgba(15,23,42,0.82) 0%, rgba(225,29,72,0.45) 100%),
        url('/storage/imagen/login2.jpg');
    background-size:cover;
    background-position:center;
    display:flex;
    flex-direction:column;
    justify-content:flex-end;
    padding:60px 70px;
    color:#fff;
    position:relative;
    overflow:hidden;
}

/* ── Botón Filament ── */
.fi-btn {
    background:linear-gradient(135deg,#E11D48,#2563EB)!important;
    border:none!important;
    border-radius:12px!important;
    font-weight:800!important;
    letter-spacing:0.05em!important;
    text-transform:uppercase!important;
    color:#fff!important;
    height:48px!important;
    transition:all 0.2s ease!important;
}
.fi-btn:hover { opacity:0.88!important; transform:translateY(-1px)!important; }

/* ── Inputs ── */
.fi-input-wrp { border-radius:12px!important; }
.fi-input:focus { border-color:#E11D48!important; box-shadow:0 0 0 3px rgba(225,29,72,0.12)!important; }

/* ── Módulos cards ── */
.yr-module {
    border-left:3px solid;
    padding:10px 16px;
    border-radius:0 10px 10px 0;
    background:rgba(255,255,255,0.06);
    backdrop-filter:blur(4px);
}

@media(max-width:1024px) {
    .yr-right { display:none; }
    .yr-left { flex:1; }
}
</style>

<div class="yr-wrap">

    {{-- ── PANEL IZQUIERDO ── --}}
    <div class="yr-left">

        {{-- Logo --}}
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:36px;">
            <div style="width:46px;height:46px;background:linear-gradient(135deg,#E11D48,#2563EB);border-radius:13px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg viewBox="0 0 32 32" fill="none" width="24" height="24">
                    <path d="M4 28V14l12-9 12 9v14H20v-7h-8v7H4z" fill="#fff"/>
                    <rect x="13" y="21" width="6" height="7" rx="1" fill="#fff" opacity="0.5"/>
                </svg>
            </div>
            <div>
                <div style="font-size:1.3rem;font-weight:900;letter-spacing:-.03em;color:#0F172A;line-height:1;">
                    YAROM <span style="color:#E11D48;">INMO</span><span style="color:#2563EB;">BILIARIA</span>
                </div>
                <div style="font-size:0.68rem;font-weight:700;letter-spacing:0.12em;color:#94a3b8;text-transform:uppercase;margin-top:2px;">
                    Serviarrendar S.A.S
                </div>
            </div>
        </div>

        {{-- Título --}}
        <div style="margin-bottom:28px;">
            <h1 style="font-size:1.8rem;font-weight:900;color:#0F172A;letter-spacing:-.03em;margin:0 0 6px;">
                Acceso al sistema
            </h1>
            <p style="font-size:0.85rem;color:#64748b;margin:0;">
                Gestión integral de arriendos y administración inmobiliaria.
            </p>
        </div>

        {{-- Formulario Filament --}}
        {{ $this->content }}

        {{-- Footer --}}
        <div style="margin-top:36px;padding-top:20px;border-top:1px solid #f1f5f9;font-size:0.7rem;color:#94a3b8;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;">
            © {{ date('Y') }} YarOM ERP •
            <span style="color:#E11D48;">Serviarrendar S.A.S</span>
        </div>
    </div>

    {{-- ── PANEL DERECHO ── --}}
    <div class="yr-right">

        {{-- Badge sistema activo --}}
        <div style="position:absolute;top:40px;left:60px;display:flex;align-items:center;gap:8px;background:rgba(255,255,255,0.1);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,0.2);padding:6px 16px;border-radius:100px;">
            <span style="width:7px;height:7px;background:#22c55e;border-radius:50%;display:inline-block;"></span>
            <span style="font-size:0.68rem;font-weight:800;text-transform:uppercase;letter-spacing:0.12em;">Sistema Operativo Activo</span>
        </div>

        {{-- Título principal --}}
        <div style="margin-bottom:40px;">
            <h2 style="font-size:3.2rem;font-weight:900;line-height:0.95;letter-spacing:-.04em;margin:0 0 20px;">
                Potencia Digital<br>para <span style="color:#E11D48;">Ocaña.</span>
            </h2>
            <p style="font-size:1rem;opacity:0.8;max-width:480px;line-height:1.6;margin:0;">
                Liderando la transformación del sector inmobiliario en Norte de Santander con tecnología de clase mundial.
            </p>
        </div>

        {{-- Módulos del sistema --}}
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:48px;">

            <div class="yr-module" style="border-color:#E11D48;">
                <div style="font-weight:800;font-size:0.85rem;margin-bottom:3px;">Gestión de Arriendos</div>
                <div style="font-size:0.75rem;opacity:0.7;">Contratos, recaudos y liquidaciones automáticas.</div>
            </div>

            <div class="yr-module" style="border-color:#2563EB;">
                <div style="font-weight:800;font-size:0.85rem;margin-bottom:3px;">Captación de Inmuebles</div>
                <div style="font-size:0.75rem;opacity:0.7;">Ficha técnica, documentos y publicación.</div>
            </div>

            <div class="yr-module" style="border-color:#10b981;">
                <div style="font-weight:800;font-size:0.85rem;margin-bottom:3px;">Evaluación Arrendatario</div>
                <div style="font-size:0.75rem;opacity:0.7;">Scoring, centrales de riesgo y garantías.</div>
            </div>

            <div class="yr-module" style="border-color:#f59e0b;">
                <div style="font-weight:800;font-size:0.85rem;margin-bottom:3px;">Facturación DIAN</div>
                <div style="font-size:0.75rem;opacity:0.7;">Factura electrónica y retenciones automáticas.</div>
            </div>

            <div class="yr-module" style="border-color:#8b5cf6;">
                <div style="font-weight:800;font-size:0.85rem;margin-bottom:3px;">Cartera y Mora</div>
                <div style="font-size:0.75rem;opacity:0.7;">Aging, acuerdos de pago y proceso legal.</div>
            </div>

            <div class="yr-module" style="border-color:#06b6d4;">
                <div style="font-weight:800;font-size:0.85rem;margin-bottom:3px;">CRM Inmobiliario</div>
                <div style="font-size:0.75rem;opacity:0.7;">Propietarios, clientes y prospectos unificados.</div>
            </div>

        </div>

        {{-- Firma --}}
        <div style="display:flex;align-items:center;justify-content:space-between;padding-top:20px;border-top:1px solid rgba(255,255,255,0.12);">
            <div style="font-size:0.7rem;opacity:0.5;text-transform:uppercase;letter-spacing:0.08em;font-weight:700;">
                © {{ date('Y') }} YaRom ERP
            </div>
            <div style="font-size:0.7rem;opacity:0.5;text-transform:uppercase;letter-spacing:0.08em;font-weight:700;">
                Ing. Jhoan Romero · Architect
            </div>
        </div>

    </div>

</div>
</x-filament-panels::page.simple>
</div>
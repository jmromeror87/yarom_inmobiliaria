<x-filament-panels::page>
<style>
.ic-wrap { display:grid; grid-template-columns:280px 1fr; gap:0; height:calc(100vh - 200px); min-height:560px; border:1px solid #e2e8f0; border-radius:18px; overflow:hidden; box-shadow:0 4px 24px rgba(15,23,42,.08); }
.ic-sidebar { background:linear-gradient(160deg,#0f172a 0%,#1e3a8a 55%,#312e81 100%); display:flex; flex-direction:column; overflow:hidden; }
.ic-main { display:flex; flex-direction:column; background:#f8fafc; }
.ic-msgs { flex:1; overflow-y:auto; padding:28px; display:flex; flex-direction:column; gap:14px; scroll-behavior:smooth; }
.ic-msg-user { display:flex; justify-content:flex-end; }
.ic-msg-bot  { display:flex; gap:10px; align-items:flex-start; }
.ic-bubble-u { background:linear-gradient(135deg,#1e3a8a,#2563eb); color:#fff; border-radius:18px 18px 4px 18px; padding:12px 16px; max-width:70%; font-size:14px; line-height:1.55; box-shadow:0 2px 10px rgba(30,58,138,.25); word-break:break-word; }
.ic-bubble-b { background:#fff; border:1px solid #e2e8f0; border-radius:4px 18px 18px 18px; padding:13px 16px; max-width:78%; font-size:14px; line-height:1.65; color:#1e293b; box-shadow:0 1px 6px rgba(15,23,42,.06); white-space:pre-wrap; word-break:break-word; }
.ic-avatar { width:32px; height:32px; border-radius:50%; background:linear-gradient(135deg,#0f172a,#1e3a8a); display:flex; align-items:center; justify-content:center; flex-shrink:0; box-shadow:0 2px 8px rgba(15,23,42,.2); }
.ic-dot { width:8px;height:8px;border-radius:50%;background:#94a3b8;display:inline-block; }
.ic-dot:nth-child(1){animation:icb 1.2s infinite 0s}
.ic-dot:nth-child(2){animation:icb 1.2s infinite .2s}
.ic-dot:nth-child(3){animation:icb 1.2s infinite .4s}
@keyframes icb{0%,60%,100%{transform:translateY(0)}30%{transform:translateY(-5px)}}
.ic-chip { display:inline-flex;align-items:center;gap:4px;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.12);color:rgba(255,255,255,.7);font-size:10px;font-weight:700;padding:3px 9px;border-radius:99px;margin-top:6px;margin-right:4px; }
.ic-suggest-sidebar { background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.1);border-radius:10px;padding:10px 12px;font-size:12px;font-weight:600;color:rgba(255,255,255,.8);cursor:pointer;text-align:left;width:100%;transition:all .12s; }
.ic-suggest-sidebar:hover { background:rgba(255,255,255,.14);color:#fff; }
.ic-tool-chip { display:inline-flex;align-items:center;gap:3px;background:#eff6ff;border:1px solid #bfdbfe;color:#2563eb;font-size:10px;font-weight:700;padding:2px 8px;border-radius:99px;margin-top:5px;margin-right:4px; }
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.5}}
</style>

<div class="ic-wrap">

    {{-- ── SIDEBAR ───────────────────────────────────────── --}}
    <div class="ic-sidebar">

        {{-- Perfil Inmo --}}
        <div style="padding:28px 24px 20px;border-bottom:1px solid rgba(255,255,255,.08);">
            <div style="display:flex;align-items:center;gap:14px;margin-bottom:16px;">
                <div style="width:52px;height:52px;background:rgba(255,255,255,.12);border:2px solid rgba(255,255,255,.2);border-radius:16px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="26" height="26" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="1.3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456z"/>
                    </svg>
                </div>
                <div>
                    <div style="font-size:20px;font-weight:900;color:#fff;letter-spacing:-.02em;line-height:1;">Inmo</div>
                    <div style="font-size:11px;color:rgba(255,255,255,.45);margin-top:3px;">Agente Inmobiliario IA</div>
                    <div style="display:flex;align-items:center;gap:5px;margin-top:6px;">
                        <div style="width:7px;height:7px;border-radius:50%;background:#86efac;box-shadow:0 0 8px #86efac;animation:pulse 2s infinite;"></div>
                        <span style="font-size:11px;color:#86efac;font-weight:700;">En línea — GPT-4o mini</span>
                    </div>
                </div>
            </div>
            <p style="font-size:12px;color:rgba(255,255,255,.4);margin:0;line-height:1.6;">
                Consulto datos reales del sistema, envío notificaciones y ejecuto acciones en Serviarrendar S.A.S.
            </p>
        </div>

        {{-- Capacidades --}}
        <div style="padding:18px 24px 14px;border-bottom:1px solid rgba(255,255,255,.08);">
            <div style="font-size:10px;font-weight:800;color:rgba(255,255,255,.3);text-transform:uppercase;letter-spacing:.1em;margin-bottom:12px;">Especialidades</div>
            @foreach([
                ['Contratos y otrosíes','#60a5fa'],
                ['Cobros y cartera','#4ade80'],
                ['Inmuebles','#fbbf24'],
                ['Servicios y mantenimientos','#c084fc'],
                ['Notificaciones WhatsApp','#34d399'],
                ['Liquidaciones propietarios','#f472b6'],
            ] as [$label,$color])
            <div style="display:flex;align-items:center;gap:8px;padding:5px 0;">
                <div style="width:6px;height:6px;border-radius:50%;background:{{ $color }};flex-shrink:0;"></div>
                <span style="font-size:12px;color:rgba(255,255,255,.65);font-weight:500;">{{ $label }}</span>
            </div>
            @endforeach
        </div>

        {{-- Consultas rápidas --}}
        <div style="padding:18px 24px;flex:1;overflow-y:auto;">
            <div style="font-size:10px;font-weight:800;color:rgba(255,255,255,.3);text-transform:uppercase;letter-spacing:.1em;margin-bottom:12px;">Consultas rápidas</div>
            <div style="display:flex;flex-direction:column;gap:6px;">
                @foreach($sugerencias as $s)
                <button wire:click="usarSugerencia('{{ $s }}')" class="ic-suggest-sidebar">{{ $s }}</button>
                @endforeach
            </div>
        </div>

        {{-- Footer sidebar --}}
        <div style="padding:14px 24px;border-top:1px solid rgba(255,255,255,.08);display:flex;justify-content:space-between;align-items:center;">
            <span style="font-size:10px;color:rgba(255,255,255,.25);font-weight:600;">YarOM ERP · Serviarrendar</span>
            @if(!empty($mensajes))
            <button wire:click="limpiarChat"
                    style="background:rgba(225,29,72,.2);border:1px solid rgba(225,29,72,.3);border-radius:8px;padding:4px 10px;font-size:11px;font-weight:700;color:#fca5a5;cursor:pointer;">
                Limpiar
            </button>
            @endif
        </div>
    </div>

    {{-- ── CHAT MAIN ─────────────────────────────────────── --}}
    <div class="ic-main">

        {{-- Topbar --}}
        <div style="background:#fff;border-bottom:1px solid #e2e8f0;padding:14px 24px;display:flex;align-items:center;justify-content:space-between;flex-shrink:0;">
            <div style="display:flex;align-items:center;gap:10px;">
                <div style="width:8px;height:8px;border-radius:50%;background:#86efac;box-shadow:0 0 6px #86efac;"></div>
                <span style="font-size:14px;font-weight:700;color:#1e293b;">
                    {{ empty($mensajes) ? 'Nueva conversación' : 'Conversación activa' }}
                </span>
                @if(!empty($mensajes))
                <span style="background:#f1f5f9;border:1px solid #e2e8f0;border-radius:99px;padding:2px 10px;font-size:11px;color:#64748b;font-weight:600;">
                    {{ count(array_filter($mensajes, fn($m) => $m['rol']==='user')) }} {{ count(array_filter($mensajes, fn($m) => $m['rol']==='user')) === 1 ? 'pregunta' : 'preguntas' }}
                </span>
                @endif
            </div>
            <div style="display:flex;align-items:center;gap:8px;">
                <span style="font-size:11px;color:#94a3b8;">Ley 820 de 2003 · Colombia</span>
                <div style="width:1px;height:14px;background:#e2e8f0;"></div>
                <span style="font-size:11px;color:#94a3b8;">{{ now()->format('d/m/Y H:i') }}</span>
            </div>
        </div>

        {{-- Mensajes --}}
        <div class="ic-msgs" id="inmo-page-msgs">

            @if(empty($mensajes))
            <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;height:100%;text-align:center;padding:40px;">
                <div style="width:80px;height:80px;background:linear-gradient(135deg,#0f172a,#1e3a8a);border-radius:24px;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;box-shadow:0 8px 24px rgba(15,23,42,.2);">
                    <svg width="40" height="40" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="1.2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/>
                    </svg>
                </div>
                <p style="font-size:22px;font-weight:900;color:#1e293b;margin:0 0 8px;letter-spacing:-.02em;">Hola, soy Inmo</p>
                <p style="font-size:14px;color:#64748b;margin:0 0 28px;max-width:380px;line-height:1.65;">
                    Tu agente inmobiliario inteligente. Tengo acceso en tiempo real a contratos, cobros, inmuebles y más de Serviarrendar.
                </p>
                <div style="display:flex;flex-wrap:wrap;gap:8px;justify-content:center;max-width:500px;">
                    @foreach(array_slice($sugerencias, 0, 4) as $s)
                    <button wire:click="usarSugerencia('{{ $s }}')"
                            style="background:#fff;border:1.5px solid #e2e8f0;border-radius:99px;padding:9px 18px;font-size:13px;font-weight:600;color:#334155;cursor:pointer;transition:all .12s;box-shadow:0 1px 4px rgba(15,23,42,.06);"
                            onmouseover="this.style.borderColor='#1e3a8a';this.style.color='#1e3a8a';"
                            onmouseout="this.style.borderColor='#e2e8f0';this.style.color='#334155';">
                        {{ $s }}
                    </button>
                    @endforeach
                </div>
            </div>
            @endif

            @foreach($mensajes as $msg)
                @if($msg['rol'] === 'user')
                <div class="ic-msg-user">
                    <div class="ic-bubble-u">{{ $msg['texto'] }}</div>
                </div>
                @else
                <div class="ic-msg-bot">
                    <div class="ic-avatar">
                        <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="ic-bubble-b">{{ $msg['texto'] }}</div>
                        @if(!empty($msg['herramientas']))
                        <div>
                            @foreach($msg['herramientas'] as $h)
                            <span class="ic-tool-chip">
                                <svg width="9" height="9" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 010 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 010-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                {{ str_replace('_',' ',$h) }}
                            </span>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>
                @endif
            @endforeach

            @if($cargando)
            <div class="ic-msg-bot">
                <div class="ic-avatar">
                    <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/>
                    </svg>
                </div>
                <div class="ic-bubble-b" style="padding:14px 18px;">
                    <span class="ic-dot"></span>
                    <span class="ic-dot"></span>
                    <span class="ic-dot"></span>
                </div>
            </div>
            @endif
        </div>

        {{-- Input bar --}}
        <div style="background:#fff;border-top:1px solid #e2e8f0;padding:16px 24px;flex-shrink:0;">
            <form wire:submit="enviar" style="display:flex;gap:12px;align-items:flex-end;">
                <textarea
                    wire:model="input"
                    wire:keydown.enter.prevent="enviar"
                    placeholder="Escríbele a Inmo... (Enter para enviar)"
                    rows="2"
                    {{ $cargando ? 'disabled' : '' }}
                    style="flex:1;border:1.5px solid #e2e8f0;border-radius:14px;padding:12px 16px;font-size:14px;color:#1e293b;resize:none;outline:none;font-family:inherit;line-height:1.5;transition:all .15s;background:#f8fafc;"
                    onfocus="this.style.borderColor='#1e3a8a';this.style.background='#fff';this.style.boxShadow='0 0 0 3px rgba(30,58,138,.08)';"
                    onblur="this.style.borderColor='#e2e8f0';this.style.background='#f8fafc';this.style.boxShadow='none';"
                ></textarea>
                <button type="submit" {{ $cargando ? 'disabled' : '' }}
                        style="background:linear-gradient(135deg,#1e3a8a,#E11D48);border:none;border-radius:14px;padding:0 24px;height:52px;cursor:pointer;display:flex;align-items:center;gap:8px;font-size:13px;font-weight:700;color:#fff;white-space:nowrap;box-shadow:0 4px 14px rgba(30,58,138,.3);transition:all .15s;flex-shrink:0;"
                        onmouseover="this.style.transform='translateY(-1px)';this.style.boxShadow='0 6px 18px rgba(30,58,138,.35)';"
                        onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 4px 14px rgba(30,58,138,.3)';">
                    <svg width="17" height="17" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/>
                    </svg>
                    Enviar
                </button>
            </form>
            <p style="font-size:11px;color:#94a3b8;margin:8px 0 0;text-align:center;">
                Inmo puede ejecutar acciones reales — revisa antes de confirmar operaciones masivas
            </p>
        </div>
    </div>
</div>

<script>
document.addEventListener('scroll-bottom', () => {
    setTimeout(() => {
        const el = document.getElementById('inmo-page-msgs');
        if (el) el.scrollTop = el.scrollHeight;
    }, 80);
});
</script>
</x-filament-panels::page>

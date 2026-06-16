<div x-data="{ open: @entangle('abierto') }"
     style="position:fixed;bottom:24px;right:24px;z-index:9999;font-family:'Plus Jakarta Sans',system-ui,sans-serif;">
<style>
@keyframes inmo-pulse  { 0%,100%{opacity:1} 50%{opacity:.4} }
@keyframes inmo-spin   { from{transform:rotate(0deg)} to{transform:rotate(360deg)} }
@keyframes inmo-slide-up { from{opacity:0;transform:translateY(8px)} to{opacity:1;transform:translateY(0)} }
@keyframes inmo-typing-dot { 0%,60%,100%{transform:translateY(0);opacity:.4} 30%{transform:translateY(-5px);opacity:1} }
@keyframes inmo-bar-flow { 0%{background-position:0%} 100%{background-position:200%} }

.inmo-msg-enter { animation:inmo-slide-up .22s ease both; }
.inmo-tdot {
    width:8px;height:8px;border-radius:50%;
    background:linear-gradient(135deg,#1e3a8a,#7c3aed);
    display:inline-block;
}
.inmo-tdot:nth-child(1){animation:inmo-typing-dot 1.2s ease-in-out infinite 0s}
.inmo-tdot:nth-child(2){animation:inmo-typing-dot 1.2s ease-in-out infinite .2s}
.inmo-tdot:nth-child(3){animation:inmo-typing-dot 1.2s ease-in-out infinite .4s}
.inmo-bar {
    height:3px;
    background:linear-gradient(90deg,#1e3a8a,#7c3aed,#E11D48,#1e3a8a);
    background-size:200%;
    animation:inmo-bar-flow 1.5s linear infinite;
}
/* Ocultar wire:loading por defecto hasta que Livewire los controle */
[wire\:loading] { display:none !important; }
</style>

    {{-- Panel chat --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-3 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0 translate-y-3 scale-95"
         style="position:absolute;bottom:76px;right:0;width:370px;background:#fff;border-radius:22px;
                box-shadow:0 24px 64px rgba(15,23,42,.2),0 4px 16px rgba(15,23,42,.08);
                border:1px solid #e2e8f0;overflow:hidden;display:flex;flex-direction:column;">

        {{-- Barra progreso --}}
        <div wire:loading.delay.shorter wire:target="enviar,sugerir" class="inmo-bar"></div>

        {{-- Header --}}
        <div style="background:linear-gradient(135deg,#0F172A 0%,#1e3a8a 55%,#6d28d9 100%);padding:14px 16px;display:flex;align-items:center;justify-content:space-between;">
            <div style="display:flex;align-items:center;gap:10px;">
                <div style="position:relative;">
                    <div style="width:40px;height:40px;background:rgba(255,255,255,.12);border:1.5px solid rgba(255,255,255,.2);border-radius:12px;display:flex;align-items:center;justify-content:center;">
                        <svg wire:loading.remove.delay.shorter wire:target="enviar,sugerir"
                             width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/>
                        </svg>
                        <svg wire:loading.delay.shorter wire:target="enviar,sugerir"
                             width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"
                             style="animation:inmo-spin .7s linear infinite;">
                            <path stroke-linecap="round" d="M12 3a9 9 0 109 9"/>
                        </svg>
                    </div>
                    <div wire:loading.remove.delay.shorter wire:target="enviar,sugerir"
                         style="position:absolute;top:-3px;right:-3px;width:11px;height:11px;background:#86efac;border-radius:50%;border:2px solid #0f172a;box-shadow:0 0 8px #86efac;animation:inmo-pulse 2s infinite;"></div>
                    <div wire:loading.delay.shorter wire:target="enviar,sugerir"
                         style="position:absolute;top:-3px;right:-3px;width:11px;height:11px;background:#fbbf24;border-radius:50%;border:2px solid #0f172a;box-shadow:0 0 8px #fbbf24;animation:inmo-pulse .6s infinite;"></div>
                </div>
                <div>
                    <div style="font-size:15px;font-weight:800;color:#fff;letter-spacing:-.01em;line-height:1.1;">Inmo</div>
                    <div style="margin-top:2px;">
                        <span wire:loading.remove.delay.shorter wire:target="enviar,sugerir"
                              style="font-size:11px;color:rgba(255,255,255,.55);font-weight:500;">● Agente activo</span>
                        <span wire:loading.delay.shorter wire:target="enviar,sugerir"
                              style="font-size:11px;color:#fcd34d;font-weight:600;">◉ Consultando sistema...</span>
                    </div>
                </div>
            </div>
            <div style="display:flex;gap:6px;align-items:center;">
                @if(!empty($mensajes))
                <button wire:click="limpiar"
                        style="background:rgba(255,255,255,.1);border:none;border-radius:8px;padding:4px 10px;cursor:pointer;color:rgba(255,255,255,.55);font-size:11px;font-weight:600;"
                        onmouseover="this.style.background='rgba(255,255,255,.2)';this.style.color='#fff';"
                        onmouseout="this.style.background='rgba(255,255,255,.1)';this.style.color='rgba(255,255,255,.55)';">
                    Limpiar
                </button>
                @endif
                <button @click="open=false"
                        style="background:rgba(255,255,255,.1);border:none;border-radius:8px;width:28px;height:28px;cursor:pointer;display:flex;align-items:center;justify-content:center;">
                    <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Mensajes --}}
        <div id="inmo-msgs"
             style="flex:1;overflow-y:auto;padding:16px;display:flex;flex-direction:column;gap:10px;max-height:390px;min-height:200px;background:#f8fafc;scroll-behavior:smooth;">

            @if(empty($mensajes))
            <div style="text-align:center;padding:24px 12px;" class="inmo-msg-enter">
                <div style="width:56px;height:56px;background:linear-gradient(135deg,#0f172a,#1e3a8a);border-radius:16px;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;box-shadow:0 4px 16px rgba(15,23,42,.25);">
                    <svg width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="1.3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/>
                    </svg>
                </div>
                <p style="font-size:15px;font-weight:800;color:#1e293b;margin:0 0 4px;letter-spacing:-.02em;">Hola, soy Inmo</p>
                <p style="font-size:12px;color:#64748b;margin:0 0 16px;line-height:1.6;">Tu agente inmobiliario inteligente. Tengo acceso en tiempo real al sistema de Serviarrendar.</p>
                <div style="display:flex;flex-direction:column;gap:5px;">
                    @foreach($sugerencias as $s)
                    <button wire:click="sugerir('{{ $s }}')"
                            style="background:#fff;border:1.5px solid #e2e8f0;border-radius:10px;padding:8px 12px;font-size:12px;font-weight:600;color:#334155;cursor:pointer;text-align:left;transition:all .12s;"
                            onmouseover="this.style.borderColor='#1e3a8a';this.style.color='#1e3a8a';this.style.background='#f0f7ff';"
                            onmouseout="this.style.borderColor='#e2e8f0';this.style.color='#334155';this.style.background='#fff';">
                        {{ $s }}
                    </button>
                    @endforeach
                </div>
            </div>
            @endif

            @foreach($mensajes as $msg)
                @if($msg['rol'] === 'user')
                <div style="display:flex;justify-content:flex-end;" class="inmo-msg-enter">
                    <div style="background:linear-gradient(135deg,#1e3a8a,#2563eb);color:#fff;border-radius:16px 16px 4px 16px;padding:10px 14px;max-width:82%;font-size:13px;line-height:1.55;box-shadow:0 2px 8px rgba(30,58,138,.22);word-break:break-word;">
                        {{ $msg['texto'] }}
                    </div>
                </div>
                @else
                <div style="display:flex;gap:8px;align-items:flex-start;" class="inmo-msg-enter">
                    <div style="width:28px;height:28px;border-radius:50%;background:linear-gradient(135deg,#0f172a,#1e3a8a);display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 2px 6px rgba(15,23,42,.2);">
                        <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/>
                        </svg>
                    </div>
                    <div style="background:#fff;border:1px solid #e8edf4;border-radius:4px 16px 16px 16px;padding:10px 14px;max-width:84%;font-size:13px;line-height:1.7;color:#1e293b;box-shadow:0 1px 4px rgba(15,23,42,.06);word-break:break-word;">
                        {!! \App\Helpers\InmoMarkdown::render($msg['texto']) !!}
                        @if(!empty($msg['herramientas']))
                        <div style="margin-top:8px;padding-top:8px;border-top:1px solid #f1f5f9;display:flex;flex-wrap:wrap;gap:4px;">
                            @foreach($msg['herramientas'] as $h)
                            <span style="background:#eff6ff;border:1px solid #bfdbfe;color:#2563eb;font-size:10px;font-weight:700;padding:2px 7px;border-radius:99px;">⚙ {{ str_replace('_',' ',$h) }}</span>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>
                @endif
            @endforeach

            {{-- Typing dots: wire:loading con delay --}}
            <div wire:loading.delay.shorter wire:target="enviar,sugerir" class="inmo-msg-enter">
                <div style="display:flex;gap:8px;align-items:flex-start;">
                    <div style="width:28px;height:28px;border-radius:50%;background:linear-gradient(135deg,#0f172a,#1e3a8a);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/>
                        </svg>
                    </div>
                    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:4px 16px 16px 16px;padding:12px 16px;display:flex;gap:5px;align-items:center;box-shadow:0 1px 4px rgba(15,23,42,.06);">
                        <span class="inmo-tdot"></span>
                        <span class="inmo-tdot"></span>
                        <span class="inmo-tdot"></span>
                        <span style="font-size:11px;color:#94a3b8;font-weight:600;margin-left:5px;">pensando...</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Input --}}
        <div style="padding:12px 14px 14px;border-top:1px solid #e8edf4;background:#fff;">
            <form wire:submit="enviar" style="display:flex;gap:8px;align-items:center;">
                <textarea
                    wire:model="input"
                    wire:keydown.enter.prevent="enviar"
                    wire:loading.attr="disabled" wire:target="enviar,sugerir"
                    placeholder="Escríbele a Inmo..."
                    rows="1"
                    style="flex:1;border:1.5px solid #e2e8f0;border-radius:12px;padding:10px 13px;font-size:13px;color:#1e293b;resize:none;outline:none;font-family:inherit;line-height:1.4;transition:all .18s;background:#f8fafc;"
                    onfocus="this.style.borderColor='#1e3a8a';this.style.background='#fff';this.style.boxShadow='0 0 0 3px rgba(30,58,138,.08)';"
                    onblur="this.style.borderColor='#e2e8f0';this.style.background='#f8fafc';this.style.boxShadow='none';"
                ></textarea>

                <button type="submit"
                        wire:loading.remove.delay.shorter wire:target="enviar,sugerir"
                        style="background:linear-gradient(135deg,#1e3a8a,#E11D48);border:none;border-radius:12px;width:42px;height:42px;cursor:pointer;display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 3px 10px rgba(30,58,138,.3);transition:transform .15s;"
                        onmouseover="this.style.transform='scale(1.08)';"
                        onmouseout="this.style.transform='scale(1)';">
                    <svg width="17" height="17" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/>
                    </svg>
                </button>

            </form>
        </div>
    </div>

    {{-- Botón flotante --}}
    <button @click="open=!open"
            style="display:flex;align-items:center;gap:10px;background:linear-gradient(135deg,#0F172A 0%,#1e3a8a 55%,#6d28d9 100%);border:none;border-radius:99px;padding:11px 20px 11px 13px;cursor:pointer;box-shadow:0 8px 28px rgba(15,23,42,.28);transition:transform .15s;"
            onmouseover="this.style.transform='scale(1.05)';"
            onmouseout="this.style.transform='scale(1)';">
        <div style="width:34px;height:34px;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.18);border-radius:50%;display:flex;align-items:center;justify-content:center;position:relative;">
            <svg width="17" height="17" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/>
            </svg>
            <div style="position:absolute;top:-2px;right:-2px;width:11px;height:11px;background:#86efac;border-radius:50%;border:2px solid #0f172a;box-shadow:0 0 8px #86efac;animation:inmo-pulse 2s infinite;"></div>
        </div>
        <div>
            <div style="font-size:14px;font-weight:800;color:#fff;letter-spacing:-.01em;line-height:1.1;">Inmo</div>
            <div style="font-size:10px;color:rgba(255,255,255,.5);font-weight:500;line-height:1;margin-top:2px;">Agente IA</div>
        </div>
    </button>

<script>
document.addEventListener('inmo-scroll', () => {
    setTimeout(() => {
        const el = document.getElementById('inmo-msgs');
        if (el) el.scrollTop = el.scrollHeight;
    }, 80);
});
</script>
</div>

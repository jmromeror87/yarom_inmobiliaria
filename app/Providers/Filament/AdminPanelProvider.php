<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Navigation\NavigationGroup;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Illuminate\Support\HtmlString;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(\App\Filament\Pages\Auth\Login::class)
            ->profile(\App\Filament\Pages\Auth\EditProfile::class)
            ->font('Plus Jakarta Sans')
            ->navigationGroups([
                NavigationGroup::make('CRM'),
                NavigationGroup::make('Operativo'),
                NavigationGroup::make('Contratación'),
                NavigationGroup::make('Cobros'),
                NavigationGroup::make('Contabilidad'),
                NavigationGroup::make('Configuración')->collapsed(true),
                NavigationGroup::make('Sistema')->collapsed(true),
            ])
            ->topbar(true)
            ->sidebarCollapsibleOnDesktop()
            // Logo compacto para el topbar — igual al estilo Farmacia
            ->brandLogo(fn () => new HtmlString('
                <div style="display:flex;align-items:center;gap:10px;">
                    <div style="width:34px;height:34px;background:linear-gradient(135deg,#1e3a8a,#E11D48);border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <svg viewBox="0 0 32 32" fill="none" width="19" height="19"><path d="M4 28V14l12-9 12 9v14H20v-7h-8v7H4z" fill="#fff"/></svg>
                    </div>
                    <div style="display:flex;flex-direction:column;line-height:1.1;">
                        <span style="font-size:14px;font-weight:900;letter-spacing:-.02em;color:#0F172A;">YarOM <span style="color:#E11D48;">INMO</span></span>
                        <span style="font-size:9px;font-weight:700;color:#94a3b8;letter-spacing:.06em;text-transform:uppercase;">Serviarrendar</span>
                    </div>
                </div>
            '))
            ->colors([
                'primary' => Color::hex('#E11D48'),
                'info'    => Color::hex('#2563EB'),
                'success' => Color::Emerald,
                'warning' => Color::Amber,
                'danger'  => Color::hex('#E11D48'),
                'gray'    => Color::Slate,
            ])
            // ── Estilos sidebar + topbar ────────────────────────────────
            ->renderHook(
                PanelsRenderHook::SIDEBAR_NAV_START,
                fn (): string => '
                <style>
                    /* Alpine cloak — ocultar elementos antes de inicializar */
                    [x-cloak]{display:none!important;}

                    /* Ícono blanco en botones con gradiente inline */
                    [style*="linear-gradient(135deg,#1e3a8a"] svg,
                    [style*="linear-gradient(135deg,#1e3a8a"] span[class*="fi-icon"] {
                        color:#fff!important;stroke:#fff!important;fill:none!important;
                    }

                    /* ── Botones wizard (Siguiente / Anterior) ─────────────── */
                    .fi-wizard-navigation-action[wire\:click*="nextStep"],
                    button[wire\:click*="nextStep"],
                    .fi-btn[wire\:click*="nextStep"] {
                        background:linear-gradient(135deg,#1e3a8a,#E11D48)!important;
                        border:none!important;color:#fff!important;font-weight:700!important;
                        border-radius:10px!important;box-shadow:0 3px 10px rgba(225,29,72,.28)!important;
                    }
                    button[wire\:click*="previousStep"],
                    .fi-btn[wire\:click*="previousStep"] {
                        border-radius:10px!important;font-weight:600!important;
                    }

                    /* ── Form actions Guardar / Cancelar ───────────────────── */
                    .fi-form-actions .fi-btn-color-primary,
                    .fi-ac-action[data-action-name="save"] .fi-btn {
                        background:linear-gradient(135deg,#1e3a8a,#E11D48)!important;
                        border:none!important;color:#fff!important;font-weight:700!important;
                        border-radius:10px!important;box-shadow:0 3px 10px rgba(225,29,72,.28)!important;
                    }
                    .fi-form-actions .fi-btn-color-gray {
                        border-radius:10px!important;font-weight:600!important;
                        border-color:#cbd5e1!important;color:#475569!important;
                    }

                    :root{
                        --color-primary-50:#fff1f2!important;--color-primary-100:#ffe4e6!important;
                        --color-primary-200:#fecdd3!important;--color-primary-300:#fda4af!important;
                        --color-primary-400:#fb7185!important;--color-primary-500:#f43f5e!important;
                        --color-primary-600:#e11d48!important;--color-primary-700:#be123c!important;
                        --color-primary-800:#9f1239!important;--color-primary-900:#881337!important;
                        --color-primary-950:#4c0519!important;
                    }

                    /* ══ TOPBAR CUSTOM (yr-*) ══════════════════════════════════ */
                    .yr-topbar-ctn{position:sticky;top:0;z-index:50;}
                    .yr-topbar{
                        display:flex;align-items:center;height:64px;
                        padding:0 24px;gap:0;
                        background:#ffffff;
                        border-bottom:1px solid #e8edf2;
                        box-shadow:0 1px 6px rgba(15,23,42,.06);
                    }

                    /* Izquierda */
                    .yr-topbar-left{display:flex;align-items:center;gap:8px;flex-shrink:0;}
                    .yr-brand{display:flex;align-items:center;text-decoration:none;}

                    /* Botón colapso — UNO solo, limpio */
                    .yr-collapse-btn{
                        display:flex;align-items:center;justify-content:center;
                        width:28px;height:28px;border-radius:7px;
                        border:1px solid #e2e8f0;background:transparent;
                        color:#94a3b8;cursor:pointer;transition:all .15s;flex-shrink:0;
                        padding:0;
                    }
                    .yr-collapse-btn:hover{background:#f8fafc;color:#E11D48;border-color:#fda4af;}

                    /* Derecha */
                    .yr-topbar-right{display:flex;align-items:center;gap:16px;flex-shrink:0;}

                    /* Reloj */
                    .yr-clock{display:flex;flex-direction:column;align-items:flex-end;line-height:1.15;flex-shrink:0;}
                    .yr-clock-time{font-size:14px;font-weight:800;color:#0F172A;font-variant-numeric:tabular-nums;letter-spacing:.01em;}
                    .yr-clock-date{font-size:10px;font-weight:500;color:#94a3b8;white-space:nowrap;}

                    /* Estado */
                    .yr-status{display:flex;align-items:center;gap:6px;flex-shrink:0;}
                    .yr-status-dot{width:7px;height:7px;background:#22c55e;border-radius:50%;box-shadow:0 0 0 3px rgba(34,197,94,.18);flex-shrink:0;}
                    .yr-status-name{font-size:11px;font-weight:700;color:#334155;line-height:1.2;white-space:nowrap;}
                    .yr-status-sub{font-size:10px;color:#94a3b8;font-weight:500;}

                    /* Separador vertical */
                    .yr-divider{width:1px;height:28px;background:#e2e8f0;flex-shrink:0;margin:0 4px;}

                    /* ── Buscador global (yr-search-*) ────────────────── */
                    .yr-search-ctn{width:280px;flex-shrink:0;}
                    .yr-search-wrap{position:relative;}
                    .yr-search-field{
                        display:flex;align-items:center;gap:8px;
                        background:#f1f5f9;border:1.5px solid transparent;
                        border-radius:10px;padding:0 12px;
                        transition:border-color .15s, background .15s;
                    }
                    .yr-search-field:focus-within{
                        background:#fff;border-color:#E11D48;
                    }
                    .yr-search-icon{width:15px;height:15px;color:#94a3b8;flex-shrink:0;}
                    .yr-search-field:focus-within .yr-search-icon{color:#E11D48;}
                    .yr-search-input{
                        flex:1;background:transparent;border:none;outline:none;
                        box-shadow:none;font-size:13px;font-weight:500;
                        color:#334155;padding:9px 0;width:100%;
                    }
                    .yr-search-input::placeholder{color:#94a3b8;}
                    .yr-search-input::-webkit-search-cancel-button{display:none;}

                    /* Notificaciones y avatar */
                    .fi-icon-btn{color:#64748b!important;width:34px!important;height:34px!important;border-radius:9px!important;display:flex!important;align-items:center!important;justify-content:center!important;transition:all .15s!important;background:transparent!important;border:none!important;}
                    .fi-icon-btn:hover{color:#E11D48!important;background:rgba(225,29,72,.07)!important;}
                    .fi-badge{background:#E11D48!important;color:#fff!important;font-size:10px!important;font-weight:800!important;}
                    .fi-user-avatar{background:#0F172A!important;color:#fff!important;font-weight:900!important;font-size:13px!important;}
                    .fi-user-menu-trigger{border-radius:50%!important;padding:0!important;}
                    /* Búsqueda */
                    .fi-global-search-field{background:#f1f5f9!important;border:none!important;border-radius:10px!important;padding:0 14px!important;min-width:240px!important;display:flex!important;align-items:center!important;gap:8px!important;}
                    .fi-global-search-field:focus-within{background:#e2e8f0!important;}
                    .fi-global-search-field input{background:transparent!important;border:none!important;outline:none!important;box-shadow:none!important;font-size:13px!important;font-weight:500!important;color:#334155!important;padding:9px 0!important;width:100%!important;}
                    .fi-global-search-field input::placeholder{color:#94a3b8!important;}
                    .fi-global-search-field svg{color:#94a3b8!important;width:15px!important;height:15px!important;flex-shrink:0!important;}
                    /* Botones */
                    .fi-icon-btn{color:#64748b!important;width:36px!important;height:36px!important;border-radius:10px!important;display:flex!important;align-items:center!important;justify-content:center!important;transition:all .15s!important;}
                    .fi-icon-btn:hover{color:#E11D48!important;background:rgba(225,29,72,.08)!important;}
                    .fi-badge{background:#E11D48!important;color:#fff!important;font-size:10px!important;font-weight:800!important;}
                    /* Avatar */
                    .fi-user-menu{display:flex!important;}
                    .fi-user-avatar{
                        background:linear-gradient(135deg,#1e3a8a,#E11D48)!important;
                        color:#fff!important;font-weight:900!important;font-size:13px!important;
                        box-shadow:0 0 0 2px #fff,0 0 0 4px rgba(225,29,72,.5)!important;
                        border-radius:50%!important;
                        transition:box-shadow .2s!important;
                    }
                    .fi-user-menu-trigger{border-radius:50%!important;padding:2px!important;}
                    .fi-user-menu-trigger:hover .fi-user-avatar{
                        box-shadow:0 0 0 2px #fff,0 0 0 4px #E11D48!important;
                    }

                    /* ── Sidebar ── especificidad alta con .fi-main-sidebar ── */
                    /* Header oculto con altura 0 para que no deje franja */
                    .fi-main-sidebar .fi-sidebar-header-ctn{display:none!important;height:0!important;overflow:hidden!important;padding:0!important;margin:0!important;}
                    /* Sidebar abierto */
                    .fi-main-sidebar{background:#fff!important;border-right:1px solid #f1f5f9!important;box-shadow:2px 0 12px rgba(15,23,42,.04)!important;}
                    .fi-main-sidebar.fi-sidebar-open{width:260px!important;}

                    /* Sidebar colapsado — solo iconos, compactos y centrados */
                    .fi-body-has-sidebar-collapsible-on-desktop .fi-main-sidebar:not(.fi-sidebar-open){
                        width:56px!important;
                    }
                    .fi-body-has-sidebar-collapsible-on-desktop .fi-main-sidebar:not(.fi-sidebar-open) nav.fi-sidebar-nav{
                        padding:6px 4px 70px!important;gap:0!important;
                    }
                    .fi-body-has-sidebar-collapsible-on-desktop .fi-main-sidebar:not(.fi-sidebar-open) .fi-sidebar-group-btn{
                        display:none!important;
                    }
                    .fi-body-has-sidebar-collapsible-on-desktop .fi-main-sidebar:not(.fi-sidebar-open) .fi-sidebar-item-btn{
                        justify-content:center!important;padding:8px 0!important;margin:1px 4px!important;width:calc(100% - 8px)!important;
                    }
                    .fi-body-has-sidebar-collapsible-on-desktop .fi-main-sidebar:not(.fi-sidebar-open) .fi-sidebar-item-label{
                        display:none!important;
                    }
                    .fi-body-has-sidebar-collapsible-on-desktop .fi-main-sidebar:not(.fi-sidebar-open) .fi-sidebar-item-btn .fi-icon{
                        width:20px!important;height:20px!important;
                    }
                    .fi-main-sidebar .fi-sidebar-header-ctn{display:none!important;}

                    /* nav: Filament aplica py-8(32px) y gap-y-7(28px) via Tailwind — los anulamos */
                    .fi-main-sidebar nav.fi-sidebar-nav{
                        padding:6px 8px 70px 8px!important;
                        gap:0!important;
                    }
                    /* ul grupos: Filament aplica gap-y-7(28px) — anulamos */
                    .fi-main-sidebar ul.fi-sidebar-nav-groups{
                        gap:0!important;margin:0!important;padding:0!important;
                    }
                    /* Cada grupo li */
                    .fi-main-sidebar .fi-sidebar-group{
                        gap:0!important;margin:0!important;padding:0!important;display:flex!important;flex-direction:column!important;
                    }
                    /* Label de grupo (CRM, OPERATIVO…) */
                    .fi-main-sidebar .fi-sidebar-group-btn{
                        padding:12px 8px 2px!important;gap:4px!important;min-height:unset!important;
                    }
                    .fi-main-sidebar .fi-sidebar-group-label{
                        font-size:10px!important;font-weight:700!important;color:#94a3b8!important;
                        text-transform:uppercase!important;letter-spacing:.08em!important;line-height:1!important;
                    }
                    /* Items dentro del grupo: Filament aplica gap-y-1(4px) — ok, lo dejamos */
                    .fi-main-sidebar .fi-sidebar-group-items{
                        gap:3px!important;padding:2px 0!important;margin:0!important;
                    }
                    /* Cada item li */
                    .fi-main-sidebar .fi-sidebar-item{margin:0!important;padding:0!important;}
                    .fi-main-sidebar .fi-sidebar-item-btn{
                        display:flex!important;align-items:center!important;gap:9px!important;
                        padding:10px 10px!important;margin:2px 2px!important;
                        border-radius:8px!important;min-height:unset!important;
                        background:transparent!important;border:none!important;box-shadow:none!important;
                        width:calc(100% - 4px)!important;transition:background .15s!important;
                    }
                    .fi-main-sidebar .fi-sidebar-item-btn .fi-icon{color:#E11D48!important;width:17px!important;height:17px!important;flex-shrink:0!important;}
                    .fi-main-sidebar .fi-sidebar-item-label{color:#334155!important;font-size:13px!important;font-weight:600!important;line-height:1.2!important;}
                    .fi-main-sidebar .fi-sidebar-item-btn:hover{background:rgba(225,29,72,.07)!important;}
                    .fi-main-sidebar .fi-sidebar-item-btn:hover .fi-sidebar-item-label{color:#0F172A!important;}
                    .fi-main-sidebar .fi-sidebar-item-grouped-border{display:none!important;}
                    /* Activo */
                    .fi-main-sidebar .fi-sidebar-item.fi-active>.fi-sidebar-item-btn{background:#fde8d8!important;}
                    .fi-main-sidebar .fi-sidebar-item.fi-active>.fi-sidebar-item-btn .fi-icon{color:#E11D48!important;}
                    .fi-main-sidebar .fi-sidebar-item.fi-active>.fi-sidebar-item-btn .fi-sidebar-item-label{color:#E11D48!important;font-weight:700!important;}
                    /* Notificaciones */
                    .fi-main-sidebar .fi-sidebar-database-notifications-btn{padding:7px 8px!important;gap:8px!important;width:100%!important;display:flex!important;align-items:center!important;}
                    .fi-main-sidebar .fi-sidebar-database-notifications-btn-label{font-size:13px!important;font-weight:600!important;color:#334155!important;}
                    .fi-main-sidebar .fi-sidebar-database-notifications-btn svg{color:#E11D48!important;}

                    /* Contenido */
                    .fi-section,.fi-ta-ctn,.fi-card,.fi-wi-card{background:#fff!important;border-radius:16px!important;border:1px solid #f1f5f9!important;box-shadow:0 2px 8px rgba(15,23,42,.04)!important;}
                    .fi-main{max-width:100%!important;width:100%!important;}
                    .fi-page{max-width:100%!important;width:100%!important;}
                    /* Widget KIPs — sin padding ni borde del wrapper */
                    .fi-page-header-widgets .fi-wi{padding:0!important;background:transparent!important;border:none!important;box-shadow:none!important;}
                </style>
                '
            )
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn (): string => '
                <style>
                .fi-main-ctn{padding-bottom:60px!important;background:#F7F8FA!important;}
                .fi-main{background:#F7F8FA!important;}
                body{background:#F7F8FA!important;}
                .fi-btn.fi-color-primary,.fi-btn-color-primary{background-color:#E11D48!important;color:#fff!important;border:none!important;font-weight:700!important;}
                .fi-btn.fi-color-primary:hover{background-color:#be123c!important;}
                .fi-toggle-input:checked~.fi-toggle-indicator,[role="switch"][aria-checked="true"]{background-color:#E11D48!important;}
                /* Franja gris sidebar header — ocultar globalmente */
                .fi-main-sidebar .fi-sidebar-header-ctn{display:none!important;height:0!important;overflow:hidden!important;padding:0!important;margin:0!important;}

                /* ── User menu dropdown ── */
                .fi-user-menu .fi-dropdown-panel{
                    min-width:220px!important;border-radius:14px!important;
                    box-shadow:0 8px 32px rgba(15,23,42,.14)!important;
                    border:1px solid #f1f5f9!important;overflow:hidden!important;
                    padding:6px!important;
                }
                /* Header con nombre */
                .fi-dropdown-header{
                    padding:10px 12px!important;border-radius:10px!important;
                    background:#f8fafc!important;margin-bottom:4px!important;
                }
                .fi-dropdown-header-label{
                    font-size:12px!important;font-weight:700!important;
                    color:#0F172A!important;letter-spacing:.01em!important;
                }
                .fi-dropdown-header .fi-icon{color:#94a3b8!important;width:20px!important;height:20px!important;}

                /* Theme switcher — oculto */
                .fi-theme-switcher{display:none!important;}


                /* Lista de items (logout) */
                .fi-dropdown-list{padding:2px 0!important;}
                .fi-dropdown-list-item{border-radius:8px!important;margin:1px 0!important;}
                .fi-dropdown-list-item-label{font-size:13px!important;font-weight:600!important;color:#334155!important;}
                .fi-dropdown-list-item:hover .fi-dropdown-list-item-label{color:#E11D48!important;}
                .fi-dropdown-list-item svg{color:#64748b!important;width:16px!important;height:16px!important;}
                .fi-dropdown-list-item:hover svg{color:#E11D48!important;}
                .fi-dropdown-list-item-btn{padding:9px 12px!important;border-radius:8px!important;width:100%!important;display:flex!important;align-items:center!important;gap:10px!important;}
                .fi-dropdown-list-item-btn:hover{background:rgba(225,29,72,.06)!important;}
                </style>
                <script>
                (function(){
                    function styleItems(){
                        // Items del sidebar
                        document.querySelectorAll(".fi-main-sidebar .fi-sidebar-item-btn").forEach(function(el){
                            el.style.setProperty("padding","10px 10px","important");
                            el.style.setProperty("margin","2px 2px","important");
                            el.style.setProperty("border-radius","8px","important");
                        });
                        document.querySelectorAll(".fi-main-sidebar .fi-sidebar-group-items").forEach(function(el){
                            el.style.setProperty("gap","4px","important");
                            el.style.setProperty("display","flex","important");
                            el.style.setProperty("flex-direction","column","important");
                        });
                        // Avatar JR — gradiente navy→rojo con anillo
                        document.querySelectorAll(".fi-user-avatar").forEach(function(el){
                            el.style.setProperty("background","linear-gradient(135deg,#1e3a8a,#E11D48)","important");
                            el.style.setProperty("color","#fff","important");
                            el.style.setProperty("font-weight","900","important");
                            el.style.setProperty("box-shadow","0 0 0 2px #fff,0 0 0 4px rgba(225,29,72,.5)","important");
                        });
                        // ── Password reveal: 1 solo ojo toggle ──
                        fixPasswordToggles();
                    }
                    function fixPasswordToggles(){
                        var eyeOpen  = \'<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" style="width:18px;height:18px"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>\';
                        var eyeSlash = \'<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" style="width:18px;height:18px"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>\';
                        // Buscar inputs tipo password sin botón propio aún
                        document.querySelectorAll(\'input[type="password"]\').forEach(function(input){
                            if(input._pwToggle) return;
                            var wrapper = input.closest(".fi-input-wrp");
                            if(!wrapper) return;
                            input._pwToggle = true;
                            // Ocultar botones revealable existentes de Filament si los hay
                            wrapper.querySelectorAll("button[x-show]").forEach(function(b){
                                b.style.setProperty("display","none","important");
                            });
                            // Crear botón único
                            var btn = document.createElement("button");
                            btn.type = "button";
                            btn.innerHTML = eyeOpen;
                            btn.style.cssText = "display:flex;align-items:center;justify-content:center;width:32px;height:32px;background:none;border:none;cursor:pointer;color:#6b7280;flex-shrink:0;padding:0;border-radius:6px;";
                            btn.onmouseenter = function(){ btn.style.color="#E11D48"; };
                            btn.onmouseleave = function(){ btn.style.color="#6b7280"; };
                            btn.onclick = function(e){
                                e.preventDefault(); e.stopPropagation();
                                var shown = input.type === "text";
                                input.type = shown ? "password" : "text";
                                btn.innerHTML = shown ? eyeOpen : eyeSlash;
                            };
                            // Insertar después del input dentro del wrapper
                            var suffix = wrapper.querySelector(".fi-input-wrp-suffix");
                            if(suffix){ suffix.innerHTML=""; suffix.appendChild(btn); }
                            else { wrapper.appendChild(btn); }
                        });
                    }

                    if(document.readyState==="loading"){
                        document.addEventListener("DOMContentLoaded", styleItems);
                    } else { styleItems(); }
                    document.addEventListener("livewire:navigated", styleItems);
                    document.addEventListener("livewire:navigated", function(){ setTimeout(fixPasswordToggles, 200); });

                    // MutationObserver para detectar campos de contraseña nuevos
                    var pwObserver = new MutationObserver(function(){ fixPasswordToggles(); });
                    pwObserver.observe(document.body, { childList:true, subtree:true });
                })();
                </script>
                <footer id="yr-footer" style="position:fixed;bottom:0;left:0;right:0;z-index:60;padding:11px 2.5rem;border-top:1px solid #112240;background:linear-gradient(135deg,#0F172A,#1e2d45,#E11D48);">
                    <div style="display:flex;justify-content:space-between;align-items:center;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;">
                        <div style="display:flex;align-items:center;gap:10px;color:rgba(255,255,255,0.8);">
                            <span>© ' . date('Y') . ' <span style="color:#fff;">YarOM ERP</span></span>
                            <span style="color:rgba(255,255,255,0.3);">|</span>
                            <span style="color:#60a5fa;">Infraestructura Cloud Engine</span>
                            <span style="color:rgba(255,255,255,0.3);">|</span>
                            <span style="background:rgba(96,165,250,0.15);border:1px solid rgba(96,165,250,0.3);color:#60a5fa;padding:2px 10px;border-radius:20px;font-size:10px;letter-spacing:0.1em;">v' . config('app.version') . '</span>
                        </div>
                        <div style="display:flex;align-items:center;gap:12px;">
                            <span style="color:rgba(255,255,255,0.6);">Desarrollado por</span>
                            <a href="https://linkedin.com/in/jmromeror87" target="_blank" style="text-decoration:none;display:flex;align-items:center;gap:6px;">
                                <span style="color:#fff;font-weight:900;">ING. JHOAN ROMERO</span>
                                <span style="font-size:8px;background:#E11D48;color:#fff;padding:2px 10px;border-radius:6px;">ARCHITECT</span>
                            </a>
                        </div>
                    </div>
                </footer>
                '
            )
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->pages([\App\Filament\Pages\Dashboard::class])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([Authenticate::class])
            ->userMenuItems([
                'profile' => \Filament\Actions\Action::make('profile')
                    ->label('Mi Perfil')
                    ->icon('heroicon-o-user-circle')
                    ->url(fn () => filament()->getProfileUrl())
                    ->sort(0),
                \Filament\Actions\Action::make('notas')
                    ->label('Notas y Tareas')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->url(fn () => route('filament.admin.pages.mensajes-internos'))
                    ->sort(1),
                'logout' => \Filament\Actions\Action::make('logout')
                    ->label('Salir')
                    ->icon('heroicon-o-arrow-left-end-on-rectangle')
                    ->url(fn () => filament()->getLogoutUrl())
                    ->postToUrl()
                    ->sort(PHP_INT_MAX),
            ])
            ->renderHook(
                PanelsRenderHook::USER_MENU_PROFILE_BEFORE,
                fn (): string => (function () {
                    $user = filament()->auth()->user();
                    if (!$user) return '';
                    $initials = collect(explode(' ', $user->name))->map(fn($w) => strtoupper(substr($w,0,1)))->take(2)->implode('');
                    $role = method_exists($user, 'getRoleNames') ? ($user->getRoleNames()->first() ?? 'Usuario') : 'Usuario';
                    return '
                    <div style="padding:14px 14px 10px;border-bottom:1px solid #f1f5f9;margin-bottom:4px;">
                        <div style="display:flex;align-items:center;gap:12px;">
                            <div style="width:44px;height:44px;background:linear-gradient(135deg,#0F172A,#E11D48);border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <span style="font-size:16px;font-weight:900;color:#fff;">'.$initials.'</span>
                            </div>
                            <div style="min-width:0;overflow:hidden;">
                                <div style="font-size:12px;font-weight:800;color:#0F172A;letter-spacing:.01em;">'.strtoupper($user->name).'</div>
                                <div style="font-size:11px;color:#64748b;margin-top:1px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">'.e($user->email ?? '').'</div>
                                <span style="display:inline-block;margin-top:4px;font-size:9px;font-weight:800;color:#16a34a;background:#dcfce7;padding:2px 8px;border-radius:20px;letter-spacing:.06em;text-transform:uppercase;">'.strtoupper($role).'</span>
                            </div>
                        </div>
                    </div>
                    ';
                })()
            )
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s');
    }
}

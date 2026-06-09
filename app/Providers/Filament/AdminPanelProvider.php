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
            // ── Reloj + estado en el centro del topbar ──────────────────
            ->renderHook(
                PanelsRenderHook::TOPBAR_START,
                fn (): string => '
                <div style="flex:1;display:flex;align-items:center;justify-content:center;gap:16px;min-width:0;">
                    <div style="text-align:center;line-height:1.2;">
                        <div id="yr-clock" style="font-size:16px;font-weight:900;color:#0F172A;font-variant-numeric:tabular-nums;letter-spacing:.01em;">--:--:--</div>
                        <div id="yr-date"  style="font-size:10px;font-weight:600;color:#94a3b8;">cargando...</div>
                    </div>
                    <div style="width:1px;height:28px;background:#e2e8f0;flex-shrink:0;"></div>
                    <div style="display:flex;align-items:center;gap:6px;">
                        <span style="width:7px;height:7px;background:#22c55e;border-radius:50%;box-shadow:0 0 0 3px rgba(34,197,94,.2);flex-shrink:0;display:inline-block;"></span>
                        <div style="line-height:1.2;">
                            <div style="font-size:11px;font-weight:800;color:#0F172A;">Sistema Activo</div>
                            <div style="font-size:10px;color:#94a3b8;font-weight:500;">En línea</div>
                        </div>
                    </div>
                </div>
                <script>
                (function(){
                    var D=["domingo","lunes","martes","miércoles","jueves","viernes","sábado"];
                    var M=["enero","febrero","marzo","abril","mayo","junio","julio","agosto","septiembre","octubre","noviembre","diciembre"];
                    function tick(){
                        var n=new Date();
                        var c=document.getElementById("yr-clock");
                        var d=document.getElementById("yr-date");
                        if(c) c.textContent=String(n.getHours()).padStart(2,"0")+":"+String(n.getMinutes()).padStart(2,"0")+":"+String(n.getSeconds()).padStart(2,"0");
                        if(d) d.textContent=D[n.getDay()]+", "+n.getDate()+" "+M[n.getMonth()]+" "+n.getFullYear();
                    }
                    tick(); setInterval(tick,1000);
                    document.addEventListener("livewire:navigated",tick);
                })();
                </script>
                '
            )
            // ── Estilos sidebar + topbar ────────────────────────────────
            ->renderHook(
                PanelsRenderHook::SIDEBAR_NAV_START,
                fn (): string => '
                <style>
                    :root{
                        --color-primary-50:#fff1f2!important;--color-primary-100:#ffe4e6!important;
                        --color-primary-200:#fecdd3!important;--color-primary-300:#fda4af!important;
                        --color-primary-400:#fb7185!important;--color-primary-500:#f43f5e!important;
                        --color-primary-600:#e11d48!important;--color-primary-700:#be123c!important;
                        --color-primary-800:#9f1239!important;--color-primary-900:#881337!important;
                        --color-primary-950:#4c0519!important;
                    }

                    /* ── Topbar ── */
                    .fi-topbar{background:#fff!important;border-bottom:1px solid #f1f5f9!important;box-shadow:0 1px 8px rgba(15,23,42,.05)!important;}
                    .fi-topbar nav{display:flex!important;align-items:center!important;width:100%!important;gap:12px!important;padding:0 20px!important;}
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
                    .fi-user-avatar{background:linear-gradient(135deg,#1e3a8a,#E11D48)!important;font-weight:900!important;}
                    .fi-user-menu-trigger{border-radius:50%!important;padding:0!important;}

                    /* ── Sidebar ── */
                    .fi-sidebar{background:#fff!important;border-right:1px solid #f1f5f9!important;box-shadow:2px 0 12px rgba(15,23,42,.04)!important;}
                    .fi-sidebar-header{border-bottom:1px solid #f1f5f9!important;padding:14px 16px!important;}

                    /* Grupos */
                    .fi-sidebar-group-label{font-weight:700!important;font-size:11px!important;color:#94a3b8!important;text-transform:uppercase!important;letter-spacing:.08em!important;padding:14px 16px 4px!important;}
                    .fi-sidebar-group-button{font-size:13px!important;font-weight:600!important;color:#334155!important;padding:10px 16px!important;width:100%!important;text-align:left!important;display:flex!important;align-items:center!important;gap:10px!important;border-radius:10px!important;margin:1px 8px!important;width:calc(100% - 16px)!important;}
                    .fi-sidebar-group-button:hover{background:rgba(0,0,0,.04)!important;color:#0F172A!important;}
                    .fi-sidebar-group-button svg{width:18px!important;height:18px!important;color:#E11D48!important;flex-shrink:0!important;display:block!important;}
                    .fi-sidebar-nav .fi-sidebar-group-items{border:none!important;margin-left:0!important;padding-left:0!important;}

                    /* Items */
                    .fi-sidebar-nav .fi-sidebar-item-btn::before,.fi-sidebar-nav .fi-sidebar-item-btn::after{display:none!important;content:none!important;}
                    .fi-sidebar-nav .fi-sidebar-item-btn{display:flex!important;align-items:center!important;gap:10px!important;margin:1px 8px!important;padding:9px 12px!important;border-radius:10px!important;background:transparent!important;border:none!important;box-shadow:none!important;transition:background .15s!important;width:calc(100% - 16px)!important;}
                    .fi-sidebar-nav .fi-sidebar-item-btn .fi-icon{color:#E11D48!important;width:18px!important;height:18px!important;flex-shrink:0!important;}
                    .fi-sidebar-nav .fi-sidebar-item-label{color:#334155!important;font-size:13px!important;font-weight:600!important;}
                    .fi-sidebar-nav .fi-sidebar-item-btn:hover{background:rgba(225,29,72,.06)!important;}
                    .fi-sidebar-nav .fi-sidebar-item-btn:hover .fi-icon{color:#be123c!important;}
                    .fi-sidebar-nav .fi-sidebar-item-btn:hover .fi-sidebar-item-label{color:#0F172A!important;}

                    /* Activo — pill completa como Farmacia */
                    .fi-sidebar-nav .fi-sidebar-item.fi-active>.fi-sidebar-item-btn{background:#fde8d8!important;border-radius:10px!important;}
                    .fi-sidebar-nav .fi-sidebar-item.fi-active>.fi-sidebar-item-btn .fi-icon{color:#E11D48!important;}
                    .fi-sidebar-nav .fi-sidebar-item.fi-active>.fi-sidebar-item-btn .fi-sidebar-item-label{color:#E11D48!important;font-weight:700!important;}

                    /* Notificaciones sidebar */
                    .fi-sidebar-database-notifications-btn{color:#334155!important;width:100%!important;display:flex!important;align-items:center!important;gap:10px!important;padding:10px 16px!important;}
                    .fi-sidebar-database-notifications-btn-label{color:#334155!important;font-size:13px!important;font-weight:600!important;}
                    .fi-sidebar-database-notifications-btn svg{color:#E11D48!important;}

                    /* Contenido */
                    .fi-section,.fi-ta-ctn,.fi-card,.fi-wi-card{background:#fff!important;border-radius:16px!important;border:1px solid #f1f5f9!important;box-shadow:0 2px 8px rgba(15,23,42,.04)!important;}
                    .fi-main{max-width:100%!important;width:100%!important;}
                    .fi-page{max-width:100%!important;width:100%!important;}
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
                </style>
                <footer id="yr-footer" style="position:fixed;bottom:0;left:0;right:0;z-index:60;padding:11px 2.5rem;border-top:1px solid #112240;background:linear-gradient(135deg,#0F172A,#1e3a8a);">
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
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s');
    }
}

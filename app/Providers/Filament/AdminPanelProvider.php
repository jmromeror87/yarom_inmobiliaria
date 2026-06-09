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
use Illuminate\Support\Facades\Auth;

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
            ->topbar(false)
            ->sidebarCollapsibleOnDesktop()
            ->brandLogo(fn () => new HtmlString('
                <div style="display:flex;align-items:center;gap:12px;">
                    <div style="width:34px;height:34px;background:linear-gradient(135deg,#0F172A,#2563EB);border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <svg viewBox="0 0 32 32" fill="none" width="20" height="20"><path d="M4 28V14l12-9 12 9v14H20v-7h-8v7H4z" fill="#fff"/></svg>
                    </div>
                    <div style="display:flex;flex-direction:column;line-height:1.15;">
                        <span style="font-size:17px;font-weight:900;letter-spacing:-.04em;color:#fff;text-transform:uppercase;">YAROM <span style="color:#E11D48;">INMO</span><span style="color:#60a5fa;">BILIARIA</span></span>
                        <span style="font-size:11px;font-weight:600;letter-spacing:0.05em;color:rgba(255,255,255,0.5);text-transform:uppercase;">Serviarrendar S.A.S</span>
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
                    .fi-sidebar{
                        background:linear-gradient(180deg,#1e3a8a 0%,#0d1b4b 85%,#0A192F 100%)!important;
                        border-right:none!important;
                        box-shadow:4px 0 24px rgba(0,0,0,.25)!important;
                    }
                    .fi-sidebar-header{border-bottom:1px solid rgba(255,255,255,.08)!important;}
                    .fi-sidebar-group-label{font-weight:800!important;text-transform:uppercase!important;letter-spacing:.1em!important;font-size:10px!important;color:#E11D48!important;}
                    .fi-sidebar-group-button{font-size:10px!important;font-weight:800!important;text-transform:uppercase!important;letter-spacing:.1em!important;color:#E11D48!important;}
                    .fi-sidebar-group-button svg{display:none!important;}
                    .fi-sidebar-group-button:hover{color:#fff!important;}
                    /* SIN LINEAS — ICONOS + TEXTO */
                    .fi-sidebar-nav .fi-sidebar-group-items{
                        border:none!important;
                        border-left:none!important;
                        outline:none!important;
                        margin-left:0!important;
                        padding-left:0!important;
                    }
                    .fi-sidebar-nav .fi-sidebar-item-btn::before,
                    .fi-sidebar-nav .fi-sidebar-item-btn::after{
                        display:none!important;
                        content:none!important;
                    }
                    .fi-sidebar-nav{padding-bottom:80px!important;}

                    .fi-sidebar-nav .fi-sidebar-item-btn{
                        display:flex!important;
                        align-items:center!important;
                        gap:10px!important;
                        margin:1px 8px!important;
                        padding:9px 12px!important;
                        border-radius:8px!important;
                        background:transparent!important;
                        border:none!important;
                        box-shadow:none!important;
                        transition:background .15s!important;
                    }
                    .fi-sidebar-nav .fi-sidebar-item-btn .fi-icon{
                        display:flex!important;
                        color:rgba(255,255,255,.4)!important;
                        width:17px!important;
                        height:17px!important;
                        flex-shrink:0!important;
                        transition:color .15s!important;
                    }
                    .fi-sidebar-nav .fi-sidebar-item-label{
                        color:rgba(255,255,255,.6)!important;
                        font-size:13px!important;
                        font-weight:500!important;
                        transition:color .15s!important;
                    }
                    .fi-sidebar-nav .fi-sidebar-item-btn:hover{background:rgba(255,255,255,.07)!important;}
                    .fi-sidebar-nav .fi-sidebar-item-btn:hover .fi-icon{color:#E11D48!important;}
                    .fi-sidebar-nav .fi-sidebar-item-btn:hover .fi-sidebar-item-label{color:#fff!important;}

                    .fi-sidebar-nav .fi-sidebar-item.fi-active>.fi-sidebar-item-btn{background:rgba(225,29,72,.13)!important;}
                    .fi-sidebar-nav .fi-sidebar-item.fi-active>.fi-sidebar-item-btn .fi-icon{color:#E11D48!important;}
                    .fi-sidebar-nav .fi-sidebar-item.fi-active>.fi-sidebar-item-btn .fi-sidebar-item-label{color:#fff!important;font-weight:700!important;}
                    .fi-sidebar-database-notifications-btn{color:#fff!important;width:100%!important;}
                    .fi-sidebar-database-notifications-btn-label{color:#fff!important;font-size:13px!important;font-weight:500!important;}
                    .fi-sidebar-database-notifications-btn svg{color:#fff!important;}
                    .fi-user-menu{display:none!important;}
                    #yr-collapsed-icon{display:none;align-items:center;justify-content:center;padding:8px;}
                    .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-header-logo-ctn{display:none!important;}
                    .fi-sidebar:not(.fi-sidebar-open) #yr-collapsed-icon{display:flex!important;}
                    /* ── Colapsado ── */
                    .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-group-items{margin-left:0!important;}
                    .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-item-btn{justify-content:center!important;width:40px!important;margin:2px auto!important;padding:8px!important;}
                    .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-item-label{display:none!important;}
                    .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-group-label{display:none!important;}
                    .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-group-button{display:none!important;}
                    .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-database-notifications-btn-label{display:none!important;}
                    .fi-section,.fi-ta-ctn,.fi-card,.fi-wi-card{background:rgba(255,255,255,.7)!important;backdrop-filter:blur(12px);border-radius:20px!important;border:1px solid rgba(226,232,240,.8)!important;box-shadow:0 10px 30px -10px rgba(15,23,42,.05)!important;}
                    .fi-ta-cell,.fi-ta-header-cell{padding-top:14px!important;padding-bottom:14px!important;padding-left:16px!important;padding-right:16px!important;}
                    .fi-main{max-width:100%!important;width:100%!important;}
                    .fi-page{max-width:100%!important;width:100%!important;}
                </style>

                <div id="yr-collapsed-icon">
                    <div style="width:36px;height:36px;background:linear-gradient(135deg,#0F172A,#2563EB);border-radius:10px;display:flex;align-items:center;justify-content:center;">
                        <svg viewBox="0 0 32 32" fill="none" width="20" height="20"><path d="M4 28V14l12-9 12 9v14H20v-7h-8v7H4z" fill="#fff"/></svg>
                    </div>
                </div>

                <script>
                (function(){
                    /* ── Inyectar estilos menú al HEAD — gana a Tailwind JIT ── */
                    var s=document.createElement("style");
                    s.id="yr-menu-style";
                    s.textContent=`
                        .fi-sidebar-group-items{border:0!important;border-left:0!important;margin-left:0!important;padding-left:0!important;}
                        .fi-sidebar-item-btn{display:flex!important;align-items:center!important;gap:10px!important;margin:1px 8px!important;padding:9px 12px!important;border-radius:8px!important;background:transparent!important;border:0!important;box-shadow:none!important;transition:background .15s!important;}
                        .fi-sidebar-item-btn::before,.fi-sidebar-item-btn::after{display:none!important;content:none!important;}
                        .fi-sidebar-item-btn .fi-icon{display:flex!important;color:rgba(255,255,255,.45)!important;width:17px!important;height:17px!important;flex-shrink:0!important;transition:color .15s!important;}
                        .fi-sidebar-item-label{color:rgba(255,255,255,.65)!important;font-size:13px!important;font-weight:500!important;transition:color .15s!important;}
                        .fi-sidebar-item-btn:hover{background:rgba(255,255,255,.07)!important;}
                        .fi-sidebar-item-btn:hover .fi-icon{color:#E11D48!important;}
                        .fi-sidebar-item-btn:hover .fi-sidebar-item-label{color:#fff!important;}
                        .fi-sidebar-item.fi-active>.fi-sidebar-item-btn{background:rgba(225,29,72,.13)!important;}
                        .fi-sidebar-item.fi-active>.fi-sidebar-item-btn .fi-icon{color:#E11D48!important;}
                        .fi-sidebar-item.fi-active>.fi-sidebar-item-btn .fi-sidebar-item-label{color:#fff!important;font-weight:700!important;}
                        .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-group-items{margin-left:0!important;}
                        .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-item-btn{justify-content:center!important;width:40px!important;margin:2px auto!important;padding:8px!important;}
                        .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-item-label{display:none!important;}
                    `;
                    if(!document.getElementById("yr-menu-style")) document.head.appendChild(s);
                    var _obs=null;
                    function updateFooter(){
                        var sidebar=document.querySelector(".fi-sidebar");
                        var footer=document.getElementById("yr-footer");
                        if(!sidebar||!footer) return;
                        var w=sidebar.offsetWidth||0;
                        footer.style.left=w+"px";
                    }
                    function update(){
                        var sidebar=document.querySelector(".fi-sidebar");
                        var icon=document.getElementById("yr-collapsed-icon");
                        if(!sidebar||!icon) return;
                        var isOpen=sidebar.classList.contains("fi-sidebar-open");
                        var logo=document.querySelector(".fi-sidebar-header-logo-ctn");
                        if(logo) logo.style.display=isOpen?"":"none";
                        icon.style.display=isOpen?"none":"flex";
                        updateFooter();
                    }
                    function moveIcon(){
                        var icon=document.getElementById("yr-collapsed-icon");
                        var header=document.querySelector(".fi-sidebar-header");
                        if(icon&&header&&!header.contains(icon)) header.prepend(icon);
                    }
                    function moveUserBar(){
                        var bar=document.getElementById("yr-user-bar");
                        var sidebar=document.querySelector(".fi-sidebar");
                        if(!bar||!sidebar) return;
                        if(sidebar.lastElementChild!==bar) sidebar.appendChild(bar);
                    }
                    function init(){
                        var sidebar=document.querySelector(".fi-sidebar");
                        if(!sidebar){setTimeout(init,300);return;}
                        moveIcon(); moveUserBar(); update();
                        if(_obs) _obs.disconnect();
                        _obs=new MutationObserver(function(){moveIcon();moveUserBar();update();});
                        _obs.observe(sidebar,{attributes:true,childList:true,attributeFilter:["class"],subtree:false});
                        var ro=new ResizeObserver(function(){updateFooter();});
                        ro.observe(sidebar);
                    }
                    document.readyState==="loading"
                        ?document.addEventListener("DOMContentLoaded",function(){setTimeout(init,200);})
                        :setTimeout(init,200);
                    document.addEventListener("livewire:navigated",function(){setTimeout(init,200);setTimeout(moveUserBar,400);});
                    setInterval(function(){update();moveUserBar();},400);
                })();
                </script>
                '
            )
            ->renderHook(
                PanelsRenderHook::SIDEBAR_NAV_END,
                function (): string {
                    $token   = csrf_token();
                    $user    = Auth::user();
                    $name    = $user?->name ?? 'Admin';
                    $initial = strtoupper(substr($name, 0, 1));
                    $email   = $user?->email ?? '';
                    return <<<HTML
                    <div id="yr-user-bar" style="padding:10px 12px;border-top:1px solid rgba(255,255,255,.1);display:flex;align-items:center;gap:10px;">
                        <div style="width:34px;height:34px;min-width:34px;background:linear-gradient(135deg,#E11D48,#be123c);border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:900;color:#fff;">{$initial}</div>
                        <div id="yr-user-info" style="flex:1;overflow:hidden;">
                            <div style="font-size:12px;font-weight:800;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{$name}</div>
                            <div style="font-size:10px;color:rgba(255,255,255,.5);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{$email}</div>
                        </div>
                        <a href="#"
                           onclick="event.preventDefault();document.getElementById('yr-lf').submit();"
                           title="Cerrar sesión"
                           style="width:32px;height:32px;min-width:32px;background:rgba(225,29,72,.15);border:1px solid rgba(225,29,72,.3);border-radius:8px;display:flex;align-items:center;justify-content:center;color:#E11D48;cursor:pointer;"
                           onmouseover="this.style.background='#E11D48';this.style.color='#fff';"
                           onmouseout="this.style.background='rgba(225,29,72,.15)';this.style.color='#E11D48';">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="15" height="15">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                        </a>
                        <form id="yr-lf" method="POST" action="/admin/logout" style="display:none;">
                            <input type="hidden" name="_token" value="{$token}">
                        </form>
                    </div>
HTML;
                }
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
                .fi-sidebar:not(.fi-sidebar-open) #yr-user-info{display:none!important;}
                .fi-sidebar-database-notifications-btn{display:flex!important;align-items:center!important;gap:10px!important;width:100%!important;padding:10px 12px!important;color:#fff!important;background:transparent!important;border:none!important;}
                .fi-sidebar-database-notifications-btn:hover{background:rgba(255,255,255,.08)!important;}
                .fi-sidebar-database-notifications-btn svg,.fi-sidebar-database-notifications-btn .fi-icon{color:#E11D48!important;}
                .fi-sidebar-database-notifications-btn-label{color:#fff!important;font-size:13px!important;font-weight:500!important;}
                .fi-sidebar-database-notifications-btn .fi-badge{background:#E11D48!important;color:#fff!important;}
                </style>
                <footer id="yr-footer" style="position:fixed;bottom:0;left:0;right:0;z-index:60;transition:left .3s ease;padding:12px 2.5rem;border-top:1px solid #112240;background:linear-gradient(135deg,#0F172A,#1e3a8a);">
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

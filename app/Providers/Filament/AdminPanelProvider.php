<?php

/*
|--------------------------------------------------------------------------
| YarOM ERP v1.6 - limpio, un solo SIDEBAR_NAV_END
|--------------------------------------------------------------------------
*/

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
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
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
                NavigationGroup::make('Contratación')->icon('heroicon-o-scale'),
                NavigationGroup::make('Cobros')->icon('heroicon-o-banknotes'),
                NavigationGroup::make('Operativo')->icon('heroicon-o-building-office-2'),
                NavigationGroup::make('Financiero')->icon('heroicon-o-banknotes'),
                NavigationGroup::make('CRM')->icon('heroicon-o-users'),
                NavigationGroup::make('Configuración')->icon('heroicon-o-cog-6-tooth')->collapsed(true),
            ])
            ->sidebarCollapsibleOnDesktop()
            ->brandLogo(fn () => new HtmlString('
                <div style="display:flex;align-items:center;gap:12px;">
                    <div style="width:34px;height:34px;background:linear-gradient(135deg,#0F172A,#2563EB);border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 4px 10px rgba(15,23,42,0.25);">
                        <svg viewBox="0 0 32 32" fill="none" width="20" height="20"><path d="M4 28V14l12-9 12 9v14H20v-7h-8v7H4z" fill="#fff"/></svg>
                    </div>
                    <div style="display:flex;flex-direction:column;line-height:1.15;">
                        <span style="font-size:17px;font-weight:900;letter-spacing:-.04em;color:#0F172A;text-transform:uppercase;">YAROM <span style="color:#E11D48;">INMO</span>BILIARIA</span>
                        <span style="font-size:11px;font-weight:600;letter-spacing:0.05em;color:#64748B;text-transform:uppercase;">Serviarrendar S.A.S</span>
                    </div>
                </div>
            '))
            ->colors([
                'primary' => Color::hex('#0e01a3ff'),
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
                    .fi-btn-color-primary{background:linear-gradient(135deg,#E11D48,#2563EB)!important;border:none!important;font-weight:800!important;border-radius:10px!important;color:#fff!important;}
                    .fi-btn-color-primary:hover{opacity:.88!important;transform:translateY(-1px)!important;}
                    .fi-section,.fi-ta-ctn,.fi-card,.fi-wi-card{background:rgba(255,255,255,.7)!important;backdrop-filter:blur(12px);border-radius:20px!important;border:1px solid rgba(226,232,240,.8)!important;box-shadow:0 10px 30px -10px rgba(15,23,42,.05)!important;}
                    .fi-sidebar-item-icon{color:#E11D48!important;transition:transform .2s ease,color .2s ease!important;}
                    .fi-sidebar-item-button:hover .fi-sidebar-item-icon{transform:scale(1.1);color:#2563EB!important;}
                    .fi-sidebar-group-label{font-weight:800!important;text-transform:uppercase;letter-spacing:.1em;font-size:10px;color:#E11D48!important;display:flex;align-items:center;gap:6px;}
                    .fi-sidebar-group-label svg,.fi-sidebar-group-label [data-icon]{color:#E11D48!important;width:14px!important;height:14px!important;}
                    .fi-sidebar-group-items{border-left:2px solid rgba(225,29,72,.3)!important;margin-left:20px;padding-left:8px;}
                </style>
                '
            )
            ->renderHook(
                PanelsRenderHook::SIDEBAR_NAV_END,
                function (): string {
                    $token = csrf_token();
                    return <<<HTML
                    <div id="yr-lw" style="display:none;justify-content:center;padding:16px;border-top:1px solid rgba(225,29,72,0.15);margin-top:auto;">
                        <a href="#"
                           onclick="event.preventDefault();document.getElementById('yr-lf').submit();"
                           title="Cerrar sesión"
                           style="width:42px;height:42px;background:#fef2f2;border:1px solid #fee2e2;border-radius:12px;display:flex;align-items:center;justify-content:center;color:#E11D48;cursor:pointer;"
                           onmouseover="this.style.background='#E11D48';this.style.color='#fff';"
                           onmouseout="this.style.background='#fef2f2';this.style.color='#E11D48';">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                        </a>
                        <form id="yr-lf" method="POST" action="/admin/logout" style="display:none;">
                            <input type="hidden" name="_token" value="{$token}">
                        </form>
                    </div>
                    <script>
                    (function(){
                        function chk(){
                            var s=document.querySelector(".fi-sidebar"),b=document.getElementById("yr-lw");
                            if(!s||!b)return;
                            b.style.display=s.offsetWidth<150?"flex":"none";
                        }
                        var ob=new MutationObserver(function(){setTimeout(chk,200);});
                        function init(){
                            var s=document.querySelector(".fi-sidebar");
                            if(s){ob.observe(s,{attributes:true,attributeFilter:["class","style"]});chk();}
                            else{setTimeout(init,300);}
                        }
                        document.readyState==="loading"?document.addEventListener("DOMContentLoaded",init):init();
                    })();
                    </script>
HTML;
                }
            )
            ->renderHook(
                PanelsRenderHook::GLOBAL_SEARCH_BEFORE,
                fn (): string => '
                <div style="display:flex;align-items:center;gap:12px;margin-right:16px;">
                    <div style="background:#fff;border:1px solid #e2e8f0;padding:6px 14px;border-radius:14px;display:flex;flex-direction:column;align-items:center;">
                        <span id="yr-clock" style="font-size:13px;font-weight:900;color:#0f172a;font-variant-numeric:tabular-nums;">' . now()->format('H:i:s') . '</span>
                        <span style="font-size:9px;color:#64748B;font-weight:700;text-transform:uppercase;">' . now()->format('d/m/Y') . '</span>
                    </div>
                    <div style="background:#fff;border:1px solid #e2e8f0;padding:4px 14px 4px 6px;border-radius:14px;display:flex;align-items:center;gap:10px;">
                        <div style="width:30px;height:30px;background:linear-gradient(135deg,#0f172a,#2563EB);color:white;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:900;">' . substr(Auth::user()?->name ?? 'A', 0, 1) . '</div>
                        <div style="display:flex;flex-direction:column;line-height:1.1;">
                            <span style="font-size:12px;font-weight:800;color:#0F172A;">' . (Auth::user()?->name ?? 'Admin') . '</span>
                            <span style="font-size:9px;font-weight:700;color:#22c55e;text-transform:uppercase;display:flex;align-items:center;gap:4px;"><span style="width:5px;height:5px;background:#22c55e;border-radius:50%;"></span> En línea</span>
                        </div>
                    </div>
                </div>
                <script>setInterval(function(){var c=document.getElementById("yr-clock");if(c)c.innerText=new Date().toLocaleTimeString("en-GB",{hour12:false});},1000);</script>
                '
            )
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn (): string => '
                <style>.fi-main-ctn{padding-bottom:60px!important;}</style>
                <footer style="position:fixed;bottom:0;left:0;right:0;z-index:40;padding:12px 2.5rem;border-top:1px solid #112240;background:#0A192F;box-shadow:0 -10px 25px rgba(0,0,0,0.1);">
                    <div style="display:flex;justify-content:space-between;align-items:center;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;">
                        <div style="color:rgba(255,255,255,0.8);">© ' . date('Y') . ' <span style="color:#fff;">YarOM ERP</span> <span style="color:#2563EB;margin:0 8px;">|</span> <span style="color:#64748B;">Infraestructura Cloud Engine</span></div>
                        <div style="display:flex;align-items:center;gap:12px;">
                            <span style="color:#475569;">Desarrollado por</span>
                            <a href="https://linkedin.com/in/jmromeror87" target="_blank" style="text-decoration:none;display:flex;align-items:center;gap:6px;">
                                <span style="color:#fff;font-weight:900;">ING. JHOAN ROMERO</span>
                                <span style="font-size:8px;background:#2563EB;color:#fff;padding:2px 10px;border-radius:6px;">ARCHITECT</span>
                            </a>
                        </div>
                    </div>
                </footer>
                '
            )
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->pages([\Filament\Pages\Dashboard::class])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([Authenticate::class]);
    }
}
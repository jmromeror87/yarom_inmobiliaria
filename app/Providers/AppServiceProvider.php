<?php
/*
|--------------------------------------------------------------------------
| YarOM ERP - Soluciones de Gestión
|--------------------------------------------------------------------------
| Proyecto privado desarrollado por:
| Ingeniero Jhoan Romero Rivera
| LinkedIn: https://linkedin.com/in/jmromeror87
|
| Módulo: \1
| Archivo: AppServiceProvider.php
| Fecha: CURRENT_DAY/05/2026
| Versión: v1.0
|--------------------------------------------------------------------------
*/
        

namespace App\Providers;

use App\Models\OwnerLiquidation;
use App\Models\RentBill;
use App\Observers\OwnerLiquidationObserver;
use App\Observers\RentBillObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(\App\Services\WhatsAppService::class);
    }

    public function register_old(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RentBill::observe(RentBillObserver::class);
        OwnerLiquidation::observe(OwnerLiquidationObserver::class);
    }
}
// en el método register():
// $this->app->singleton(\App\Services\WhatsAppService::class);

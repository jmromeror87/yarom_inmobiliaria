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
use Livewire\Livewire;

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

        // Sanitize all strings in Livewire response payload before JSON encoding
        // Prevents InvalidArgumentException: Malformed UTF-8 characters from DB data
        Livewire::listen('response', function ($payload) {
            return function (&$forward) {
                $before = json_encode($forward);
                $forward = AppServiceProvider::sanitizeUtf8Recursive($forward);
                $after = json_encode($forward);
                \Illuminate\Support\Facades\Log::info('livewire_response_hook', [
                    'before_ok' => $before !== false,
                    'after_ok'  => $after !== false,
                ]);
            };
        });
    }

    public static function sanitizeUtf8Recursive(mixed $value): mixed
    {
        if (is_string($value)) {
            return mb_scrub($value, 'UTF-8');
        }
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $value[$k] = static::sanitizeUtf8Recursive($v);
            }
        }
        return $value;
    }
}
// en el método register():
// $this->app->singleton(\App\Services\WhatsAppService::class);

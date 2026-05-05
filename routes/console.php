<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Generar facturas el día 1 de cada mes a las 7am
\Illuminate\Support\Facades\Schedule::job(new \App\Jobs\GenerarFacturasMensuales)
    ->monthlyOn(1, '07:00')
    ->name('generar-facturas-mensuales')
    ->withoutOverlapping();

// Verificar mora diariamente a las 8am
\Illuminate\Support\Facades\Schedule::call(function () {
    \App\Jobs\VerificarMoraJob::dispatch();
})->dailyAt('08:00')->name('verificar-mora');

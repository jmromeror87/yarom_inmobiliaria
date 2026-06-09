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
| Archivo: web.php
| Fecha: CURRENT_DAY/05/2026
| Versión: v1.0
|--------------------------------------------------------------------------
*/
    

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// ── PDF Contratos ──────────────────────────────────────────────
Route::get('/admin/contratos/{contract}/pdf', [\App\Http\Controllers\ContractPdfController::class, 'download'])
    ->middleware(['web', 'auth'])
    ->name('contrato.pdf');
Route::get('/admin/contratos-arriendo/{contract}/pdf', [\App\Http\Controllers\RentalContractPdfController::class, 'download'])->name('contrato.arriendo.pdf');
Route::get('/admin/actas/{handover}/pdf', [\App\Http\Controllers\PropertyHandoverPdfController::class, 'download'])->name('acta.entrega.pdf');
Route::post('/admin/actas/{handover}/firma', [App\Http\Controllers\HandoverSignatureController::class, 'store'])->name('acta.firma')->middleware('web');
Route::get('/admin/facturas/{bill}/pdf', [App\Http\Controllers\RentBillPdfController::class, 'download'])->name('factura.pdf')->middleware('web');

// ── Estudio de crédito Sudamericana (público, sin auth) ─────────────────
Route::get('/estudio/{token}',  [\App\Http\Controllers\EstudioController::class, 'show'])->name('estudio.show');
Route::post('/estudio/{token}', [\App\Http\Controllers\EstudioController::class, 'store'])->name('estudio.store');

// ── Acta de entrega pública (sin auth — acceso por token) ───────────────
Route::get('/acta/{token}',          [\App\Http\Controllers\ActaPublicaController::class, 'show'])->name('acta.publica');
Route::post('/acta/{token}/guardar', [\App\Http\Controllers\ActaPublicaController::class, 'guardar'])->name('acta.publica.guardar');
Route::post('/acta/{token}/firma',   [\App\Http\Controllers\ActaPublicaController::class, 'guardarFirma'])->name('acta.publica.firma');
Route::post('/acta/{token}/foto',    [\App\Http\Controllers\ActaPublicaController::class, 'subirFoto'])->name('acta.publica.foto');

// ── Reportes exportables (requiere auth) ────────────────────────────────
Route::get('/admin/reportes/{tipo}', [\App\Http\Controllers\ReportesController::class, 'descargar'])
    ->middleware(['web', 'auth'])
    ->name('reportes.descargar');

// ── Informes contables — descarga por ruta (evita conflicto wire:click + JSON) ──
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/admin/informes-contables/excel', [\App\Http\Controllers\InformeContableController::class, 'excel'])
        ->name('informes.excel');
    Route::get('/admin/informes-contables/pdf', [\App\Http\Controllers\InformeContableController::class, 'pdf'])
        ->name('informes.pdf');
});

// ── Invitación de usuarios (público, sin auth) ──────────────────────────
Route::get('/invitacion/{token}',  [\App\Http\Controllers\InvitacionController::class, 'show'])->name('invitacion.show');
Route::post('/invitacion/{token}', [\App\Http\Controllers\InvitacionController::class, 'store'])->name('invitacion.store');

// ── Pagos en línea (público, sin auth) ──────────────────────────────────
Route::get('/pagar/resultado',  [\App\Http\Controllers\PaymentController::class, 'resultado'])->name('payment.resultado');
Route::get('/pagar/{token}',    [\App\Http\Controllers\PaymentController::class, 'show'])->name('payment.show');
Route::post('/webhooks/wompi',  [\App\Http\Controllers\PaymentController::class, 'webhook'])->name('payment.webhook');

// ── PDFs Liquidaciones Propietario (con auth admin) ─────────────────────
Route::middleware(['auth'])->group(function () {
    Route::get('/admin/liquidaciones/{liquidation}/pdf', [\App\Http\Controllers\OwnerLiquidationPdfController::class, 'individual'])->name('liquidacion.pdf');
    Route::get('/admin/liquidaciones/reporte/{mes}/{anio}', [\App\Http\Controllers\OwnerLiquidationPdfController::class, 'reporte'])->name('liquidacion.reporte.pdf');
});

// ── Portal del Propietario (acceso por token, sin login) ─────────────────
Route::get('/propietario/{token}', [\App\Http\Controllers\PortalPropietarioController::class, 'show'])->name('portal.propietario');


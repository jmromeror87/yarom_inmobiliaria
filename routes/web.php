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

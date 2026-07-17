<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fecha real en que el inquilino recibió el inmueble (acta de entrega
 * cerrada). Ancla el cobro del contrato: si la entrega ocurre después de
 * la firma, la primera factura no debe cobrarse desde el día pactado del
 * mes sino desde esta fecha + días de gracia.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('rental_contracts', 'fecha_entrega_efectiva')) {
            Schema::table('rental_contracts', function (Blueprint $table) {
                $table->date('fecha_entrega_efectiva')->nullable()->after('fecha_fin');
            });
        }
    }

    public function down(): void
    {
        Schema::table('rental_contracts', function (Blueprint $table) {
            $table->dropColumn('fecha_entrega_efectiva');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Día de pago mensual pactado con el arrendatario (1-31), capturado al crear
 * el tercero. Sirve como valor por defecto para RentalContract.dia_pago —
 * la fuente de verdad para facturación sigue siendo el contrato (un mismo
 * inquilino podría tener contratos distintos con fechas distintas), pero
 * este campo evita tener que repetir el dato cada vez.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('thirds', function (Blueprint $table) {
            $table->unsignedTinyInteger('dia_pago')->nullable()->after('es_arrendatario');
        });
    }

    public function down(): void
    {
        Schema::table('thirds', function (Blueprint $table) {
            $table->dropColumn('dia_pago');
        });
    }
};

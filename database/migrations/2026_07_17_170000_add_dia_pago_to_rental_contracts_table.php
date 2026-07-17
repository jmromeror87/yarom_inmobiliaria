<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * El sistema calculaba la fecha límite de pago de TODAS las facturas con
 * un solo día global (Company.dia_corte_mensual), ignorando que cada
 * contrato puede tener su propio día de pago pactado — como es el caso
 * real de los contratos migrados de Victoria (cada arrendatario paga un
 * día distinto del mes). `dia_pago` es nullable: si no se define, el
 * sistema sigue usando el día global de la empresa (comportamiento actual
 * sin cambios para los contratos que no lo necesiten).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rental_contracts', function (Blueprint $table) {
            $table->unsignedTinyInteger('dia_pago')->nullable()->after('fecha_fin');
        });
    }

    public function down(): void
    {
        Schema::table('rental_contracts', function (Blueprint $table) {
            $table->dropColumn('dia_pago');
        });
    }
};

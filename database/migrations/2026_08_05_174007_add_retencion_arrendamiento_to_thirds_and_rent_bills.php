<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('thirds', function (Blueprint $table) {
            $table->boolean('es_agente_retenedor')->default(false)->after('tipo_persona')
                ->comment('Arrendatario persona jurídica que retiene en la fuente sobre el canon');
            $table->decimal('tarifa_retefuente_arrendamiento', 5, 2)->nullable()->after('es_agente_retenedor')
                ->comment('% que retiene este arrendatario — si es null, se usa la tarifa global de la empresa');
        });

        Schema::table('rent_bills', function (Blueprint $table) {
            $table->decimal('retencion_practicada', 12, 2)->default(0)->after('saldo_pendiente')
                ->comment('Retención en la fuente que el arrendatario (persona jurídica) practica sobre esta factura');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('thirds', function (Blueprint $table) {
            $table->dropColumn(['es_agente_retenedor', 'tarifa_retefuente_arrendamiento']);
        });

        Schema::table('rent_bills', function (Blueprint $table) {
            $table->dropColumn('retencion_practicada');
        });
    }
};

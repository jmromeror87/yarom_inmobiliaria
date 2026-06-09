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
        Schema::table('administration_contracts', function (Blueprint $table) {
            $table->decimal('cuota_administracion_valor', 12, 2)->nullable()->default(0)->change();
            $table->decimal('comision_porcentaje', 5, 2)->nullable()->default(0)->change();
            $table->decimal('comision_venta_porcentaje', 5, 2)->nullable()->default(0)->change();
            $table->decimal('canon_pactado', 12, 2)->nullable()->change();
            $table->tinyInteger('dias_aviso_terminacion')->nullable()->default(30)->change();
        });
    }

    public function down(): void
    {
        Schema::table('administration_contracts', function (Blueprint $table) {
            $table->decimal('cuota_administracion_valor', 12, 2)->default(0)->change();
        });
    }
};

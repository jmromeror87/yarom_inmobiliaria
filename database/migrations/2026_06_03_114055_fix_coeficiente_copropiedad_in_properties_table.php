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
        Schema::table('properties', function (Blueprint $table) {
            // decimal(6,4) solo llega a 99.9999 — cambiamos a decimal(8,4) para soportar hasta 9999.9999
            $table->decimal('coeficiente_copropiedad', 8, 4)->nullable()->change();
            $table->decimal('porcentaje_propiedad', 8, 4)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->decimal('coeficiente_copropiedad', 6, 4)->nullable()->change();
            $table->decimal('porcentaje_propiedad', 6, 4)->nullable()->change();
        });
    }
};

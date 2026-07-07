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
            if (Schema::hasColumn('properties', 'coeficiente_copropiedad')) {
                $table->decimal('coeficiente_copropiedad', 8, 4)->nullable()->change();
            } else {
                $table->decimal('coeficiente_copropiedad', 8, 4)->nullable();
            }
            if (Schema::hasColumn('properties', 'porcentaje_propiedad')) {
                $table->decimal('porcentaje_propiedad', 8, 4)->nullable()->change();
            } else {
                $table->decimal('porcentaje_propiedad', 8, 4)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            if (Schema::hasColumn('properties', 'coeficiente_copropiedad')) {
                $table->decimal('coeficiente_copropiedad', 6, 4)->nullable()->change();
            }
            if (Schema::hasColumn('properties', 'porcentaje_propiedad')) {
                $table->decimal('porcentaje_propiedad', 6, 4)->nullable()->change();
            }
        });
    }
};

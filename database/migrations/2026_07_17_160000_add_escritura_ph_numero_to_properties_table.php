<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `escritura_ph_numero` está en Property::$fillable y en PropertyForm desde
 * hace tiempo, pero nunca se creó en ninguna migración — el formulario
 * fallaba con "Column not found" al intentar guardar (SQLSTATE 42S22).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('properties', 'escritura_ph_numero')) {
            Schema::table('properties', function (Blueprint $table) {
                $table->string('escritura_ph_numero')->nullable()->after('porcentaje_propiedad');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('properties', 'escritura_ph_numero')) {
            Schema::table('properties', function (Blueprint $table) {
                $table->dropColumn('escritura_ph_numero');
            });
        }
    }
};

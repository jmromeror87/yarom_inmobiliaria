<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // tinyint max=127, pero el formulario permite 180 días → ampliar a smallint
        Schema::table('administration_contracts', function (Blueprint $table) {
            $table->smallInteger('dias_aviso_terminacion')->nullable()->default(30)->change();
        });

        // Agregar 'directa' al ENUM de tipo_garantia en thirds
        DB::statement("ALTER TABLE thirds MODIFY COLUMN tipo_garantia ENUM('fiador','poliza','deposito','ninguna','directa') NULL");
    }

    public function down(): void
    {
        Schema::table('administration_contracts', function (Blueprint $table) {
            $table->tinyInteger('dias_aviso_terminacion')->nullable()->default(30)->change();
        });

        DB::statement("ALTER TABLE thirds MODIFY COLUMN tipo_garantia ENUM('fiador','poliza','deposito','ninguna') NULL");
    }
};

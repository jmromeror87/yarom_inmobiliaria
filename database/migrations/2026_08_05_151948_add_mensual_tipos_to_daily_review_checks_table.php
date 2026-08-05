<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE daily_review_checks MODIFY COLUMN tipo ENUM('inquilino','propietario','comprobante_ingreso','comprobante_egreso','inquilino_mes','propietario_mes') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE daily_review_checks MODIFY COLUMN tipo ENUM('inquilino','propietario','comprobante_ingreso','comprobante_egreso') NOT NULL");
    }
};

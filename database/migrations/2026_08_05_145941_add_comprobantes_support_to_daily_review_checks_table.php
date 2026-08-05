<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('daily_review_checks', function (Blueprint $table) {
            $table->foreignId('entry_id')->nullable()->after('third_id')
                ->constrained('accounting_entries')->cascadeOnDelete();
        });

        // third_id ya no aplica a comprobantes de ingreso/egreso (esos usan
        // entry_id) — se relaja el FK a nullable para poder guardar esas filas.
        Schema::table('daily_review_checks', function (Blueprint $table) {
            $table->dropForeign(['third_id']);
        });
        DB::statement('ALTER TABLE daily_review_checks MODIFY COLUMN third_id BIGINT UNSIGNED NULL');
        Schema::table('daily_review_checks', function (Blueprint $table) {
            $table->foreign('third_id')->references('id')->on('thirds')->cascadeOnDelete();
        });

        DB::statement("ALTER TABLE daily_review_checks MODIFY COLUMN tipo ENUM('inquilino','propietario','comprobante_ingreso','comprobante_egreso') NOT NULL");

        Schema::table('daily_review_checks', function (Blueprint $table) {
            $table->unique(['fecha', 'tipo', 'entry_id'], 'daily_review_checks_fecha_tipo_entry_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_review_checks', function (Blueprint $table) {
            $table->dropUnique('daily_review_checks_fecha_tipo_entry_unique');
            $table->dropForeign(['entry_id']);
            $table->dropColumn('entry_id');
        });

        DB::statement("ALTER TABLE daily_review_checks MODIFY COLUMN tipo ENUM('inquilino','propietario') NOT NULL");
    }
};

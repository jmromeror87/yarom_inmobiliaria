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
        Schema::table('rent_bills', function (Blueprint $table) {
            $table->text('motivo_anulacion')->nullable()->after('estado');
            $table->foreignId('anulado_por_id')->nullable()->after('motivo_anulacion')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('anulado_en')->nullable()->after('anulado_por_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rent_bills', function (Blueprint $table) {
            $table->dropConstrainedForeignId('anulado_por_id');
            $table->dropColumn(['motivo_anulacion', 'anulado_en']);
        });
    }
};

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
            $table->boolean('contabilizado_via_historico')->default(false)->after('notas');
            $table->string('referencia_historico')->nullable()->after('contabilizado_via_historico');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rent_bills', function (Blueprint $table) {
            $table->dropColumn(['contabilizado_via_historico', 'referencia_historico']);
        });
    }
};

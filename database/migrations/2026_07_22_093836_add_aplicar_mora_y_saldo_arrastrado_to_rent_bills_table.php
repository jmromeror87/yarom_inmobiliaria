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
            $table->boolean('aplicar_mora')->default(true)->after('tasa_mora_diaria');
            $table->decimal('saldo_anterior_arrastrado', 12, 2)->default(0)->after('otros_cobros');
            $table->string('nota_saldo_arrastrado')->nullable()->after('saldo_anterior_arrastrado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rent_bills', function (Blueprint $table) {
            $table->dropColumn(['aplicar_mora', 'saldo_anterior_arrastrado', 'nota_saldo_arrastrado']);
        });
    }
};

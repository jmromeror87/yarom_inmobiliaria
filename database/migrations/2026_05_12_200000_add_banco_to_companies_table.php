<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('banco', 80)->nullable()->after('tasa_mora_mensual');
            $table->string('tipo_cuenta', 20)->nullable()->after('banco');
            $table->string('numero_cuenta', 30)->nullable()->after('tipo_cuenta');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['banco', 'tipo_cuenta', 'numero_cuenta']);
        });
    }
};

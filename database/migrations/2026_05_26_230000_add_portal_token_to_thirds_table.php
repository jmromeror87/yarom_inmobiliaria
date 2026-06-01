<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('thirds', function (Blueprint $table) {
            $table->string('portal_token', 80)->nullable()->unique()->after('notas');
            $table->timestamp('portal_token_generado_at')->nullable()->after('portal_token');
            $table->boolean('portal_activo')->default(false)->after('portal_token_generado_at');
        });
    }

    public function down(): void
    {
        Schema::table('thirds', function (Blueprint $table) {
            $table->dropColumn(['portal_token','portal_token_generado_at','portal_activo']);
        });
    }
};

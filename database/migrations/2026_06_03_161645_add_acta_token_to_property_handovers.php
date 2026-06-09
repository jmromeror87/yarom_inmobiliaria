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
        Schema::table('property_handovers', function (Blueprint $table) {
            $table->string('acta_token', 64)->nullable()->unique()->after('numero');
            $table->timestamp('acta_token_generado_at')->nullable()->after('acta_token');
            $table->timestamp('acta_completada_asesor_at')->nullable()->after('acta_token_generado_at');
            $table->timestamp('acta_completada_inquilino_at')->nullable()->after('acta_completada_asesor_at');
            $table->boolean('notificado_asesor')->default(false)->after('acta_completada_inquilino_at');
            $table->boolean('notificado_inquilino')->default(false)->after('notificado_asesor');
        });
    }

    public function down(): void
    {
        Schema::table('property_handovers', function (Blueprint $table) {
            $table->dropColumn([
                'acta_token', 'acta_token_generado_at',
                'acta_completada_asesor_at', 'acta_completada_inquilino_at',
                'notificado_asesor', 'notificado_inquilino',
            ]);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Cambiar enum por string para mayor flexibilidad
        Schema::table('requests', function (Blueprint $table) {
            $table->string('estado', 50)->default('radicada')->change();
            $table->string('tipo_aprobacion')->nullable()->after('estado');   // 'sura' | 'gerente_directo'
            $table->string('aprobado_por_id')->nullable()->after('tipo_aprobacion'); // user_id
            $table->text('justificacion_gerente')->nullable()->after('aprobado_por_id');
            $table->decimal('tarifa_estudio_cobrada', 12, 2)->nullable()->after('justificacion_gerente');
        });
    }

    public function down(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            $table->dropColumn(['tipo_aprobacion','aprobado_por_id','justificacion_gerente','tarifa_estudio_cobrada']);
        });
    }
};

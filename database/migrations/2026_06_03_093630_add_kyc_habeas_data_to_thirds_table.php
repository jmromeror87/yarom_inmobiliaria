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
        Schema::table('thirds', function (Blueprint $table) {
            // Expedición documento
            $table->string('lugar_expedicion')->nullable()->after('numero_documento');
            $table->date('fecha_expedicion')->nullable()->after('lugar_expedicion');

            // Habeas Data — Ley 1581/2012
            $table->boolean('habeas_data_aceptado')->default(false)->after('notas');
            $table->timestamp('habeas_data_fecha')->nullable()->after('habeas_data_aceptado');
            $table->string('habeas_data_metodo')->nullable()->after('habeas_data_fecha');

            // KYC / SARLAFT
            $table->boolean('kyc_completado')->default(false)->after('habeas_data_metodo');
            $table->date('kyc_fecha')->nullable()->after('kyc_completado');
            $table->string('kyc_actividad_economica')->nullable()->after('kyc_fecha');
            $table->text('kyc_declaracion_fondos')->nullable()->after('kyc_actividad_economica');
            $table->string('kyc_nivel_riesgo')->nullable()->after('kyc_declaracion_fondos');
            $table->string('kyc_screening_resultado')->nullable()->after('kyc_nivel_riesgo');
            $table->timestamp('kyc_screening_fecha')->nullable()->after('kyc_screening_resultado');

            // Estado expediente propietario
            $table->string('estado_expediente')->default('incompleto')->after('kyc_screening_fecha');
        });
    }

    public function down(): void
    {
        Schema::table('thirds', function (Blueprint $table) {
            $table->dropColumn([
                'lugar_expedicion', 'fecha_expedicion',
                'habeas_data_aceptado', 'habeas_data_fecha', 'habeas_data_metodo',
                'kyc_completado', 'kyc_fecha', 'kyc_actividad_economica',
                'kyc_declaracion_fondos', 'kyc_nivel_riesgo',
                'kyc_screening_resultado', 'kyc_screening_fecha',
                'estado_expediente',
            ]);
        });
    }
};

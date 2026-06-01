<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            // Tarifa que SURA cobra al arrendatario por estudio
            $table->decimal('sura_tarifa_estudio', 12, 2)->default(35000)->after('comision_administracion');
            // Tarifa que la inmobiliaria cobra al arrendatario por el estudio
            $table->decimal('inmobiliaria_tarifa_estudio', 12, 2)->default(60000)->after('sura_tarifa_estudio');
            // Si se aprueba sin SURA, tarifa que cobra la inmobiliaria
            $table->decimal('tarifa_estudio_directo', 12, 2)->default(50000)->after('inmobiliaria_tarifa_estudio');
            // Texto informativo en la cotización/contrato
            $table->string('nota_estudio_sura')->nullable()->after('tarifa_estudio_directo');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'sura_tarifa_estudio',
                'inmobiliaria_tarifa_estudio',
                'tarifa_estudio_directo',
                'nota_estudio_sura',
            ]);
        });
    }
};

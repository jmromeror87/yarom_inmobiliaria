<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->decimal('tarifa_seguro_sura', 5, 2)->default(2.50)->after('nota_estudio_sura')
                ->comment('% tarifa seguro arrendamiento SURA sobre el canon mensual');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('tarifa_seguro_sura');
        });
    }
};

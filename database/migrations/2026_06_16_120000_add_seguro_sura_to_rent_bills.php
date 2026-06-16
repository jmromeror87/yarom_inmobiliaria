<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rent_bills', function (Blueprint $table) {
            $table->decimal('valor_seguro_sura', 12, 2)->default(0)->after('otros_cobros')
                ->comment('Valor base del seguro SURA (% del canon)');
            $table->decimal('iva_seguro_sura', 12, 2)->default(0)->after('valor_seguro_sura')
                ->comment('IVA 19% sobre el valor del seguro SURA');
            $table->decimal('redondeo_seguro', 12, 2)->default(0)->after('iva_seguro_sura')
                ->comment('Diferencia entre cobro redondeado y valor exacto — ingreso de la inmobiliaria');
        });
    }

    public function down(): void
    {
        Schema::table('rent_bills', function (Blueprint $table) {
            $table->dropColumn(['valor_seguro_sura', 'iva_seguro_sura', 'redondeo_seguro']);
        });
    }
};

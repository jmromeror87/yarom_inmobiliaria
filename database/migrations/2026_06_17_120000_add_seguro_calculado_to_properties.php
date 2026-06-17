<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->decimal('valor_seguro_sura', 12, 2)->default(0)->after('canon_cobrado_inquilino')
                ->comment('Valor calculado del seguro SURA (canon * tarifa%). Se recalcula al guardar el inmueble.');
            $table->decimal('iva_seguro_sura', 12, 2)->default(0)->after('valor_seguro_sura')
                ->comment('IVA 19% sobre el seguro SURA. Se recalcula al guardar el inmueble.');
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn(['valor_seguro_sura', 'iva_seguro_sura']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('owner_liquidations', function (Blueprint $table) {
            $table->decimal('seguro_sura_deducido', 12, 2)->default(0)->after('retefuente_valor')
                ->comment('Valor seguro SURA + IVA cobrado al inquilino — la inmobiliaria lo paga a ASURA, no va al propietario');
        });
    }

    public function down(): void
    {
        Schema::table('owner_liquidations', function (Blueprint $table) {
            $table->dropColumn('seguro_sura_deducido');
        });
    }
};

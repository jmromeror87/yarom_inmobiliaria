<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Quitar del contrato
        Schema::table('rental_contracts', function (Blueprint $table) {
            $table->dropColumn('canon_cobrado_inquilino');
        });

        // Agregar al inmueble
        Schema::table('properties', function (Blueprint $table) {
            $table->boolean('tiene_seguro_sura')->default(false)->after('cuota_administracion')
                ->comment('Indica si el inmueble tiene seguro de arrendamiento SURA');
            $table->decimal('canon_cobrado_inquilino', 12, 2)->nullable()->after('tiene_seguro_sura')
                ->comment('Valor total cobrado al inquilino (canon + seguro + IVA redondeado). Manual.');
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn(['tiene_seguro_sura', 'canon_cobrado_inquilino']);
        });

        Schema::table('rental_contracts', function (Blueprint $table) {
            $table->decimal('canon_cobrado_inquilino', 12, 2)->nullable()->after('canon_mensual');
        });
    }
};

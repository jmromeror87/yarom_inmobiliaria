<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rental_contracts', function (Blueprint $table) {
            $table->decimal('canon_cobrado_inquilino', 12, 2)->nullable()->after('canon_mensual')
                ->comment('Valor total que se le cobra al inquilino (canon + seguro SURA redondeado). Se define manualmente en el contrato.');
        });
    }

    public function down(): void
    {
        Schema::table('rental_contracts', function (Blueprint $table) {
            $table->dropColumn('canon_cobrado_inquilino');
        });
    }
};

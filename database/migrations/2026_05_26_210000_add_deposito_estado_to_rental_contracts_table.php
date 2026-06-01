<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rental_contracts', function (Blueprint $table) {
            // 'pagado' | 'pendiente' | 'en_cartera' | 'exonerado'
            $table->string('estado_deposito')->default('pendiente')->after('deposito');
            $table->date('fecha_pago_deposito')->nullable()->after('estado_deposito');
            $table->decimal('deposito_pagado', 12, 2)->default(0)->after('fecha_pago_deposito');
            $table->text('notas_deposito')->nullable()->after('deposito_pagado');
        });
    }

    public function down(): void
    {
        Schema::table('rental_contracts', function (Blueprint $table) {
            $table->dropColumn(['estado_deposito','fecha_pago_deposito','deposito_pagado','notas_deposito']);
        });
    }
};

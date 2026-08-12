<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rental_contracts', function (Blueprint $table) {
            // Distinto de "admin_cobrada_por" (quién le COBRA la administración
            // al inquilino) — esto es cuando la INMOBILIARIA le PAGA la
            // administración al edificio/copropiedad por cuenta del
            // propietario, y por eso hay que descontárselo del giro.
            $table->decimal('admin_pagada_inmobiliaria_valor', 12, 2)->nullable()->default(0)
                ->after('admin_cobrada_por');
        });
    }

    public function down(): void
    {
        Schema::table('rental_contracts', function (Blueprint $table) {
            $table->dropColumn('admin_pagada_inmobiliaria_valor');
        });
    }
};

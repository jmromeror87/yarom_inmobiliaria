<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rental_contracts', function (Blueprint $table) {
            // Quién cobra la administración: 'inmobiliaria' | 'edificio' | 'ninguna'
            $table->string('admin_cobrada_por')->default('inmobiliaria')->after('cuota_administracion');
            // Si la cobra el edificio, la mora no aplica sobre cuota_administracion
            $table->boolean('mora_solo_sobre_canon')->default(false)->after('admin_cobrada_por');
        });
    }

    public function down(): void
    {
        Schema::table('rental_contracts', function (Blueprint $table) {
            $table->dropColumn(['admin_cobrada_por', 'mora_solo_sobre_canon']);
        });
    }
};

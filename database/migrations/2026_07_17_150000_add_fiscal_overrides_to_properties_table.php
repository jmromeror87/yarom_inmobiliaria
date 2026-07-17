<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Permite que un inmueble en particular se aparte del comportamiento fiscal
 * general del propietario (Third.requiere_iva / requiere_retefuente). Un
 * propietario puede declarar IVA por unos inmuebles y por otros no — estos
 * campos quedan en null por defecto (hereda del tercero); solo se marcan
 * cuando ese inmueble específico necesita comportarse distinto.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->boolean('requiere_iva_override')->nullable()->after('estado');
            $table->boolean('requiere_retefuente_override')->nullable()->after('requiere_iva_override');
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn(['requiere_iva_override', 'requiere_retefuente_override']);
        });
    }
};

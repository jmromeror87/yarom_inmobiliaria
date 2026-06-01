<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('thirds', function (Blueprint $table) {
            $table->boolean('requiere_iva')->default(false)->after('is_active');
            $table->boolean('requiere_retefuente')->default(false)->after('requiere_iva');
            $table->boolean('quiere_factura_electronica')->default(false)->after('requiere_retefuente');
            $table->decimal('tarifa_iva_pactada', 5, 2)->nullable()->after('quiere_factura_electronica');
            $table->decimal('tarifa_retefuente_pactada', 5, 2)->nullable()->after('tarifa_iva_pactada');
        });
    }

    public function down(): void
    {
        Schema::table('thirds', function (Blueprint $table) {
            $table->dropColumn([
                'requiere_iva',
                'requiere_retefuente',
                'quiere_factura_electronica',
                'tarifa_iva_pactada',
                'tarifa_retefuente_pactada',
            ]);
        });
    }
};

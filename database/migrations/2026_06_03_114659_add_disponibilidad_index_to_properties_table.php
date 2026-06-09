<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->index(['disponible_arriendo', 'estado'], 'idx_disponible_arriendo_estado');
            $table->index(['disponible_venta', 'estado'], 'idx_disponible_venta_estado');
            $table->index('ctl_tiene_limitacion', 'idx_ctl_limitacion');
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropIndex('idx_disponible_arriendo_estado');
            $table->dropIndex('idx_disponible_venta_estado');
            $table->dropIndex('idx_ctl_limitacion');
        });
    }
};

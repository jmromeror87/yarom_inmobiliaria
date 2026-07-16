<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('owner_liquidations', function (Blueprint $table) {
            $table->boolean('aplica_retefuente')->default(false)->after('iva_comision');
            $table->decimal('retefuente_valor', 12, 2)->default(0)->after('aplica_retefuente');
        });
    }

    public function down(): void
    {
        Schema::table('owner_liquidations', function (Blueprint $table) {
            $table->dropColumn(['aplica_retefuente', 'retefuente_valor']);
        });
    }
};

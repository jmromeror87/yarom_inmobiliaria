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
        Schema::table('administration_contracts', function (Blueprint $table) {
            $table->boolean('incluye_administracion')->default(false)->after('comision_porcentaje');
            $table->decimal('cuota_administracion_valor', 12, 2)->default(0)->after('incluye_administracion');
            $table->boolean('autoriza_venta')->default(false)->after('cuota_administracion_valor');
            $table->decimal('precio_venta_pactado', 14, 2)->nullable()->after('autoriza_venta');
        });
    }

    public function down(): void
    {
        Schema::table('administration_contracts', function (Blueprint $table) {
            $table->dropColumn([
                'incluye_administracion',
                'cuota_administracion_valor',
                'autoriza_venta',
                'precio_venta_pactado',
            ]);
        });
    }
};

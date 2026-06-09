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
            // Destino / uso del inmueble
            $table->string('destinacion')->nullable()->after('property_type_id');

            // CTL — limitación jurídica
            $table->boolean('ctl_tiene_limitacion')->default(false)->after('doc_certificado_libertad_fecha');
            $table->string('ctl_tipo_limitacion')->nullable()->after('ctl_tiene_limitacion');
            $table->text('ctl_observacion_limitacion')->nullable()->after('ctl_tipo_limitacion');

            // Recibo servicios públicos — detalle
            $table->string('doc_recibo_tipo')->nullable()->after('doc_recibo_servicios');
            $table->string('doc_recibo_periodo')->nullable()->after('doc_recibo_tipo');
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn([
                'destinacion',
                'ctl_tiene_limitacion', 'ctl_tipo_limitacion', 'ctl_observacion_limitacion',
                'doc_recibo_tipo', 'doc_recibo_periodo',
            ]);
        });
    }
};

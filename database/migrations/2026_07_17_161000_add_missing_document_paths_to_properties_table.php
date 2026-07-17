<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Estas 7 columnas están en Property::$fillable y se usan en el paso
 * "Documentos" del formulario del inmueble desde hace tiempo, pero nunca
 * se crearon en ninguna migración (ni siquiera en local) — al guardar un
 * inmueble con cualquier documento adjunto o servicios públicos fallaba
 * con "Column not found" (SQLSTATE 42S22).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $paths = [
                'doc_escritura_path',
                'doc_certificado_libertad_path',
                'doc_predial_path',
                'doc_paz_salvo_admin_path',
                'doc_propietario_path',
                'doc_recibo_servicios_path',
            ];
            foreach ($paths as $col) {
                if (! Schema::hasColumn('properties', $col)) {
                    $table->string($col)->nullable();
                }
            }
            if (! Schema::hasColumn('properties', 'servicios_publicos')) {
                $table->text('servicios_publicos')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $cols = [
                'doc_escritura_path',
                'doc_certificado_libertad_path',
                'doc_predial_path',
                'doc_paz_salvo_admin_path',
                'doc_propietario_path',
                'doc_recibo_servicios_path',
                'servicios_publicos',
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('properties', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};

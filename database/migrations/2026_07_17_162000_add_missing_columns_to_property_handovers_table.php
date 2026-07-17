<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Estas 4 columnas están en PropertyHandover::$fillable y se usan en el
 * flujo real de firma digital (SignHandover) y envío por WhatsApp desde
 * hace tiempo, pero nunca se crearon en ninguna migración — firmar un
 * acta de entrega fallaba con "Column not found" (SQLSTATE 42S22).
 * firma_digital_* son longText porque guardan la imagen completa en
 * base64 (data:image/png;base64,...), no solo una ruta de archivo.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('property_handovers', function (Blueprint $table) {
            if (! Schema::hasColumn('property_handovers', 'firma_digital_arrendatario')) {
                $table->longText('firma_digital_arrendatario')->nullable();
            }
            if (! Schema::hasColumn('property_handovers', 'firma_digital_asesor')) {
                $table->longText('firma_digital_asesor')->nullable();
            }
            if (! Schema::hasColumn('property_handovers', 'whatsapp_enviado')) {
                $table->boolean('whatsapp_enviado')->default(false);
            }
            if (! Schema::hasColumn('property_handovers', 'fecha_whatsapp_enviado')) {
                $table->dateTime('fecha_whatsapp_enviado')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('property_handovers', function (Blueprint $table) {
            $cols = ['firma_digital_arrendatario', 'firma_digital_asesor', 'whatsapp_enviado', 'fecha_whatsapp_enviado'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('property_handovers', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};

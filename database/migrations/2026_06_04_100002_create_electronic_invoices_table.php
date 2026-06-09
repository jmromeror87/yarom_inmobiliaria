<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('electronic_invoices', function (Blueprint $table) {
            $table->id();

            // Relación con la factura interna
            $table->foreignId('rent_bill_id')->constrained('rent_bills')->cascadeOnDelete();

            // Identificación DIAN
            $table->string('cufe', 200)->nullable()->unique();
            $table->string('numero_factura_dian', 50)->nullable(); // prefijo + consecutivo
            $table->unsignedInteger('consecutivo')->nullable();
            $table->string('prefijo', 10)->nullable();
            $table->string('qr_data', 500)->nullable();

            // Operador y ambiente
            $table->enum('operador', ['factus', 'dataico', 'facturatech']);
            $table->enum('ambiente', ['habilitacion', 'produccion'])->default('habilitacion');

            // Estado DIAN
            $table->enum('estado', [
                'pendiente',    // Aún no enviada
                'enviada',      // Enviada al operador, esperando DIAN
                'aceptada',     // DIAN aceptó
                'aceptada_con_notificacion', // Aceptada con observaciones
                'rechazada',    // DIAN rechazó
                'anulada',      // Nota crédito emitida
                'error',        // Error técnico al enviar
            ])->default('pendiente');

            // Respuesta del operador
            $table->json('respuesta_operador')->nullable();  // Raw response guardada
            $table->text('mensaje_dian')->nullable();        // Mensaje legible de la DIAN
            $table->string('codigo_dian', 20)->nullable();   // Código de respuesta DIAN

            // URLs de documentos
            $table->string('xml_url', 500)->nullable();
            $table->string('pdf_url', 500)->nullable();
            $table->string('attached_document_url', 500)->nullable();

            // Anulación
            $table->string('cufe_nota_credito', 200)->nullable();
            $table->text('razon_anulacion')->nullable();
            $table->foreignId('anulado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('anulado_en')->nullable();

            // Reintentos
            $table->unsignedTinyInteger('intentos')->default(0);
            $table->timestamp('proximo_reintento')->nullable();
            $table->text('ultimo_error')->nullable();

            // Trazabilidad
            $table->foreignId('emitido_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('emitido_en')->nullable();
            $table->timestamp('aceptada_en')->nullable();

            $table->timestamps();

            $table->index(['rent_bill_id', 'estado']);
            $table->index(['estado', 'proximo_reintento']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('electronic_invoices');
    }
};

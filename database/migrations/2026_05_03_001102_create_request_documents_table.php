<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('request_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('requests')->cascadeOnDelete();
            $table->foreignId('request_third_id')->nullable()->constrained('request_thirds')->nullOnDelete();

            $table->enum('tipo_documento', [
                'cedula',
                'desprendible_nomina',
                'extracto_bancario',
                'certificado_ingresos',
                'declaracion_renta',
                'carta_laboral',
                'camara_comercio',
                'rut',
                'referencia_personal',
                'referencia_comercial',
                'promesa_compraventa',
                'otro',
            ]);

            $table->string('nombre_original', 200);
            $table->string('path', 300);
            $table->string('extension', 10)->nullable();
            $table->unsignedBigInteger('tamanio_bytes')->nullable();

            $table->enum('estado_documento', [
                'pendiente', 'recibido', 'verificado', 'rechazado'
            ])->default('pendiente');

            $table->text('notas')->nullable();
            $table->foreignId('subido_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['request_id', 'tipo_documento']);
        });
    }
    public function down(): void { Schema::dropIfExists('request_documents'); }
};

<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained('properties')->cascadeOnDelete();
            $table->enum('tipo', [
                'escritura',
                'certificado_libertad',
                'predial',
                'paz_salvo_admin',
                'documento_propietario',
                'recibo_servicios',
                'otro',
            ]);
            $table->string('nombre_original', 200);
            $table->string('path', 300);
            $table->string('extension', 10)->nullable();
            $table->unsignedBigInteger('tamanio_bytes')->nullable();
            $table->text('notas')->nullable();
            $table->foreignId('subido_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['property_id', 'tipo']);
        });
    }
    public function down(): void { Schema::dropIfExists('property_documents'); }
};

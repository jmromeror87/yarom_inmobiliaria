<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('request_sura_studies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('requests')->cascadeOnDelete();

            // ── Envío ───────────────────────────────────────────
            $table->enum('canal_envio', ['whatsapp', 'email', 'presencial'])->default('whatsapp');
            $table->timestamp('fecha_envio')->nullable();
            $table->foreignId('enviado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->text('mensaje_enviado')->nullable();

            // ── Contacto Sura ───────────────────────────────────
            $table->string('contacto_sura', 150)->nullable();
            $table->string('telefono_sura', 20)->nullable();
            $table->string('email_sura', 120)->nullable();

            // ── Respuesta ───────────────────────────────────────
            $table->string('numero_solicitud_sura', 30)->nullable();
            $table->timestamp('fecha_respuesta')->nullable();
            $table->enum('resultado_sura', [
                'pendiente',
                'aprobada',
                'rechazada',
                'condicional',
            ])->default('pendiente');
            $table->string('analista_sura', 150)->nullable();
            $table->text('observaciones_sura')->nullable();
            $table->string('path_respuesta', 300)->nullable();

            $table->text('notas')->nullable();
            $table->timestamps();

            $table->index(['request_id', 'resultado_sura']);
        });
    }
    public function down(): void { Schema::dropIfExists('request_sura_studies'); }
};

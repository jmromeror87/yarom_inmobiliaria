<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_executions', function (Blueprint $table) {
            $table->id();
            $table->string('job_name');           // Nombre legible: "Generar Facturas"
            $table->string('job_class');           // FQCN del Job
            $table->string('disparado_por')->default('scheduler'); // scheduler | manual
            $table->string('estado')->default('ejecutando'); // ejecutando | completado | fallido
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->unsignedInteger('registros_procesados')->default(0);
            $table->json('detalles')->nullable();  // resumen estructurado del resultado
            $table->text('errores')->nullable();
            $table->timestamps();

            $table->index(['job_class', 'started_at']);
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_executions');
    }
};

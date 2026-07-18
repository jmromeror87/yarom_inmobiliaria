<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('siinmob_historico_notas', function (Blueprint $table) {
            $table->id();
            $table->string('ver_ref')->unique();
            $table->date('fecha');
            $table->string('tipo', 5);
            $table->string('nota_numero')->nullable();
            $table->string('transaccion')->nullable();
            $table->string('detalle')->nullable();
            $table->string('creada_por')->nullable();
            $table->text('concepto')->nullable();
            $table->decimal('total_debito', 15, 2)->default(0);
            $table->decimal('total_credito', 15, 2)->default(0);
            $table->timestamps();

            $table->index('fecha');
            $table->index('tipo');
        });

        Schema::create('siinmob_historico_lineas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nota_id')->constrained('siinmob_historico_notas')->cascadeOnDelete();
            $table->string('cuenta_codigo')->nullable();
            $table->string('cuenta_nombre')->nullable();
            $table->string('descripcion_linea')->nullable();
            $table->decimal('debito', 15, 2)->default(0);
            $table->decimal('credito', 15, 2)->default(0);
            $table->timestamps();

            $table->index('cuenta_codigo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('siinmob_historico_lineas');
        Schema::dropIfExists('siinmob_historico_notas');
    }
};

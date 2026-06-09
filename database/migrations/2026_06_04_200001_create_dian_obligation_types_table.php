<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dian_obligation_types', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 30)->unique();          // retefte, iva, renta, exogena_1001, ica
            $table->string('nombre', 150);
            $table->string('formulario', 20)->nullable();    // 350, 300, 110, 1001, D-500
            $table->enum('periodicidad', [
                'mensual',
                'bimestral',
                'cuatrimestral',
                'anual',
            ]);
            $table->text('descripcion')->nullable();
            $table->boolean('activa')->default(true);
            $table->unsignedTinyInteger('orden')->default(10);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dian_obligation_types');
    }
};

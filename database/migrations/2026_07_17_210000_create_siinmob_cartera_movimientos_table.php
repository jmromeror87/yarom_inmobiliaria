<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('siinmob_cartera_movimientos', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->string('tipo_cartera', 5); // cxc | cxp
            $table->string('comprobante')->nullable();
            $table->string('tercero')->nullable();
            $table->text('descripcion')->nullable();
            $table->string('referencia')->nullable();
            $table->decimal('debito', 15, 2)->default(0);
            $table->decimal('credito', 15, 2)->default(0);
            $table->timestamps();

            $table->index('fecha');
            $table->index('tercero');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('siinmob_cartera_movimientos');
    }
};

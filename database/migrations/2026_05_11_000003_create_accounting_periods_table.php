<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounting_periods', function (Blueprint $table) {
            $table->id();
            $table->smallInteger('anio');
            $table->tinyInteger('mes');          // 1-12
            $table->enum('estado', ['abierto', 'cerrado'])->default('abierto');
            $table->foreignId('cerrado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('cerrado_en')->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();

            $table->unique(['anio', 'mes']);
        });
    }

    public function down(): void { Schema::dropIfExists('accounting_periods'); }
};

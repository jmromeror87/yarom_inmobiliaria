<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('daily_review_checks', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->enum('tipo', ['inquilino', 'propietario']);
            $table->foreignId('third_id')->constrained('thirds')->cascadeOnDelete();
            $table->boolean('revisado')->default(false);
            $table->foreignId('revisado_por_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('revisado_en')->nullable();
            $table->timestamps();

            $table->unique(['fecha', 'tipo', 'third_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_review_checks');
    }
};

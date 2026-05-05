<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('municipios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('departamento_id')->constrained('departamentos');
            $table->string('codigo_dane', 5)->unique();
            $table->string('nombre', 120);
            $table->enum('categoria', ['especial','primera','segunda','tercera','cuarta','quinta','sexta'])->nullable();
            $table->decimal('tarifa_ica', 5, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('municipios'); }
};

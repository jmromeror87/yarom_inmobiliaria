<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rental_contract_clauses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_contract_id')->constrained('rental_contracts')->cascadeOnDelete();
            $table->foreignId('contract_clause_id')->nullable()->constrained('contract_clauses')->nullOnDelete();

            $table->string('numero', 60);
            $table->string('titulo', 200);
            $table->enum('tipo', ['clausula', 'paragrafo', 'considerando', 'nota'])->default('clausula');
            $table->text('contenido_original');
            $table->text('contenido_actual');
            $table->boolean('fue_editada')->default(false);
            $table->boolean('es_editable')->default(true);
            $table->boolean('es_obligatoria')->default(true);
            $table->smallInteger('orden')->default(0);

            $table->timestamps();
            $table->index(['rental_contract_id', 'orden']);
        });
    }
    public function down(): void { Schema::dropIfExists('rental_contract_clauses'); }
};

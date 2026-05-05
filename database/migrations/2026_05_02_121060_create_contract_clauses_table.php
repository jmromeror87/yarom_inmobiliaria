<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_clauses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_template_id')->constrained('contract_templates')->cascadeOnDelete();
            $table->string('numero', 20);
            $table->string('titulo', 200);
            $table->enum('tipo', ['considerando', 'clausula', 'paragrafo', 'nota'])->default('clausula');
            $table->longText('contenido');
            $table->boolean('es_editable')->default(true);
            $table->boolean('es_obligatoria')->default(true);
            $table->smallInteger('orden')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['contract_template_id', 'orden']);
        });
    }
    public function down(): void { Schema::dropIfExists('contract_clauses'); }
};

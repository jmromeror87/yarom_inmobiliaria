<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('administration_contract_clauses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('administration_contract_id');
            $table->unsignedBigInteger('contract_clause_id')->nullable();
            $table->string('numero', 20);
            $table->string('titulo', 200);
            $table->enum('tipo', ['considerando','clausula','paragrafo','nota'])->default('clausula');
            $table->longText('contenido_original');
            $table->longText('contenido_actual');
            $table->boolean('fue_editada')->default(false);
            $table->boolean('es_editable')->default(true);
            $table->boolean('es_obligatoria')->default(true);
            $table->smallInteger('orden')->default(0);
            $table->timestamps();

            $table->foreign('administration_contract_id', 'fk_acc_contract')
                ->references('id')->on('administration_contracts')->cascadeOnDelete();
            $table->foreign('contract_clause_id', 'fk_acc_clause')
                ->references('id')->on('contract_clauses')->nullOnDelete();

            $table->index(['administration_contract_id', 'orden'], 'idx_acc_contract_orden');
        });
    }
    public function down(): void { Schema::dropIfExists('administration_contract_clauses'); }
};

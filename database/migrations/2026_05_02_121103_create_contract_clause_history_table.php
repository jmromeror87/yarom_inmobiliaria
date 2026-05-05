<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_clause_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('acc_clause_id');
            $table->unsignedBigInteger('editado_por')->nullable();
            $table->longText('contenido_anterior');
            $table->longText('contenido_nuevo');
            $table->string('razon_cambio', 300)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('editado_en');
            $table->timestamps();

            $table->foreign('acc_clause_id', 'fk_cch_clause')
                ->references('id')->on('administration_contract_clauses')->cascadeOnDelete();
            $table->foreign('editado_por', 'fk_cch_user')
                ->references('id')->on('users')->nullOnDelete();

            $table->index('acc_clause_id', 'idx_cch_clause');
        });
    }
    public function down(): void { Schema::dropIfExists('contract_clause_history'); }
};

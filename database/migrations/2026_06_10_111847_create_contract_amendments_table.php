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
        Schema::create('contract_amendments', function (Blueprint $table) {
            $table->id();

            $table->string('numero', 30)->unique();

            $table->foreignId('rental_contract_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('administration_contract_id')->nullable()->constrained()->nullOnDelete();

            $table->enum('tipo', [
                'incremento_canon',
                'prorroga',
                'cesion_arrendatario',
                'cambio_codeudor',
                'adicion_areas',
                'modificacion_clausula',
                'cambio_comision',
                'otro',
            ]);

            $table->string('titulo', 200);
            $table->text('descripcion');
            $table->text('clausula_modificada')->nullable();

            $table->decimal('valor_anterior', 14, 2)->nullable();
            $table->decimal('valor_nuevo', 14, 2)->nullable();
            $table->date('fecha_fin_anterior')->nullable();
            $table->date('fecha_fin_nueva')->nullable();
            $table->string('texto_anterior', 500)->nullable();
            $table->string('texto_nuevo', 500)->nullable();

            $table->date('fecha_firma');
            $table->date('fecha_vigencia');

            $table->enum('estado', ['borrador', 'firmado', 'anulado'])->default('borrador');

            $table->boolean('aplica_cambio_automatico')->default(true);
            $table->boolean('cambio_aplicado')->default(false);
            $table->timestamp('cambio_aplicado_en')->nullable();

            $table->string('firmado_por_arrendador')->nullable();
            $table->string('firmado_por_arrendatario')->nullable();
            $table->string('firmado_por_garante')->nullable();

            $table->string('path_documento')->nullable();
            $table->text('notas')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contract_amendments');
    }
};

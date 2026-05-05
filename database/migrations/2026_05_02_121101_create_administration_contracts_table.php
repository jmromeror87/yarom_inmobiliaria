<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('administration_contracts', function (Blueprint $table) {
            $table->id();
            $table->string('numero_contrato', 30)->unique();
            $table->foreignId('contract_template_id')->constrained('contract_templates');
            $table->foreignId('property_id')->constrained('properties');
            $table->foreignId('propietario_id')->constrained('thirds');
            $table->foreignId('asesor_id')->nullable()->constrained('users')->nullOnDelete();

            // Tipo y vigencia
            $table->enum('tipo_contrato', ['administracion_arriendo', 'administracion_venta'])->default('administracion_arriendo');
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->enum('renovacion', ['automatica', 'manual'])->default('automatica');
            $table->tinyInteger('dias_aviso_terminacion')->default(30);

            // Condiciones económicas
            $table->decimal('canon_pactado', 12, 2);
            $table->decimal('comision_porcentaje', 5, 2)->default(10.00);
            $table->decimal('comision_venta_porcentaje', 5, 2)->default(3.00);

            // Estado del contrato
            $table->enum('estado', [
                'borrador',
                'enviado_propietario',
                'en_revision',
                'aprobado',
                'firmado',
                'activo',
                'terminado',
                'cancelado',
            ])->default('borrador');

            $table->date('fecha_firma')->nullable();
            $table->string('firmado_por', 150)->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('estado');
            $table->index('property_id');
            $table->index('propietario_id');
        });
    }
    public function down(): void { Schema::dropIfExists('administration_contracts'); }
};

<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();

            // ── Identificación ─────────────────────────────────
            $table->string('codigo', 20)->unique();           // INM-2026-0001
            $table->foreignId('property_type_id')->constrained('property_types');
            $table->foreignId('propietario_id')->constrained('thirds');

            // ── Ubicación ──────────────────────────────────────
            $table->text('direccion');
            $table->string('barrio', 100)->nullable();
            $table->string('conjunto_edificio', 150)->nullable();
            $table->string('apto_casa_oficina', 30)->nullable();
            $table->foreignId('municipio_id')->nullable()->constrained('municipios');
            $table->foreignId('departamento_id')->nullable()->constrained('departamentos');
            $table->decimal('latitud', 10, 7)->nullable();
            $table->decimal('longitud', 10, 7)->nullable();

            // ── Características físicas ────────────────────────
            $table->tinyInteger('estrato')->default(1);
            $table->decimal('area_construida_m2', 8, 2)->nullable();
            $table->decimal('area_privada_m2', 8, 2)->nullable();
            $table->decimal('area_total_m2', 8, 2)->nullable();
            $table->tinyInteger('habitaciones')->default(0);
            $table->tinyInteger('banos')->default(0);
            $table->tinyInteger('garajes')->default(0);
            $table->tinyInteger('depositos')->default(0);
            $table->tinyInteger('piso')->nullable();
            $table->tinyInteger('total_pisos')->nullable();
            $table->smallInteger('anio_construccion')->nullable();

            // ── Características adicionales ────────────────────
            $table->boolean('tiene_ascensor')->default(false);
            $table->boolean('tiene_piscina')->default(false);
            $table->boolean('tiene_gym')->default(false);
            $table->boolean('tiene_salon_comunal')->default(false);
            $table->boolean('tiene_vigilancia')->default(false);
            $table->boolean('permite_mascotas')->default(false);
            $table->boolean('amoblado')->default(false);

            // ── Valores económicos ─────────────────────────────
            $table->decimal('canon_arriendo', 12, 2)->nullable();
            $table->decimal('cuota_administracion', 12, 2)->default(0);
            $table->decimal('precio_venta', 14, 2)->nullable();
            $table->decimal('avaluo_catastral', 14, 2)->nullable();
            $table->decimal('avaluo_comercial', 14, 2)->nullable();
            $table->integer('anio_avaluo')->nullable();

            // ── Tipo de negocio ────────────────────────────────
            $table->boolean('disponible_arriendo')->default(true);
            $table->boolean('disponible_venta')->default(false);

            // ── Estado del pipeline ────────────────────────────
            $table->enum('estado', [
                'en_captacion',
                'documentos_pendientes',
                'disponible',
                'arrendado',
                'en_venta',
                'vendido',
                'en_mantenimiento',
                'inactivo',
            ])->default('en_captacion');

            // ── Documentos requeridos (checklist) ─────────────
            $table->boolean('doc_escritura')->default(false);
            $table->boolean('doc_certificado_libertad')->default(false);
            $table->date('doc_certificado_libertad_fecha')->nullable();
            $table->boolean('doc_predial')->default(false);
            $table->boolean('doc_paz_salvo_admin')->default(false);
            $table->boolean('doc_documento_propietario')->default(false);
            $table->boolean('doc_recibo_servicios')->default(false);

            // ── Publicación ────────────────────────────────────
            $table->date('fecha_captacion')->nullable();
            $table->date('fecha_disponible')->nullable();
            $table->text('descripcion_publica')->nullable();
            $table->text('notas_internas')->nullable();

            // ── Asesor responsable ─────────────────────────────
            $table->foreignId('asesor_id')->nullable()->constrained('users')->nullOnDelete();

            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('estado');
            $table->index('propietario_id');
            $table->index('municipio_id');
        });
    }
    public function down(): void { Schema::dropIfExists('properties'); }
};

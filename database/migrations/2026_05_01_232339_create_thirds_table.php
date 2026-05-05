<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('thirds', function (Blueprint $table) {
            $table->id();

            // ── Roles del tercero ──────────────────────────────
            $table->boolean('es_propietario')->default(false);
            $table->boolean('es_arrendatario')->default(false);
            $table->boolean('es_cliente_compra')->default(false);
            $table->boolean('es_fiador')->default(false);
            $table->boolean('es_proveedor')->default(false);

            // ── Identificación ─────────────────────────────────
            $table->enum('tipo_persona', ['natural', 'juridica'])->default('natural');
            $table->enum('tipo_documento', ['CC','CE','NIT','Pasaporte','TI','PEP','PPT'])->default('CC');
            $table->string('numero_documento', 20)->unique();
            $table->tinyInteger('digito_verificacion')->nullable();

            // ── Nombre persona natural ─────────────────────────
            $table->string('primer_nombre', 80)->nullable();
            $table->string('segundo_nombre', 80)->nullable();
            $table->string('primer_apellido', 80)->nullable();
            $table->string('segundo_apellido', 80)->nullable();

            // ── Nombre persona jurídica ────────────────────────
            $table->string('razon_social', 200)->nullable();
            $table->string('nombre_comercial', 200)->nullable();

            // ── Campo calculado ────────────────────────────────
            $table->string('nombre_completo', 300)->nullable();

            // ── Género y estado civil ──────────────────────────
            $table->enum('genero', ['masculino','femenino','otro'])->nullable();
            $table->enum('estado_civil', [
                'soltero','casado','union_libre','divorciado','viudo','separado'
            ])->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->string('lugar_nacimiento', 100)->nullable();
            $table->string('nacionalidad', 80)->default('Colombiana');

            // ── Contacto ───────────────────────────────────────
            $table->string('email', 120)->nullable();
            $table->string('email_alt', 120)->nullable();
            $table->string('telefono_fijo', 20)->nullable();
            $table->string('celular', 20)->nullable();
            $table->string('celular_alt', 20)->nullable();
            $table->string('whatsapp', 20)->nullable();

            // ── Dirección de residencia ────────────────────────
            $table->text('direccion_residencia')->nullable();
            $table->string('barrio_residencia', 100)->nullable();
            $table->foreignId('municipio_id')->nullable()->constrained('municipios');
            $table->foreignId('departamento_id')->nullable()->constrained('departamentos');
            $table->foreignId('pais_id')->default(1)->constrained('paises');
            $table->string('codigo_postal', 10)->nullable();

            // ── Información laboral ────────────────────────────
            $table->enum('tipo_empleo', [
                'dependiente','independiente','pensionado',
                'rentista','desempleado','otro'
            ])->nullable();
            $table->string('empresa_donde_trabaja', 150)->nullable();
            $table->string('cargo', 100)->nullable();
            $table->string('telefono_empresa', 20)->nullable();
            $table->text('direccion_empresa')->nullable();
            $table->integer('meses_empleo_actual')->default(0);
            $table->decimal('ingresos_mensuales', 12, 2)->nullable();
            $table->decimal('otros_ingresos', 12, 2)->nullable();
            $table->text('descripcion_otros_ingresos')->nullable();

            // ── Datos bancarios (propietarios) ─────────────────
            $table->string('banco', 80)->nullable();
            $table->enum('tipo_cuenta', ['ahorros','corriente'])->nullable();
            $table->string('numero_cuenta', 30)->nullable();
            $table->string('titular_cuenta', 150)->nullable();

            // ── Evaluación crediticia ──────────────────────────
            $table->enum('estado_crediticio', [
                'sin_evaluar','aprobado','rechazado','condicional','en_proceso'
            ])->default('sin_evaluar');
            $table->date('fecha_evaluacion_crediticia')->nullable();
            $table->string('score_crediticio', 20)->nullable();
            $table->boolean('reporte_negativo')->default(false);
            $table->text('notas_evaluacion')->nullable();

            // ── Garantía arrendamiento ─────────────────────────
            $table->enum('tipo_garantia', [
                'fiador','poliza','deposito','ninguna'
            ])->nullable();
            $table->string('aseguradora', 100)->nullable();
            $table->string('numero_poliza', 50)->nullable();

            // ── Config propietario ─────────────────────────────
            $table->decimal('comision_pactada', 5, 2)->nullable();

            // ── Referencias personales ─────────────────────────
            $table->json('referencias_personales')->nullable();

            // ── Documentos ────────────────────────────────────
            $table->json('documentos_adjuntos')->nullable();

            // ── CRM ───────────────────────────────────────────
            $table->enum('fuente_captacion', [
                'referido','web','redes_sociales','portales_inmobiliarios',
                'voz_a_voz','llamada','visita_directa','otro'
            ])->nullable();
            $table->foreignId('asesor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('ultimo_contacto')->nullable();
            $table->text('notas_crm')->nullable();

            // ── Estado ────────────────────────────────────────
            $table->text('notas')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tipo_documento', 'numero_documento']);
            $table->index('es_propietario');
            $table->index('es_arrendatario');
            $table->index('es_cliente_compra');
            $table->index('estado_crediticio');
            $table->index('asesor_id');
        });
    }

    public function down(): void { Schema::dropIfExists('thirds'); }
};

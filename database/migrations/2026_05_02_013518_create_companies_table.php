<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo_persona', ['natural','juridica'])->default('juridica');
            $table->string('razon_social', 200);
            $table->string('nombre_comercial', 200)->nullable();
            $table->string('nit', 15)->unique();
            $table->tinyInteger('digito_verificacion')->default(0);
            $table->string('nit_completo', 20)->nullable();
            $table->string('matricula_mercantil', 20)->nullable();
            $table->date('fecha_matricula')->nullable();
            $table->date('fecha_renovacion')->nullable();
            $table->string('camara_comercio', 100)->nullable();
            $table->string('codigo_ciiu', 10)->nullable();
            $table->string('descripcion_ciiu', 200)->nullable();
            $table->enum('tipo_contribuyente', ['persona_juridica','persona_natural_comerciante','persona_natural_no_comerciante','entidad_sin_animo_lucro','gran_contribuyente','otro'])->default('persona_juridica');
            $table->enum('regimen_fiscal', ['simple_tributacion','ordinario','especial'])->default('ordinario');
            $table->boolean('responsable_iva')->default(true);
            $table->decimal('tarifa_iva', 5, 2)->default(19.00);
            $table->boolean('gran_contribuyente')->default(false);
            $table->boolean('autorretenedor')->default(false);
            $table->boolean('agente_retencion_fuente')->default(true);
            $table->boolean('agente_reteica')->default(false);
            $table->boolean('agente_reteiva')->default(false);
            $table->decimal('tarifa_retefuente_servicios', 5, 2)->default(4.00);
            $table->decimal('tarifa_retefuente_honorarios', 5, 2)->default(10.00);
            $table->decimal('tarifa_retefuente_arrendamiento', 5, 2)->default(3.50);
            $table->decimal('tarifa_reteica', 5, 3)->nullable();
            $table->string('resolucion_facturacion', 30)->nullable();
            $table->date('fecha_resolucion')->nullable();
            $table->string('prefijo_factura', 10)->nullable();
            $table->unsignedInteger('consecutivo_desde')->default(1);
            $table->unsignedInteger('consecutivo_hasta')->nullable();
            $table->unsignedInteger('consecutivo_actual')->default(1);
            $table->boolean('factura_electronica_activa')->default(false);
            $table->string('software_dian_id', 100)->nullable();
            $table->string('software_dian_pin', 20)->nullable();
            $table->string('rep_legal_nombre', 150)->nullable();
            $table->enum('rep_legal_tipo_doc', ['CC','CE','Pasaporte'])->default('CC');
            $table->string('rep_legal_documento', 20)->nullable();
            $table->string('rep_legal_email', 120)->nullable();
            $table->string('rep_legal_telefono', 20)->nullable();
            $table->text('direccion')->nullable();
            $table->string('barrio', 100)->nullable();
            $table->foreignId('municipio_id')->nullable()->constrained('municipios');
            $table->foreignId('departamento_id')->nullable()->constrained('departamentos');
            $table->foreignId('pais_id')->default(1)->constrained('paises');
            $table->string('codigo_postal', 10)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('telefono_alt', 20)->nullable();
            $table->string('celular', 20)->nullable();
            $table->string('email', 120)->nullable();
            $table->string('email_notificaciones', 120)->nullable();
            $table->string('sitio_web', 150)->nullable();
            $table->string('logo_path')->nullable();
            $table->string('color_primario', 7)->default('#E11D48');
            $table->string('color_secundario', 7)->default('#2563EB');
            $table->decimal('comision_administracion', 5, 2)->default(10.00);
            $table->tinyInteger('dia_corte_mensual')->default(5);
            $table->tinyInteger('dias_gracia_mora')->default(5);
            $table->decimal('tasa_mora_mensual', 5, 4)->default(1.5441);
            $table->text('notas')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('companies'); }
};

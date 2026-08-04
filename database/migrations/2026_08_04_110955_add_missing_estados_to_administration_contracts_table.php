<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * El formulario del contrato de administración ya ofrecía los estados
     * 'aprobado_gerencia', 'enviado_notaria' y 'autenticado_notaria', pero
     * la columna nunca se actualizó para permitirlos — causaba un error
     * SQL "Data truncated for column 'estado'" al intentar aprobar un
     * contrato desde gerencia.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE administration_contracts MODIFY estado ENUM(
            'borrador',
            'enviado_propietario',
            'en_revision',
            'aprobado',
            'aprobado_gerencia',
            'enviado_notaria',
            'autenticado_notaria',
            'firmado',
            'activo',
            'terminado',
            'cancelado'
        ) NOT NULL DEFAULT 'borrador'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE administration_contracts MODIFY estado ENUM(
            'borrador',
            'enviado_propietario',
            'en_revision',
            'aprobado',
            'firmado',
            'activo',
            'terminado',
            'cancelado'
        ) NOT NULL DEFAULT 'borrador'");
    }
};

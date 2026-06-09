<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL no permite ALTER COLUMN en enums con CHANGE simple en todos los drivers,
        // usamos DB::statement para modificar el enum directamente.
        DB::statement("ALTER TABLE accounting_entries MODIFY COLUMN tipo ENUM('CC','CI','CE','ND','NC','CA','CR') NOT NULL COMMENT 'CC=Contabilidad,CI=Ingreso,CE=Egreso,ND=Nota Débito,NC=Nota Crédito,CA=Ajuste,CR=Comprobante Recaudo'");
    }

    public function down(): void
    {
        // Actualizar registros CR a CC antes de revertir para no violar el enum
        DB::statement("UPDATE accounting_entries SET tipo = 'CC' WHERE tipo = 'CR'");
        DB::statement("ALTER TABLE accounting_entries MODIFY COLUMN tipo ENUM('CC','CI','CE','ND','NC','CA') NOT NULL COMMENT 'CC=Contabilidad,CI=Ingreso,CE=Egreso,ND=Nota Débito,NC=Nota Crédito,CA=Ajuste'");
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * La tabla owner_liquidation_status_histories se creó con solo id/timestamps
 * — nunca se agregaron las columnas reales que usa el modelo
 * OwnerLiquidationStatusHistory. El botón "Historial" en Liquidaciones
 * Propietarios se rompería con "Column not found" en cuanto alguien
 * lo abriera (orderByDesc('cambiado_en')).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('owner_liquidation_status_histories', function (Blueprint $table) {
            if (! Schema::hasColumn('owner_liquidation_status_histories', 'owner_liquidation_id')) {
                $table->foreignId('owner_liquidation_id')->nullable()->constrained('owner_liquidations')->nullOnDelete();
            }
            if (! Schema::hasColumn('owner_liquidation_status_histories', 'estado_anterior')) {
                $table->string('estado_anterior')->nullable();
            }
            if (! Schema::hasColumn('owner_liquidation_status_histories', 'estado_nuevo')) {
                $table->string('estado_nuevo')->nullable();
            }
            if (! Schema::hasColumn('owner_liquidation_status_histories', 'usuario_id')) {
                $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('owner_liquidation_status_histories', 'ip')) {
                $table->string('ip')->nullable();
            }
            if (! Schema::hasColumn('owner_liquidation_status_histories', 'notas')) {
                $table->text('notas')->nullable();
            }
            if (! Schema::hasColumn('owner_liquidation_status_histories', 'cambiado_en')) {
                $table->dateTime('cambiado_en')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('owner_liquidation_status_histories', function (Blueprint $table) {
            $cols = ['owner_liquidation_id', 'estado_anterior', 'estado_nuevo', 'usuario_id', 'ip', 'notas', 'cambiado_en'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('owner_liquidation_status_histories', $col)) {
                    if (in_array($col, ['owner_liquidation_id', 'usuario_id'])) {
                        $table->dropConstrainedForeignId($col);
                    } else {
                        $table->dropColumn($col);
                    }
                }
            }
        });
    }
};

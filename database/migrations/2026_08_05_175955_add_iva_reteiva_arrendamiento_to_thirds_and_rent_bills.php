<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('thirds', function (Blueprint $table) {
            $table->boolean('arrendamiento_aplica_iva')->default(false)->after('tarifa_retefuente_arrendamiento')
                ->comment('Arriendo comercial: el canon genera IVA (vivienda está exenta por ley)');
            $table->decimal('arrendamiento_tarifa_iva', 5, 2)->nullable()->after('arrendamiento_aplica_iva')
                ->comment('% de IVA sobre el canon — si es null, usa la tarifa IVA global de la empresa (19%)');
            $table->boolean('es_agente_retenedor_iva')->default(false)->after('arrendamiento_tarifa_iva')
                ->comment('Arrendatario persona jurídica que además practica reteIVA sobre el IVA generado');
            $table->decimal('arrendamiento_tarifa_reteiva', 5, 2)->nullable()->after('es_agente_retenedor_iva')
                ->comment('% de reteIVA sobre el IVA (no sobre el canon) — si es null, usa 15% estándar DIAN');
        });

        Schema::table('rent_bills', function (Blueprint $table) {
            $table->decimal('iva_practicado', 12, 2)->default(0)->after('retencion_practicada')
                ->comment('IVA generado sobre el canon (solo arriendo comercial)');
            $table->decimal('reteiva_practicada', 12, 2)->default(0)->after('iva_practicado')
                ->comment('ReteIVA que el arrendatario practica sobre el IVA generado');
        });

        // Cuentas contables nuevas para IVA de arrendamiento y reteIVA —
        // no existían en el PUC (solo estaba el IVA de comisiones).
        $ivaComision = DB::table('accounting_accounts')->where('codigo', '24080101')->first();
        if ($ivaComision && !DB::table('accounting_accounts')->where('codigo', '24080102')->exists()) {
            DB::table('accounting_accounts')->insert([
                'codigo' => '24080102', 'nombre' => 'Iva de arrendamientos',
                'nivel' => 5, 'parent_id' => $ivaComision->parent_id, 'clase' => 2,
                'naturaleza' => 'credito', 'acepta_movimiento' => true,
                'requiere_tercero' => false, 'requiere_centro_costo' => false,
                'estado' => 'activo', 'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        $rete = DB::table('accounting_accounts')->where('codigo', '13551501')->first();
        if ($rete && !DB::table('accounting_accounts')->where('codigo', '13551503')->exists()) {
            DB::table('accounting_accounts')->insert([
                'codigo' => '13551503', 'nombre' => 'ReteIVA practicada por arrendatarios',
                'nivel' => 5, 'parent_id' => $rete->parent_id, 'clase' => 1,
                'naturaleza' => 'debito', 'acepta_movimiento' => true,
                'requiere_tercero' => true, 'requiere_centro_costo' => false,
                'estado' => 'activo', 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('accounting_accounts')->whereIn('codigo', ['24080102', '13551503'])->delete();

        Schema::table('thirds', function (Blueprint $table) {
            $table->dropColumn([
                'arrendamiento_aplica_iva', 'arrendamiento_tarifa_iva',
                'es_agente_retenedor_iva', 'arrendamiento_tarifa_reteiva',
            ]);
        });

        Schema::table('rent_bills', function (Blueprint $table) {
            $table->dropColumn(['iva_practicado', 'reteiva_practicada']);
        });
    }
};

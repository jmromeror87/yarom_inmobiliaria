<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * El PUC real extraído de Siinmob no traía cuentas auxiliares para conceptos
 * que ContabilidadService sí necesita generar (mora, provisión de cartera).
 * Se agregan aquí como cuentas hoja (acepta_movimiento) bajo su grupo real.
 */
return new class extends Migration
{
    public function up(): void
    {
        $parentIntereses  = DB::table('accounting_accounts')->where('codigo', '4210')->value('id');
        $parentDiversos    = DB::table('accounting_accounts')->where('codigo', '5195')->value('id');

        $cuentas = [
            [
                'codigo' => '42100505', 'nombre' => 'Intereses de mora',
                'nivel' => 5, 'clase' => '4', 'naturaleza' => 'credito',
                'acepta_movimiento' => true, 'parent_id' => $parentIntereses,
            ],
            [
                'codigo' => '519905', 'nombre' => 'Gasto provisión deudores',
                'nivel' => 4, 'clase' => '5', 'naturaleza' => 'debito',
                'acepta_movimiento' => true, 'parent_id' => $parentDiversos,
            ],
        ];

        foreach ($cuentas as $c) {
            $existe = DB::table('accounting_accounts')->where('codigo', $c['codigo'])->exists();
            if (! $existe) {
                DB::table('accounting_accounts')->insert(array_merge($c, [
                    'requiere_tercero' => false,
                    'requiere_centro_costo' => false,
                    'estado' => 'activo',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }
    }

    public function down(): void
    {
        DB::table('accounting_accounts')->whereIn('codigo', ['42100505', '519905'])->delete();
    }
};

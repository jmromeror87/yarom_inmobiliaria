<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Crear grupo 1365 Cuentas por cobrar a trabajadores (parent de 136515) ──
        $g1365 = DB::table('accounting_accounts')->insertGetId([
            'codigo' => '1365', 'nombre' => 'CUENTAS POR COBRAR A TRABAJADORES',
            'nivel' => 3, 'parent_id' => DB::table('accounting_accounts')->where('codigo','13')->value('id'),
            'clase' => '1', 'naturaleza' => 'debito',
            'acepta_movimiento' => false, 'requiere_tercero' => false, 'requiere_centro_costo' => false,
            'estado' => 'activo', 'created_at' => now(), 'updated_at' => now(),
        ]);

        // ── 2. Cuentas auxiliares faltantes ─────────────────────────────────────────
        $nuevas = [
            // Anticipo autorretención (ya existe 135505, agregamos la de renta)
            ['codigo'=>'136515','nombre'=>'Deudores — anticipo de impuestos varios',     'nivel'=>4,'parent_id'=>$g1365,              'clase'=>'1','naturaleza'=>'debito',  'acepta_movimiento'=>true],

            // Provisión acumulada cartera (cuenta correctiva del activo)
            ['codigo'=>'139905','nombre'=>'Provisión acumulada deudores',                 'nivel'=>4,'parent_id'=>DB::table('accounting_accounts')->where('codigo','1380')->value('id'),  'clase'=>'1','naturaleza'=>'credito', 'acepta_movimiento'=>true],

            // Provisión gasto (ya existe 519905, agregamos la recuperación)
            ['codigo'=>'519910','nombre'=>'Recuperación provisión deudores',              'nivel'=>4,'parent_id'=>DB::table('accounting_accounts')->where('codigo','5199')->value('id'),  'clase'=>'5','naturaleza'=>'debito',  'acepta_movimiento'=>true],

            // Autorretención en la fuente a título de renta
            ['codigo'=>'236525','nombre'=>'Autorretención a título de renta',             'nivel'=>4,'parent_id'=>DB::table('accounting_accounts')->where('codigo','2365')->value('id'),  'clase'=>'2','naturaleza'=>'credito', 'acepta_movimiento'=>true],

            // Cuentas de orden — inmuebles administrados
            ['codigo'=>'8','nombre'=>'CUENTAS DE ORDEN DEUDORAS',                        'nivel'=>1,'parent_id'=>null,                'clase'=>'8','naturaleza'=>'debito',  'acepta_movimiento'=>false],
            ['codigo'=>'81','nombre'=>'DERECHOS CONTINGENTES',                           'nivel'=>2,'parent_id'=>null,                'clase'=>'8','naturaleza'=>'debito',  'acepta_movimiento'=>false],
            ['codigo'=>'8105','nombre'=>'BIENES Y VALORES ENTREGADOS EN CUSTODIA',       'nivel'=>3,'parent_id'=>null,                'clase'=>'8','naturaleza'=>'debito',  'acepta_movimiento'=>false],
            ['codigo'=>'810505','nombre'=>'Inmuebles recibidos en administración',        'nivel'=>4,'parent_id'=>null,                'clase'=>'8','naturaleza'=>'debito',  'acepta_movimiento'=>true],
            ['codigo'=>'810510','nombre'=>'Pólizas y garantías recibidas',               'nivel'=>4,'parent_id'=>null,                'clase'=>'8','naturaleza'=>'debito',  'acepta_movimiento'=>true],

            // Cuentas de orden acreedoras (contrapartidas)
            ['codigo'=>'9','nombre'=>'CUENTAS DE ORDEN ACREEDORAS',                     'nivel'=>1,'parent_id'=>null,                'clase'=>'9','naturaleza'=>'credito', 'acepta_movimiento'=>false],
            ['codigo'=>'91','nombre'=>'RESPONSABILIDADES CONTINGENTES',                  'nivel'=>2,'parent_id'=>null,                'clase'=>'9','naturaleza'=>'credito', 'acepta_movimiento'=>false],
            ['codigo'=>'9105','nombre'=>'BIENES Y VALORES RECIBIDOS EN CUSTODIA',       'nivel'=>3,'parent_id'=>null,                'clase'=>'9','naturaleza'=>'credito', 'acepta_movimiento'=>false],
            ['codigo'=>'910505','nombre'=>'Propietarios — inmuebles en administración',  'nivel'=>4,'parent_id'=>null,                'clase'=>'9','naturaleza'=>'credito', 'acepta_movimiento'=>true],
            ['codigo'=>'910510','nombre'=>'Pólizas y garantías entregadas',             'nivel'=>4,'parent_id'=>null,                'clase'=>'9','naturaleza'=>'credito', 'acepta_movimiento'=>true],
        ];

        foreach ($nuevas as $cuenta) {
            // Fijar parent_id para cuentas de orden que lo necesitan
            if (in_array($cuenta['codigo'], ['81','8105','810505','810510'])) {
                $cuenta['parent_id'] = DB::table('accounting_accounts')->where('codigo','8')->value('id') ?? null;
            }
            if (in_array($cuenta['codigo'], ['91','9105','910505','910510'])) {
                $cuenta['parent_id'] = DB::table('accounting_accounts')->where('codigo','9')->value('id') ?? null;
            }
            if ($cuenta['codigo'] === '8105') {
                $cuenta['parent_id'] = DB::table('accounting_accounts')->where('codigo','81')->value('id') ?? null;
            }
            if (in_array($cuenta['codigo'], ['810505','810510'])) {
                $cuenta['parent_id'] = DB::table('accounting_accounts')->where('codigo','8105')->value('id') ?? null;
            }
            if ($cuenta['codigo'] === '9105') {
                $cuenta['parent_id'] = DB::table('accounting_accounts')->where('codigo','91')->value('id') ?? null;
            }
            if (in_array($cuenta['codigo'], ['910505','910510'])) {
                $cuenta['parent_id'] = DB::table('accounting_accounts')->where('codigo','9105')->value('id') ?? null;
            }

            DB::table('accounting_accounts')->insertOrIgnore(array_merge($cuenta, [
                'requiere_tercero'      => $cuenta['acepta_movimiento'],
                'requiere_centro_costo' => false,
                'estado'                => 'activo',
                'created_at'            => now(),
                'updated_at'            => now(),
            ]));
        }
    }

    public function down(): void
    {
        $codigos = ['136515','139905','519910','236525','810505','810510','8105','81','8','910505','910510','9105','91','9'];
        DB::table('accounting_accounts')->whereIn('codigo', $codigos)->delete();
    }
};

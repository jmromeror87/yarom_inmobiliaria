<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // ── PERMISOS ──────────────────────────────────────────────────────
        $permisos = [
            // Portafolio
            'ver_inmuebles', 'crear_inmuebles', 'editar_inmuebles', 'eliminar_inmuebles',

            // Contratos de arriendo
            'ver_contratos_arriendo', 'crear_contratos_arriendo', 'editar_contratos_arriendo', 'eliminar_contratos_arriendo',
            'firmar_contratos_arriendo',

            // Contratos de administración
            'ver_contratos_administracion', 'crear_contratos_administracion',
            'editar_contratos_administracion', 'eliminar_contratos_administracion',

            // Cartera / Facturas
            'ver_facturas', 'crear_facturas', 'editar_facturas', 'eliminar_facturas',
            'enviar_link_pago', 'ver_pagos',

            // Liquidaciones
            'ver_liquidaciones', 'crear_liquidaciones', 'aprobar_liquidaciones',
            'registrar_giro_liquidaciones', 'eliminar_liquidaciones',

            // Solicitudes / Estudios
            'ver_solicitudes', 'crear_solicitudes', 'editar_solicitudes', 'eliminar_solicitudes',
            'enviar_estudio_sudamericana',

            // Actas de entrega
            'ver_actas', 'crear_actas', 'editar_actas', 'eliminar_actas', 'firmar_actas',

            // Terceros
            'ver_terceros', 'crear_terceros', 'editar_terceros', 'eliminar_terceros',

            // Empresas
            'ver_empresas', 'editar_empresas',

            // Plantillas de contratos
            'ver_plantillas', 'crear_plantillas', 'editar_plantillas', 'eliminar_plantillas',

            // Contabilidad
            'ver_contabilidad', 'crear_asientos', 'contabilizar_asientos', 'anular_asientos',

            // Reportes
            'ver_reportes', 'descargar_reportes',

            // Dashboard y widgets
            'ver_dashboard',

            // Usuarios y roles (solo super-admin)
            'ver_usuarios', 'crear_usuarios', 'editar_usuarios', 'eliminar_usuarios',
            'asignar_roles',
        ];

        foreach ($permisos as $permiso) {
            Permission::firstOrCreate(['name' => $permiso, 'guard_name' => 'web']);
        }

        // ── ROLES ──────────────────────────────────────────────────────────

        // 1. Super Administrador — acceso total
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(Permission::all());

        // 2. Administrador — gestión completa excepto usuarios/roles
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions([
            'ver_inmuebles', 'crear_inmuebles', 'editar_inmuebles', 'eliminar_inmuebles',
            'ver_contratos_arriendo', 'crear_contratos_arriendo', 'editar_contratos_arriendo', 'eliminar_contratos_arriendo', 'firmar_contratos_arriendo',
            'ver_contratos_administracion', 'crear_contratos_administracion', 'editar_contratos_administracion', 'eliminar_contratos_administracion',
            'ver_facturas', 'crear_facturas', 'editar_facturas', 'eliminar_facturas', 'enviar_link_pago', 'ver_pagos',
            'ver_liquidaciones', 'crear_liquidaciones', 'aprobar_liquidaciones', 'registrar_giro_liquidaciones', 'eliminar_liquidaciones',
            'ver_solicitudes', 'crear_solicitudes', 'editar_solicitudes', 'eliminar_solicitudes', 'enviar_estudio_sudamericana',
            'ver_actas', 'crear_actas', 'editar_actas', 'eliminar_actas', 'firmar_actas',
            'ver_terceros', 'crear_terceros', 'editar_terceros', 'eliminar_terceros',
            'ver_empresas', 'editar_empresas',
            'ver_plantillas', 'crear_plantillas', 'editar_plantillas', 'eliminar_plantillas',
            'ver_contabilidad', 'crear_asientos', 'contabilizar_asientos', 'anular_asientos',
            'ver_reportes', 'descargar_reportes',
            'ver_dashboard',
        ]);

        // 3. Asesor — opera sin eliminar ni configurar
        $asesor = Role::firstOrCreate(['name' => 'asesor', 'guard_name' => 'web']);
        $asesor->syncPermissions([
            'ver_inmuebles', 'crear_inmuebles', 'editar_inmuebles',
            'ver_contratos_arriendo', 'crear_contratos_arriendo', 'editar_contratos_arriendo', 'firmar_contratos_arriendo',
            'ver_contratos_administracion', 'crear_contratos_administracion', 'editar_contratos_administracion',
            'ver_facturas', 'enviar_link_pago', 'ver_pagos',
            'ver_liquidaciones',
            'ver_solicitudes', 'crear_solicitudes', 'editar_solicitudes', 'enviar_estudio_sudamericana',
            'ver_actas', 'crear_actas', 'editar_actas', 'firmar_actas',
            'ver_terceros', 'crear_terceros', 'editar_terceros',
            'ver_reportes', 'descargar_reportes',
            'ver_dashboard',
        ]);

        // 4. Contador — solo contabilidad y reportes
        $contador = Role::firstOrCreate(['name' => 'contador', 'guard_name' => 'web']);
        $contador->syncPermissions([
            'ver_inmuebles',
            'ver_contratos_arriendo',
            'ver_facturas', 'ver_pagos',
            'ver_liquidaciones',
            'ver_contabilidad', 'crear_asientos', 'contabilizar_asientos', 'anular_asientos',
            'ver_reportes', 'descargar_reportes',
            'ver_dashboard',
        ]);

        // 5. Solo lectura — auditor, visitante
        $lector = Role::firstOrCreate(['name' => 'solo_lectura', 'guard_name' => 'web']);
        $lector->syncPermissions([
            'ver_inmuebles',
            'ver_contratos_arriendo',
            'ver_facturas', 'ver_pagos',
            'ver_liquidaciones',
            'ver_solicitudes',
            'ver_actas',
            'ver_terceros',
            'ver_reportes', 'descargar_reportes',
            'ver_dashboard',
        ]);

        $this->command->info('✅ Roles y permisos creados:');
        $this->command->table(
            ['Rol', 'Permisos'],
            Role::all()->map(fn ($r) => [$r->name, $r->permissions()->count() . ' permisos'])->toArray()
        );
    }
}

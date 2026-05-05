<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        $bogota    = DB::table('municipios')->where('codigo_dane', '11001')->first();
        $bogotaDep = DB::table('departamentos')->where('codigo_dane', '11')->first();

        DB::table('companies')->insert([
            'tipo_persona'                    => 'juridica',
            'razon_social'                    => 'Yarom Inmobiliaria S.A.S.',
            'nombre_comercial'                => 'Yarom Inmobiliaria',
            'nit'                             => '900000000',
            'digito_verificacion'             => 0,
            'nit_completo'                    => '900000000-0',
            'matricula_mercantil'             => '0000000',
            'camara_comercio'                 => 'Cámara de Comercio de Bogotá',
            'codigo_ciiu'                     => '6810',
            'descripcion_ciiu'                => 'Actividades inmobiliarias realizadas con bienes propios o arrendados',
            'tipo_contribuyente'              => 'persona_juridica',
            'regimen_fiscal'                  => 'ordinario',
            'responsable_iva'                 => true,
            'tarifa_iva'                      => 19.00,
            'gran_contribuyente'              => false,
            'autorretenedor'                  => false,
            'agente_retencion_fuente'         => true,
            'agente_reteica'                  => false,
            'agente_reteiva'                  => false,
            'tarifa_retefuente_servicios'     => 4.00,
            'tarifa_retefuente_honorarios'    => 10.00,
            'tarifa_retefuente_arrendamiento' => 3.50,
            'factura_electronica_activa'      => false,
            'rep_legal_nombre'                => 'Administrador Principal',
            'rep_legal_tipo_doc'              => 'CC',
            'rep_legal_documento'             => '00000000',
            'rep_legal_email'                 => 'legal@yarom.com',
            'rep_legal_telefono'              => '+57 300 0000000',
            'direccion'                       => 'Calle 000 # 00 - 00',
            'barrio'                          => 'Centro',
            'municipio_id'                    => $bogota?->id,
            'departamento_id'                 => $bogotaDep?->id,
            'pais_id'                         => 1,
            'codigo_postal'                   => '110111',
            'telefono'                        => '+57 601 0000000',
            'celular'                         => '+57 300 0000000',
            'email'                           => 'info@yarom.com',
            'email_notificaciones'            => 'notificaciones@yarom.com',
            'sitio_web'                       => 'https://yarom.com',
            'color_primario'                  => '#E11D48',
            'color_secundario'                => '#2563EB',
            'comision_administracion'         => 10.00,
            'dia_corte_mensual'               => 5,
            'dias_gracia_mora'                => 5,
            'tasa_mora_mensual'               => 1.5441,
            'is_active'                       => true,
            'created_at'                      => now(),
            'updated_at'                      => now(),
        ]);
    }
}

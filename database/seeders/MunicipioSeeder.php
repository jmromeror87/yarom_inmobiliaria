<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MunicipioSeeder extends Seeder
{
    public function run(): void
    {
        $municipios = [
            ['11','11001','Bogotá D.C.','especial'],
            ['05','05001','Medellín','primera'],['05','05088','Bello','segunda'],
            ['05','05266','Envigado','segunda'],['05','05360','Itagüí','segunda'],
            ['05','05631','Rionegro','segunda'],
            ['08','08001','Barranquilla','especial'],['08','08758','Soledad','primera'],
            ['08','08573','Puerto Colombia','quinta'],
            ['13','13001','Cartagena','especial'],['13','13430','Magangué','segunda'],
            ['15','15001','Tunja','segunda'],['15','15238','Duitama','tercera'],['15','15660','Sogamoso','tercera'],
            ['17','17001','Manizales','primera'],
            ['18','18001','Florencia','segunda'],
            ['19','19001','Popayán','segunda'],
            ['20','20001','Valledupar','primera'],
            ['23','23001','Montería','primera'],
            ['25','25175','Chía','tercera'],['25','25269','Facatativá','tercera'],
            ['25','25290','Fusagasugá','tercera'],['25','25307','Girardot','tercera'],
            ['25','25430','Madrid','cuarta'],['25','25473','Mosquera','tercera'],
            ['25','25662','Soacha','segunda'],['25','25851','Zipaquirá','tercera'],
            ['25','25793','Tocancipá','cuarta'],
            ['27','27001','Quibdó','segunda'],
            ['41','41001','Neiva','primera'],
            ['44','44001','Riohacha','segunda'],['44','44430','Maicao','tercera'],
            ['47','47001','Santa Marta','primera'],
            ['50','50001','Villavicencio','primera'],
            ['52','52001','Pasto','primera'],['52','52356','Ipiales','tercera'],
            ['54','54001','Cúcuta','primera'],['54','54498','Ocaña','tercera'],
            ['63','63001','Armenia','primera'],
            ['66','66001','Pereira','primera'],['66','66170','Dosquebradas','segunda'],
            ['66','66682','Santa Rosa de Cabal','cuarta'],
            ['68','68001','Bucaramanga','primera'],['68','68276','Floridablanca','segunda'],
            ['68','68307','Girón','segunda'],['68','68547','Piedecuesta','tercera'],
            ['68','68081','Barrancabermeja','segunda'],
            ['70','70001','Sincelejo','primera'],
            ['73','73001','Ibagué','primera'],
            ['76','76001','Cali','especial'],['76','76520','Palmira','primera'],
            ['76','76100','Buenaventura','primera'],['76','76109','Buga','tercera'],
            ['76','76130','Cartago','segunda'],['76','76364','Jamundí','tercera'],
            ['76','76834','Tuluá','segunda'],['76','76892','Yumbo','tercera'],
            ['81','81001','Arauca','segunda'],
            ['85','85001','Yopal','segunda'],
            ['86','86001','Mocoa','segunda'],
            ['88','88001','San Andrés','segunda'],
            ['91','91001','Leticia','segunda'],
            ['94','94001','Inírida','segunda'],
            ['95','95001','San José del Guaviare','segunda'],
            ['97','97001','Mitú','segunda'],
            ['99','99001','Puerto Carreño','segunda'],
        ];

        foreach ($municipios as [$dpto, $codigo, $nombre, $categoria]) {
            $dep = DB::table('departamentos')->where('codigo_dane', $dpto)->first();
            if ($dep) {
                DB::table('municipios')->insert([
                    'departamento_id' => $dep->id, 'codigo_dane' => $codigo,
                    'nombre' => $nombre, 'categoria' => $categoria,
                    'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        }
    }
}

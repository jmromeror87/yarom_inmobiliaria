<?php

namespace App\Services;

use App\Models\RentBill;
use App\Models\RentalContract;
use App\Models\AdministrationContract;
use App\Models\ContractAmendment;
use App\Models\PropertyService;
use App\Models\Property;
use App\Models\Third;
use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;

class AgenteService
{
    private string $model = 'gpt-4o-mini';

    public function chat(array $historial): array
    {
        $mensajes = array_merge(
            [['role' => 'system', 'content' => $this->getSystemPrompt()]],
            $historial
        );

        $herramientasUsadas = [];
        $maxIteraciones = 8;

        // En el primer turno de la conversación forzamos resumen_sistema
        // para que Inmo siempre arranque con datos reales del sistema.
        $primerTurno = count($historial) === 1;

        for ($i = 0; $i < $maxIteraciones; $i++) {
            $toolChoice = ($i === 0 && $primerTurno)
                ? ['type' => 'function', 'function' => ['name' => 'resumen_sistema']]
                : 'auto';

            $respuesta = OpenAI::chat()->create([
                'model'       => $this->model,
                'messages'    => $mensajes,
                'tools'       => $this->getHerramientas(),
                'tool_choice' => $toolChoice,
            ]);

            $choice  = $respuesta->choices[0];
            $mensaje = $choice->message;

            // Sin tool calls → respuesta final
            if (empty($mensaje->toolCalls)) {
                return [
                    'texto'             => $mensaje->content ?? '',
                    'herramientas_usadas' => $herramientasUsadas,
                ];
            }

            // Agregar mensaje del asistente con tool_calls
            $mensajes[] = $mensaje->toArray();

            // Ejecutar cada herramienta
            foreach ($mensaje->toolCalls as $toolCall) {
                $nombre = $toolCall->function->name;
                $input  = json_decode($toolCall->function->arguments, true) ?? [];

                $herramientasUsadas[] = $nombre;

                try {
                    $resultado = $this->ejecutarHerramienta($nombre, $input);
                } catch (\Throwable $e) {
                    $resultado = 'Error ejecutando herramienta: ' . $e->getMessage();
                    Log::error("Inmo herramienta [{$nombre}]: " . $e->getMessage());
                }

                $mensajes[] = [
                    'role'         => 'tool',
                    'tool_call_id' => $toolCall->id,
                    'content'      => $resultado,
                ];
            }
        }

        return ['texto' => 'Alcancé el límite de iteraciones.', 'herramientas_usadas' => $herramientasUsadas];
    }

    private function getSystemPrompt(): string
    {
        $fecha = now()->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY');
        $hora  = now()->format('H:i');
        return <<<PROMPT
Eres **Inmo**, agente inteligente autónomo especializado en gestión inmobiliaria de Serviarrendar S.A.S (Colombia).

📅 Hoy: {$fecha} — {$hora}

## REGLA PRINCIPAL — SIEMPRE CONSULTA ANTES DE RESPONDER
Ante CUALQUIER mensaje del usuario — incluso saludos como "hola", "buenos días", "cómo estás" — DEBES llamar primero a `resumen_sistema` y presentar el estado real del sistema. Nunca respondas sin datos reales cuando sea posible obtenerlos.

## Comportamiento autónomo
- Al recibir un saludo o mensaje genérico → llama `resumen_sistema` y presenta el estado actual con alertas prioritarias.
- Al preguntar por morosos → llama `buscar_morosos` con dias_mora_minimo=1 automáticamente.
- Al preguntar por contratos → llama `buscar_contratos_por_vencer` automáticamente.
- Al mencionar un nombre de persona → llama `buscar_tercero` de inmediato.
- Al pedir crear un servicio para un inmueble → llama `buscar_inmueble` primero para obtener el ID, luego `crear_servicio`. Los inmuebles pueden estar en cualquier estado (arrendado, disponible, etc.).
- Encadena herramientas si necesitas más contexto: primero consulta, luego responde.
- Si ves mora alta o contratos por vencer pronto → alerta proactivamente aunque no te lo pidan.

## Cómo presentas los datos
- Empieza SIEMPRE con el dato más urgente/importante.
- Usa formato estructurado:
  🔴 URGENTE / ⚠️ ATENCIÓN / ✅ OK
- Listas con • para múltiples ítems.
- **Negrita** para valores monetarios y nombres clave.
- Números en COP: $1.200.000 (punto para miles, coma para decimales).
- Termina siempre con "¿Qué quieres hacer?" o una acción sugerida concreta.

## Nunca inventas datos
Solo reportas lo que retornan las herramientas. Si no hay datos → "Sin registros en este momento."

## Tono
Profesional, directo, español colombiano. Como un gerente de cartera eficiente.
PROMPT;
    }

    private function getHerramientas(): array
    {
        return [
            $this->tool('resumen_sistema', 'Resumen general del sistema: contratos activos, inmuebles, cobros pendientes.', []),

            $this->tool('buscar_morosos', 'Lista arrendatarios con facturas vencidas.', [
                'dias_mora_minimo' => ['type' => 'integer', 'description' => 'Días mínimos de mora (default 1)'],
            ]),

            $this->tool('buscar_contratos_por_vencer', 'Contratos de arriendo que vencen próximamente.', [
                'dias' => ['type' => 'integer', 'description' => 'Días hacia adelante (default 30)'],
            ]),

            $this->tool('listar_inmuebles', 'Lista inmuebles filtrados por estado.', [
                'estado' => ['type' => 'string', 'description' => 'disponible | arrendado | en_captacion | en_mantenimiento (opcional)'],
            ]),

            $this->tool('listar_servicios_pendientes', 'Servicios/mantenimientos pendientes o en proceso.', []),

            $this->tool('buscar_tercero', 'Busca un tercero por nombre, documento o teléfono.', [
                'busqueda' => ['type' => 'string', 'description' => 'Nombre, documento o teléfono'],
            ], ['busqueda']),

            $this->tool('enviar_whatsapp', 'Envía un WhatsApp a un número de teléfono.', [
                'telefono' => ['type' => 'string', 'description' => 'Número con código de país (ej: 573001234567)'],
                'mensaje'  => ['type' => 'string', 'description' => 'Texto del mensaje'],
            ], ['telefono', 'mensaje']),

            $this->tool('notificar_morosos_whatsapp', 'Envía WhatsApp masivo a todos los arrendatarios morosos.', [
                'dias_mora_minimo' => ['type' => 'integer', 'description' => 'Días mínimos de mora'],
                'mensaje_base'     => ['type' => 'string', 'description' => 'Plantilla con {nombre}, {valor}, {dias_mora}'],
            ], ['mensaje_base']),

            $this->tool('buscar_inmueble', 'Busca un inmueble por dirección o ID para obtener su ID exacto.', [
                'busqueda' => ['type' => 'string', 'description' => 'Dirección o parte de la dirección del inmueble'],
            ], ['busqueda']),

            $this->tool('crear_servicio', 'Crea un servicio o mantenimiento para un inmueble.', [
                'property_id'  => ['type' => 'integer', 'description' => 'ID del inmueble (usar buscar_inmueble si no se tiene)'],
                'tipo'         => ['type' => 'string', 'description' => 'Tipo: mantenimiento | reparacion | plomeria | electrico | pintura | fumigacion | aseo | otro'],
                'descripcion'  => ['type' => 'string', 'description' => 'Descripción del servicio'],
                'valor'        => ['type' => 'number', 'description' => 'Valor en pesos COP'],
                'quien_paga'   => ['type' => 'string', 'description' => 'propietario | arrendatario | empresa (default: propietario)'],
                'fecha_servicio' => ['type' => 'string', 'description' => 'Fecha YYYY-MM-DD (default: hoy)'],
            ], ['property_id', 'tipo', 'descripcion', 'valor']),

            $this->tool('crear_otrosi_borrador', 'Crea un borrador de otrosí para un contrato de arriendo.', [
                'rental_contract_id' => ['type' => 'integer', 'description' => 'ID del contrato'],
                'tipo'               => ['type' => 'string', 'description' => 'incremento_canon | prorroga | cesion_arrendatario | cambio_codeudor | adicion_areas | modificacion_clausula | cambio_comision | otro'],
                'titulo'             => ['type' => 'string', 'description' => 'Título del otrosí'],
                'descripcion'        => ['type' => 'string', 'description' => 'Descripción y justificación legal'],
                'valor_nuevo'        => ['type' => 'number', 'description' => 'Nuevo valor si aplica'],
                'fecha_fin_nueva'    => ['type' => 'string', 'description' => 'Nueva fecha fin YYYY-MM-DD si aplica'],
            ], ['rental_contract_id', 'tipo', 'titulo', 'descripcion']),
        ];
    }

    private function tool(string $name, string $description, array $properties, array $required = []): array
    {
        return [
            'type'     => 'function',
            'function' => [
                'name'        => $name,
                'description' => $description,
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => empty($properties) ? new \stdClass() : $properties,
                    'required'   => $required,
                ],
            ],
        ];
    }

    private function ejecutarHerramienta(string $nombre, array $input): string
    {
        return match($nombre) {
            'resumen_sistema'             => $this->toolResumenSistema(),
            'buscar_morosos'              => $this->toolBuscarMorosos($input),
            'buscar_contratos_por_vencer' => $this->toolContratosPorVencer($input),
            'listar_inmuebles'            => $this->toolListarInmuebles($input),
            'listar_servicios_pendientes' => $this->toolServiciosPendientes(),
            'buscar_tercero'              => $this->toolBuscarTercero($input),
            'enviar_whatsapp'             => $this->toolEnviarWhatsApp($input),
            'notificar_morosos_whatsapp'  => $this->toolNotificarMorosos($input),
            'buscar_inmueble'             => $this->toolBuscarInmueble($input),
            'crear_servicio'              => $this->toolCrearServicio($input),
            'crear_otrosi_borrador'       => $this->toolCrearOtrosi($input),
            default                       => "Herramienta '{$nombre}' no reconocida.",
        };
    }

    // ── HERRAMIENTAS ──────────────────────────────────────────

    private function toolResumenSistema(): string
    {
        $contratosArriendo   = RentalContract::where('estado','activo')->count();
        $contratosAdmin      = AdministrationContract::where('estado','activo')->count();
        $inmueblesTotal      = Property::count();
        $inmueblesDisp       = Property::where('estado','disponible')->count();
        $morosos             = RentBill::where('estado','pendiente')->where('fecha_limite_pago','<',today())->count();
        $moraValor           = RentBill::where('estado','pendiente')->where('fecha_limite_pago','<',today())->sum('saldo_pendiente');
        $serviciosPendientes = PropertyService::whereIn('estado',['pendiente','en_proceso'])->count();
        $vencen30            = RentalContract::where('estado','activo')->whereBetween('fecha_fin',[today(),today()->addDays(30)])->count();
        $vencen7             = RentalContract::where('estado','activo')->whereBetween('fecha_fin',[today(),today()->addDays(7)])->count();

        $lineas = [
            "=== ESTADO ACTUAL DEL SISTEMA ===",
            "",
            "CONTRATOS:",
            "- Arriendo activos: {$contratosArriendo}",
            "- Administración activos: {$contratosAdmin}",
            "",
            "INMUEBLES:",
            "- Total: {$inmueblesTotal} | Disponibles: {$inmueblesDisp}",
            "",
            "CARTERA:",
            "- Facturas en mora: {$morosos}",
            "- Valor total mora: $" . number_format($moraValor, 0, ',', '.'),
            "",
            "ALERTAS:",
        ];

        if ($vencen7 > 0)  $lineas[] = "⚠️ URGENTE: {$vencen7} contratos vencen en los próximos 7 días";
        if ($vencen30 > 0) $lineas[] = "⚠️ {$vencen30} contratos vencen en los próximos 30 días";
        if ($morosos > 0)  $lineas[] = "🔴 {$morosos} arrendatarios con facturas vencidas — mora: $" . number_format($moraValor,0,',','.');
        if ($serviciosPendientes > 0) $lineas[] = "🔧 {$serviciosPendientes} servicios/mantenimientos pendientes";
        if ($morosos === 0 && $vencen30 === 0 && $serviciosPendientes === 0) $lineas[] = "✅ Sin alertas urgentes";

        $lineas[] = "";
        $lineas[] = "Servicios pendientes: {$serviciosPendientes}";

        return implode("\n", $lineas);
    }

    private function toolBuscarMorosos(array $input): string
    {
        $dias = $input['dias_mora_minimo'] ?? 1;
        $facturas = RentBill::with(['rentalContract.arrendatario','rentalContract.property'])
            ->where('estado','pendiente')
            ->where('fecha_limite_pago','<=',today()->subDays($dias))
            ->orderBy('fecha_limite_pago')->limit(20)->get();

        if ($facturas->isEmpty()) return "No hay morosos con mora ≥ {$dias} días.";

        $r = "MOROSOS (mora ≥ {$dias} días):\n";
        foreach ($facturas as $f) {
            $a = $f->rentalContract->arrendatario;
            $p = $f->rentalContract->property;
            $diasMora = today()->diffInDays($f->fecha_limite_pago);
            $r .= "• {$a->nombre_completo} — {$p->direccion} — $" . number_format($f->saldo_pendiente,0,',','.') . " — {$diasMora} días — Tel: " . ($a->celular ?? 'sin tel') . "\n";
        }
        return $r;
    }

    private function toolContratosPorVencer(array $input): string
    {
        $dias = $input['dias'] ?? 30;
        $contratos = RentalContract::with(['arrendatario','property'])
            ->where('estado','activo')
            ->whereBetween('fecha_fin',[today(), today()->addDays($dias)])
            ->orderBy('fecha_fin')->get();

        if ($contratos->isEmpty()) return "No hay contratos que venzan en {$dias} días.";

        $r = "CONTRATOS POR VENCER ({$dias} días):\n";
        foreach ($contratos as $c) {
            $r .= "• {$c->numero_contrato} — {$c->property->direccion} — {$c->arrendatario->nombre_completo} — Vence: {$c->fecha_fin->format('d/m/Y')} (" . today()->diffInDays($c->fecha_fin) . " días)\n";
        }
        return $r;
    }

    private function toolListarInmuebles(array $input): string
    {
        $q = Property::with('municipio');
        if (!empty($input['estado'])) $q->where('estado', $input['estado']);
        $items = $q->limit(25)->get();

        if ($items->isEmpty()) return "No se encontraron inmuebles.";

        $r = "INMUEBLES:\n";
        foreach ($items as $p) {
            $r .= "• [{$p->estado}] {$p->direccion} {$p->apto_casa_oficina} — {$p->municipio?->nombre} — $" . number_format($p->canon_sugerido ?? 0,0,',','.') . "\n";
        }
        return $r;
    }

    private function toolServiciosPendientes(): string
    {
        $items = PropertyService::with(['property','proveedor'])
            ->whereIn('estado',['pendiente','en_proceso'])
            ->orderBy('fecha_servicio')->limit(20)->get();

        if ($items->isEmpty()) return "No hay servicios pendientes.";

        $r = "SERVICIOS PENDIENTES:\n";
        foreach ($items as $s) {
            $r .= "• {$s->numero} — {$s->property->direccion} — {$s->tipo_label} — {$s->proveedor->nombre_completo} — $" . number_format($s->valor,0,',','.') . "\n";
        }
        return $r;
    }

    private function toolBuscarTercero(array $input): string
    {
        $b = $input['busqueda'] ?? '';
        $items = Third::where('nombre_completo','like',"%{$b}%")
            ->orWhere('numero_documento','like',"%{$b}%")
            ->orWhere('celular','like',"%{$b}%")
            ->limit(5)->get();

        if ($items->isEmpty()) return "No se encontraron terceros con '{$b}'.";

        $r = "TERCEROS:\n";
        foreach ($items as $t) {
            $r .= "• {$t->nombre_completo} — {$t->tipo_documento} {$t->numero_documento} — Tel: {$t->celular} — {$t->email}\n";
        }
        return $r;
    }

    private function toolEnviarWhatsApp(array $input): string
    {
        $wa = app(WhatsAppService::class);
        if (!$wa->isConnected()) return "WhatsApp no conectado. Inicia con: cd ~/whatsapp-service && node server.js";
        $r = $wa->enviar($input['telefono'], $input['mensaje']);
        return ($r['ok'] ?? false) ? "Mensaje enviado a {$input['telefono']}." : "Error: " . ($r['error'] ?? 'desconocido');
    }

    private function toolNotificarMorosos(array $input): string
    {
        $dias = $input['dias_mora_minimo'] ?? 1;
        $plantilla = $input['mensaje_base'] ?? '';
        $facturas = RentBill::with(['rentalContract.arrendatario'])
            ->where('estado','pendiente')
            ->where('fecha_limite_pago','<=',today()->subDays($dias))
            ->get();

        if ($facturas->isEmpty()) return "No hay morosos con mora ≥ {$dias} días.";

        $wa = app(WhatsAppService::class);
        $enviados = $sinTel = $errores = 0;

        foreach ($facturas as $f) {
            $a = $f->rentalContract->arrendatario;
            $tel = $a->celular ?? $a->telefono ?? null;
            if (!$tel) { $sinTel++; continue; }

            $msg = str_replace(['{nombre}','{valor}','{dias_mora}'],
                [$a->nombre_completo, number_format($f->saldo_pendiente,0,',','.'), today()->diffInDays($f->fecha_limite_pago)],
                $plantilla);

            $r = $wa->isConnected() ? $wa->enviar($tel, $msg) : ['ok'=>false];
            ($r['ok'] ?? false) ? $enviados++ : $errores++;
        }

        return "Notificación masiva:\n- Enviados: {$enviados}\n- Sin teléfono: {$sinTel}\n- Errores: {$errores}";
    }

    private function toolBuscarInmueble(array $input): string
    {
        $b = trim($input['busqueda'] ?? '');

        $query = Property::with('municipio');

        if (is_numeric($b)) {
            $query->where('id', (int)$b);
        } else {
            // Busca por cada palabra de la frase para mayor tolerancia
            $palabras = array_filter(explode(' ', $b));
            $query->where(function ($q) use ($palabras, $b) {
                $q->where('direccion', 'like', "%{$b}%");
                foreach ($palabras as $p) {
                    if (strlen($p) >= 3) {
                        $q->orWhere('direccion', 'like', "%{$p}%");
                    }
                }
            });
        }

        $items = $query->limit(10)->get();

        // Si no encontró nada, devuelve todos (son pocos en este sistema)
        if ($items->isEmpty()) {
            $items = Property::with('municipio')->limit(20)->get();
            if ($items->isEmpty()) return "No hay inmuebles registrados en el sistema.";
            $r = "No encontré '{$b}' exacto. Todos los inmuebles disponibles:\n";
        } else {
            $r = "INMUEBLES ENCONTRADOS:\n";
        }

        foreach ($items as $p) {
            $r .= "• ID:{$p->id} — {$p->direccion} {$p->apto_casa_oficina} — [{$p->estado}]\n";
        }
        return $r;
    }

    private function toolCrearServicio(array $input): string
    {
        $property = Property::find($input['property_id'] ?? 0);
        if (!$property) return "No se encontró el inmueble ID {$input['property_id']}. Usa buscar_inmueble para obtener el ID correcto.";

        $numero = 'SRV-' . now()->format('Y') . '-' . str_pad(PropertyService::withTrashed()->count() + 1, 4, '0', STR_PAD_LEFT);

        $servicio = PropertyService::create([
            'property_id'  => $property->id,
            'numero'       => $numero,
            'tipo'         => $input['tipo'] ?? 'mantenimiento',
            'descripcion'  => $input['descripcion'],
            'valor'        => $input['valor'],
            'iva'          => 0,
            'retencion'    => 0,
            'quien_paga'   => $input['quien_paga'] ?? 'propietario',
            'fecha_servicio' => $input['fecha_servicio'] ?? today()->toDateString(),
            'estado'       => 'pendiente',
            'estado_pago_proveedor' => 'pendiente',
            'created_by'   => auth()->id(),
        ]);

        return "✅ Servicio creado:\n" .
            "- Número: {$servicio->numero}\n" .
            "- Inmueble: {$property->direccion}\n" .
            "- Tipo: {$servicio->tipo}\n" .
            "- Valor: $" . number_format($servicio->valor, 0, ',', '.') . "\n" .
            "- Quién paga: {$servicio->quien_paga}\n" .
            "- Estado: Pendiente";
    }

    private function toolCrearOtrosi(array $input): string
    {
        $c = RentalContract::find($input['rental_contract_id'] ?? 0);
        if (!$c) return "No se encontró el contrato ID {$input['rental_contract_id']}.";

        $o = ContractAmendment::create([
            'rental_contract_id'       => $c->id,
            'tipo'                     => $input['tipo'],
            'titulo'                   => $input['titulo'],
            'descripcion'              => $input['descripcion'],
            'valor_nuevo'              => $input['valor_nuevo'] ?? null,
            'fecha_fin_nueva'          => $input['fecha_fin_nueva'] ?? null,
            'fecha_firma'              => today(),
            'fecha_vigencia'           => today(),
            'estado'                   => 'borrador',
            'aplica_cambio_automatico' => true,
        ]);

        return "Otrosí creado:\n- Número: {$o->numero}\n- Contrato: {$c->numero_contrato}\n- Tipo: {$o->tipo_label}\n- Estado: Borrador";
    }
}

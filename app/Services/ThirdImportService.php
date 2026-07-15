<?php

namespace App\Services;

use App\Exports\Thirds\ThirdTemplateExporter;
use App\Models\Departamento;
use App\Models\Municipio;
use App\Models\Third;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Importa (o valida sin importar) terceros desde el Excel generado por ThirdTemplateExporter.
 * Filas cuyo numero_documento ya exista se omiten (no se sobrescriben).
 *
 * Cada fila procesada queda registrada en `filas` con: fila, numero_documento,
 * nombre, estado (creado|valido|omitido_duplicado|error) y motivo.
 */
class ThirdImportService
{
    /** @var array<int, array{fila:int, numero_documento:string, nombre:string, estado:string, motivo:string}> */
    private array $filas = [];

    public function importFrom(string $filePath, bool $dryRun = false): array
    {
        $this->filas = [];

        $spreadsheet = IOFactory::load($filePath);
        $sheet       = $spreadsheet->getSheetByName('Terceros') ?? $spreadsheet->getSheet(0);

        $columnKeys = array_keys(ThirdTemplateExporter::COLUMNS);
        $headerRow  = 1;
        $columnMap  = [];

        $col = 1;
        foreach ($columnKeys as $key) {
            $letter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
            $columnMap[$letter] = $key;
            $col++;
        }

        $highestRow = $sheet->getHighestDataRow();

        for ($row = $headerRow + 1; $row <= $highestRow; $row++) {
            $data = [];
            foreach ($columnMap as $letter => $key) {
                $value = $sheet->getCell("{$letter}{$row}")->getValue();
                $data[$key] = is_string($value) ? trim($value) : $value;
            }

            if (empty(array_filter($data, fn ($v) => $v !== null && $v !== ''))) {
                continue; // fila vacía, ni siquiera se reporta
            }

            $this->procesarFila($row, $data, $dryRun);
        }

        return $this->resumen();
    }

    private function procesarFila(int $row, array $data, bool $dryRun): void
    {
        $numeroDocumento = (string) ($data['numero_documento'] ?? '');
        $nombre          = $this->nombreParaMostrar($data);

        if ($numeroDocumento === '') {
            $this->registrar($row, '', $nombre, 'error', 'Falta el número de documento (campo obligatorio).');
            return;
        }

        if (! in_array($data['tipo_persona'] ?? null, ['natural', 'juridica'])) {
            $this->registrar($row, $numeroDocumento, $nombre, 'error', 'Tipo de persona vacío o inválido (debe ser "natural" o "juridica").');
            return;
        }

        if ($data['tipo_persona'] === 'natural' && empty($data['primer_nombre']) && empty($data['primer_apellido'])) {
            $this->registrar($row, $numeroDocumento, $nombre, 'error', 'Persona natural sin primer nombre ni primer apellido.');
            return;
        }

        if ($data['tipo_persona'] === 'juridica' && empty($data['razon_social'])) {
            $this->registrar($row, $numeroDocumento, $nombre, 'error', 'Persona jurídica sin razón social.');
            return;
        }

        if (! in_array($data['tipo_documento'] ?? null, ['CC','CE','NIT','Pasaporte','TI','PEP','PPT'])) {
            $this->registrar($row, $numeroDocumento, $nombre, 'error', 'Tipo de documento inválido (use CC, CE, NIT, Pasaporte, TI, PEP o PPT).');
            return;
        }

        $sinRol = ! $this->toBool($data['es_propietario'] ?? null)
            && ! $this->toBool($data['es_arrendatario'] ?? null)
            && ! $this->toBool($data['es_cliente_compra'] ?? null)
            && ! $this->toBool($data['es_fiador'] ?? null)
            && ! $this->toBool($data['es_proveedor'] ?? null);

        if ($sinRol) {
            $this->registrar($row, $numeroDocumento, $nombre, 'error', 'No tiene ningún rol marcado (Propietario/Arrendatario/Fiador/Cliente compra/Proveedor).');
            return;
        }

        if (Third::where('numero_documento', $numeroDocumento)->exists()) {
            $this->registrar($row, $numeroDocumento, $nombre, 'omitido_duplicado', 'Ya existe un tercero con este número de documento — no se modificó.');
            return;
        }

        if ($dryRun) {
            $this->registrar($row, $numeroDocumento, $nombre, 'valido', 'Listo para importar.');
            return;
        }

        try {
            $this->createThird($data);
            $this->registrar($row, $numeroDocumento, $nombre, 'creado', 'Tercero creado correctamente.');
        } catch (\Throwable $e) {
            $this->registrar($row, $numeroDocumento, $nombre, 'error', 'Error al guardar: ' . $e->getMessage());
        }
    }

    private function registrar(int $row, string $numeroDocumento, string $nombre, string $estado, string $motivo): void
    {
        $this->filas[] = [
            'fila'             => $row,
            'numero_documento' => $numeroDocumento,
            'nombre'           => $nombre,
            'estado'           => $estado,
            'motivo'           => $motivo,
        ];
    }

    private function nombreParaMostrar(array $data): string
    {
        if (($data['tipo_persona'] ?? null) === 'juridica') {
            return (string) ($data['razon_social'] ?? '');
        }

        return trim(($data['primer_nombre'] ?? '') . ' ' . ($data['primer_apellido'] ?? ''));
    }

    private function resumen(): array
    {
        $porEstado = fn (string $estado) => array_values(array_filter($this->filas, fn ($f) => $f['estado'] === $estado));

        return [
            'filas'               => $this->filas,
            'creados'             => $porEstado('creado'),
            'validos'             => $porEstado('valido'),
            'omitidos_existentes' => $porEstado('omitido_duplicado'),
            'errores'             => $porEstado('error'),
        ];
    }

    private function createThird(array $data): Third
    {
        $municipioId    = $this->resolveMunicipioId($data['municipio'] ?? null, $data['departamento'] ?? null);
        $departamentoId = $this->resolveDepartamentoId($data['departamento'] ?? null);

        $payload = [
            'es_propietario'    => $this->toBool($data['es_propietario'] ?? null),
            'es_arrendatario'   => $this->toBool($data['es_arrendatario'] ?? null),
            'es_cliente_compra' => $this->toBool($data['es_cliente_compra'] ?? null),
            'es_fiador'         => $this->toBool($data['es_fiador'] ?? null),
            'es_proveedor'      => $this->toBool($data['es_proveedor'] ?? null),

            'tipo_persona'      => $data['tipo_persona'],
            'tipo_documento'    => $data['tipo_documento'],
            'numero_documento'  => (string) $data['numero_documento'],
            'digito_verificacion' => $data['digito_verificacion'] !== '' ? $data['digito_verificacion'] : null,

            'primer_nombre'     => $data['primer_nombre'] ?: null,
            'segundo_nombre'    => $data['segundo_nombre'] ?: null,
            'primer_apellido'   => $data['primer_apellido'] ?: null,
            'segundo_apellido'  => $data['segundo_apellido'] ?: null,
            'razon_social'      => $data['razon_social'] ?: null,
            'nombre_comercial'  => $data['nombre_comercial'] ?: null,

            'email'                 => $data['email'] ?: null,
            'celular'               => $data['celular'] ?: null,
            'telefono_fijo'         => $data['telefono_fijo'] ?: null,
            'direccion_residencia'  => $data['direccion_residencia'] ?: null,
            'barrio_residencia'     => $data['barrio_residencia'] ?: null,
            'municipio_id'          => $municipioId,
            'departamento_id'       => $departamentoId,
            'nacionalidad'          => $data['nacionalidad'] ?: 'Colombiana',

            'banco'            => $data['banco'] ?: null,
            'tipo_cuenta'      => in_array($data['tipo_cuenta'] ?? null, ['ahorros', 'corriente']) ? $data['tipo_cuenta'] : null,
            'numero_cuenta'    => $data['numero_cuenta'] ?: null,
            'titular_cuenta'   => $data['titular_cuenta'] ?: null,
            'comision_pactada' => $data['comision_pactada'] !== '' ? $data['comision_pactada'] : null,

            'tipo_empleo'           => in_array($data['tipo_empleo'] ?? null, ['dependiente','independiente','pensionado','rentista','desempleado','otro']) ? $data['tipo_empleo'] : null,
            'empresa_donde_trabaja' => $data['empresa_donde_trabaja'] ?: null,
            'cargo'                 => $data['cargo'] ?: null,
            'ingresos_mensuales'    => $data['ingresos_mensuales'] !== '' ? $data['ingresos_mensuales'] : null,
            'otros_ingresos'        => $data['otros_ingresos'] !== '' ? $data['otros_ingresos'] : null,
        ];

        return Third::create($payload);
    }

    private function toBool(mixed $value): bool
    {
        return Str::upper((string) $value) === 'SI';
    }

    private function resolveDepartamentoId(?string $nombre): ?int
    {
        if (! $nombre) return null;

        return Departamento::whereRaw('LOWER(nombre) = ?', [Str::lower(trim($nombre))])->value('id');
    }

    private function resolveMunicipioId(?string $nombre, ?string $departamentoNombre): ?int
    {
        if (! $nombre) return null;

        $query = Municipio::whereRaw('LOWER(nombre) = ?', [Str::lower(trim($nombre))]);

        if ($departamentoNombre) {
            $depId = $this->resolveDepartamentoId($departamentoNombre);
            if ($depId) {
                $query->where('departamento_id', $depId);
            }
        }

        return $query->value('id');
    }
}

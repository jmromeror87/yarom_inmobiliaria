<?php

namespace App\Services;

use App\Exports\RentalContracts\RentalContractTemplateExporter;
use App\Models\AdministrationContract;
use App\Models\Property;
use App\Models\RentalContract;
use App\Models\RentalContractThird;
use App\Models\Third;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Importa (o valida sin importar) contratos de arriendo desde el Excel
 * generado por RentalContractTemplateExporter. El arrendatario se resuelve
 * por documento, el inmueble por dirección exacta (desambiguado por canon
 * si hay varias unidades con la misma dirección). El codeudor, si viene
 * diligenciado, se crea como Tercero (rol Fiador) si no existe y se vincula.
 */
class RentalContractImportService
{
    private array $filas = [];

    private const ESTADOS_VALIDOS = [
        'borrador','enviado_arrendatario','aprobado','firmado','activo','terminado','cancelado',
    ];

    private const GARANTIAS_VALIDAS = [
        'codeudor','garantia_bancaria','seguro_arrendamiento','ninguna',
    ];

    private const PLANTILLA_VIVIENDA_ID = 2; // "Contrato de Arrendamiento Vivienda Urbana"

    public function importFrom(string $filePath, bool $dryRun = false): array
    {
        $this->filas = [];

        $spreadsheet = IOFactory::load($filePath);
        $sheet       = $spreadsheet->getSheetByName('Contratos') ?? $spreadsheet->getSheet(0);

        $columnKeys = array_keys(RentalContractTemplateExporter::COLUMNS);
        $columnMap  = [];
        $col = 1;
        foreach ($columnKeys as $key) {
            $letter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
            $columnMap[$letter] = $key;
            $col++;
        }

        $highestRow = $sheet->getHighestDataRow();

        for ($row = 2; $row <= $highestRow; $row++) {
            $data = [];
            foreach ($columnMap as $letter => $key) {
                $value = $sheet->getCell("{$letter}{$row}")->getValue();
                $data[$key] = is_string($value) ? trim($value) : $value;
            }

            if (empty(array_filter($data, fn ($v) => $v !== null && $v !== ''))) {
                continue;
            }

            $this->procesarFila($row, $data, $dryRun);
        }

        return $this->resumen();
    }

    private function procesarFila(int $row, array $data, bool $dryRun): void
    {
        $documento = (string) ($data['arrendatario_documento'] ?? '');
        $direccion = (string) ($data['inmueble_direccion'] ?? '');

        if ($documento === '') {
            $this->registrar($row, '', $direccion, 'error', 'Falta el número de documento del arrendatario.');
            return;
        }

        $arrendatario = Third::where('numero_documento', $documento)->where('es_arrendatario', true)->first();
        if (! $arrendatario) {
            $this->registrar($row, $documento, $direccion, 'error', 'No existe ningún Arrendatario con ese número de documento.');
            return;
        }

        if ($direccion === '') {
            $this->registrar($row, $documento, $direccion, 'error', 'Falta la dirección del inmueble.');
            return;
        }

        $candidatos = Property::where('direccion', $direccion)->get();
        if ($candidatos->isEmpty()) {
            $this->registrar($row, $documento, $direccion, 'error', 'No se encontró ningún inmueble con esa dirección exacta.');
            return;
        }

        $idsConContratoVigente = RentalContract::whereIn('property_id', $candidatos->pluck('id'))
            ->whereIn('estado', ['activo', 'firmado', 'aprobado'])
            ->pluck('property_id')
            ->all();

        $disponibles = $candidatos->whereNotIn('id', $idsConContratoVigente);
        if ($disponibles->isEmpty()) {
            $this->registrar($row, $documento, $direccion, 'error', 'Todas las unidades de esa dirección ya tienen un contrato de arriendo vigente.');
            return;
        }

        $canonMensual = $data['canon_mensual'] ?? null;
        $property = $disponibles->count() === 1
            ? $disponibles->first()
            : ($disponibles->first(fn ($p) => (float) $p->canon_arriendo === (float) ($canonMensual ?: 0)) ?? $disponibles->first());

        $fechaInicio = $this->parsearFecha($data['fecha_inicio'] ?? null);
        $fechaFin    = $this->parsearFecha($data['fecha_fin'] ?? null);

        if (! $fechaInicio || ! $fechaFin) {
            $this->registrar($row, $documento, $direccion, 'error', 'Fecha de inicio o fin vacía o con formato inválido (use AAAA-MM-DD).');
            return;
        }

        if (! $canonMensual) {
            $this->registrar($row, $documento, $direccion, 'error', 'Falta el canon mensual.');
            return;
        }

        if ($dryRun) {
            $this->registrar($row, $documento, $direccion, 'valido', 'Listo para importar.');
            return;
        }

        try {
            $this->createContract($property, $arrendatario, $fechaInicio, $fechaFin, $canonMensual, $data);
            $this->registrar($row, $documento, $direccion, 'creado', 'Contrato creado correctamente.');
        } catch (\Throwable $e) {
            $this->registrar($row, $documento, $direccion, 'error', 'Error al guardar: ' . $e->getMessage());
        }
    }

    private function registrar(int $row, string $documento, string $direccion, string $estado, string $motivo): void
    {
        $this->filas[] = [
            'fila'                   => $row,
            'arrendatario_documento' => $documento,
            'inmueble_direccion'     => $direccion,
            'estado'                 => $estado,
            'motivo'                 => $motivo,
        ];
    }

    private function resumen(): array
    {
        $porEstado = fn (string $estado) => array_values(array_filter($this->filas, fn ($f) => $f['estado'] === $estado));

        return [
            'filas'   => $this->filas,
            'creados' => $porEstado('creado'),
            'validos' => $porEstado('valido'),
            'errores' => $porEstado('error'),
        ];
    }

    private function createContract(Property $property, Third $arrendatario, Carbon $fechaInicio, Carbon $fechaFin, $canonMensual, array $data): RentalContract
    {
        $estado = in_array($data['estado'] ?? null, self::ESTADOS_VALIDOS) ? $data['estado'] : 'activo';
        $tipoGarantia = in_array($data['tipo_garantia'] ?? null, self::GARANTIAS_VALIDAS) ? $data['tipo_garantia'] : 'ninguna';

        $administrationContractId = AdministrationContract::where('property_id', $property->id)
            ->whereIn('estado', ['activo', 'firmado', 'aprobado'])
            ->value('id');

        $contract = RentalContract::create([
            'property_id'                 => $property->id,
            'administration_contract_id'  => $administrationContractId,
            'contract_template_id'        => self::PLANTILLA_VIVIENDA_ID,
            'arrendatario_id'             => $arrendatario->id,
            'tipo'                        => 'vivienda_urbana',
            'canon_mensual'               => $canonMensual,
            'deposito'                    => $data['deposito'] ?: 0,
            'fecha_inicio'                => $fechaInicio,
            'fecha_fin'                   => $fechaFin,
            'duracion_meses'              => $data['duracion_meses'] ?: 12,
            'tipo_garantia'                => $tipoGarantia,
            'estado'                       => $estado,
            'notas'                        => $data['notas'] ?: null,
        ]);

        \App\Filament\Resources\RentalContracts\Schemas\RentalContractForm::copyClausesFromTemplate($contract);

        RentalContractThird::create([
            'rental_contract_id' => $contract->id,
            'third_id'           => $arrendatario->id,
            'rol'                => 'arrendatario',
        ]);

        $codeudorDocumento = (string) ($data['codeudor_documento'] ?? '');
        if ($codeudorDocumento !== '') {
            $codeudor = $this->resolverOCrearCodeudor($codeudorDocumento, $data);
            if ($codeudor) {
                RentalContractThird::create([
                    'rental_contract_id' => $contract->id,
                    'third_id'           => $codeudor->id,
                    'rol'                => 'codeudor',
                ]);
            }
        }

        return $contract;
    }

    private function resolverOCrearCodeudor(string $documento, array $data): ?Third
    {
        $existente = Third::where('numero_documento', $documento)->first();
        if ($existente) {
            return $existente;
        }

        $nombre   = trim((string) ($data['codeudor_nombre'] ?? ''));
        $apellido = trim((string) ($data['codeudor_apellido'] ?? ''));

        if ($nombre === '' && $apellido === '') {
            return null;
        }

        return Third::create([
            'es_fiador'            => true,
            'tipo_persona'         => 'natural',
            'tipo_documento'       => 'CC',
            'numero_documento'     => $documento,
            'primer_nombre'        => $nombre ?: null,
            'primer_apellido'      => $apellido ?: null,
            'celular'              => $data['codeudor_celular'] ?: null,
            'direccion_residencia' => $data['codeudor_direccion'] ?: null,
            'nacionalidad'         => 'Colombiana',
        ]);
    }

    private function parsearFecha(mixed $valor): ?Carbon
    {
        if (empty($valor)) {
            return null;
        }

        try {
            if (is_numeric($valor)) {
                return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($valor));
            }
            return Carbon::parse((string) $valor);
        } catch (\Throwable) {
            return null;
        }
    }
}

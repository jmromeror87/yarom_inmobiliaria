<?php

namespace App\Contracts\FE;

use App\Models\ElectronicInvoice;
use App\Models\RentBill;

interface FacturacionElectronicaContract
{
    /**
     * Emite la factura electrónica ante la DIAN vía operador.
     * Retorna un FEResponse con el resultado.
     */
    public function emitir(RentBill $bill, array $opciones = []): FEResponse;

    /**
     * Emite una nota crédito (anulación) para una FE ya aceptada.
     */
    public function anular(ElectronicInvoice $fe, string $razon): FEResponse;

    /**
     * Consulta el estado actual de una FE en el operador/DIAN.
     */
    public function consultarEstado(ElectronicInvoice $fe): FEResponse;

    /**
     * Descarga el PDF de representación gráfica.
     * Retorna el contenido binario del PDF o null si no está disponible.
     */
    public function descargarPdf(ElectronicInvoice $fe): ?string;
}

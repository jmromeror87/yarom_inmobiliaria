<?php

namespace App\Contracts\FE;

class FEResponse
{
    public function __construct(
        public readonly bool   $exitoso,
        public readonly string $estado,        // aceptada | rechazada | enviada | error
        public readonly ?string $cufe          = null,
        public readonly ?string $qrData        = null,
        public readonly ?string $xmlUrl        = null,
        public readonly ?string $pdfUrl        = null,
        public readonly ?string $attachedUrl   = null,
        public readonly ?string $mensajeDian   = null,
        public readonly ?string $codigoDian    = null,
        public readonly ?string $cufeCreditNote = null,
        public readonly array  $raw            = [],
        public readonly ?string $error         = null,
    ) {}

    public static function exito(
        string $estado,
        ?string $cufe        = null,
        ?string $qrData      = null,
        ?string $xmlUrl      = null,
        ?string $pdfUrl      = null,
        ?string $attachedUrl = null,
        ?string $mensajeDian = null,
        ?string $codigoDian  = null,
        array   $raw         = [],
    ): self {
        return new self(
            exitoso: true,
            estado:  $estado,
            cufe:    $cufe,
            qrData:  $qrData,
            xmlUrl:  $xmlUrl,
            pdfUrl:  $pdfUrl,
            attachedUrl: $attachedUrl,
            mensajeDian: $mensajeDian,
            codigoDian:  $codigoDian,
            raw:     $raw,
        );
    }

    public static function error(string $mensaje, array $raw = []): self
    {
        return new self(
            exitoso: false,
            estado:  'error',
            error:   $mensaje,
            raw:     $raw,
        );
    }

    public static function rechazada(string $mensajeDian, string $codigoDian = '', array $raw = []): self
    {
        return new self(
            exitoso:     false,
            estado:      'rechazada',
            mensajeDian: $mensajeDian,
            codigoDian:  $codigoDian,
            raw:         $raw,
        );
    }
}

<?php

namespace App\Services;

use App\Models\RentBill;

class WompiService
{
    /**
     * $montoOverride permite cobrar un valor distinto al saldo_pendiente de
     * esta factura (pagar varios meses de una vez, o un abono libre) — el
     * webhook solo necesita el número de la factura como ancla (primeros 3
     * segmentos de la referencia) para reconciliar el pago, por lo que la
     * referencia se genera nueva cada vez en lugar de reutilizar una fija.
     */
    public function checkoutUrl(RentBill $bill, ?float $montoOverride = null): string
    {
        $monto        = $montoOverride ?? (float) $bill->saldo_pendiente;
        $amountCents  = (int) round($monto * 100);
        $reference    = $bill->numero . '-' . uniqid();
        $currency     = 'COP';
        $integrity    = config('wompi.integrity_secret');
        $signature    = hash('sha256', $reference . $amountCents . $currency . $integrity);
        $redirectUrl  = route('payment.resultado');
        $base         = 'https://checkout.wompi.co/p/';

        return $base . '?' . http_build_query([
            'public-key'          => config('wompi.public_key'),
            'currency'            => $currency,
            'amount-in-cents'     => $amountCents,
            'reference'           => $reference,
            'signature:integrity' => $signature,
            'redirect-url'        => $redirectUrl,
        ]);
    }

    /**
     * Verifica la firma del evento tal como Wompi realmente la envía: el propio
     * payload trae qué propiedades firmar (signature.properties, rutas tipo
     * "transaction.id") y el checksum a comparar (signature.checksum) — no es
     * un checksum fijo de 4 campos ni viene en un header.
     */
    public function verifyWebhook(array $payload): bool
    {
        $properties = $payload['signature']['properties'] ?? [];
        $checksum   = $payload['signature']['checksum'] ?? '';
        $timestamp  = $payload['timestamp'] ?? '';
        $secret     = config('wompi.events_secret');

        if (empty($properties) || !$checksum) return false;

        $concatenado = '';
        foreach ($properties as $ruta) {
            $valor = data_get($payload['data'] ?? [], $ruta);
            $concatenado .= $valor;
        }

        $firmaCalculada = hash('sha256', $concatenado . $timestamp . $secret);

        return hash_equals($firmaCalculada, $checksum);
    }

    public function transactionStatus(string $transactionId): ?array
    {
        $base = config('wompi.env') === 'production'
            ? 'https://production.wompi.co/v1'
            : 'https://sandbox.wompi.co/v1';

        $response = \Illuminate\Support\Facades\Http::withToken(config('wompi.private_key'))
            ->get("{$base}/transactions/{$transactionId}");

        return $response->successful() ? $response->json('data') : null;
    }
}

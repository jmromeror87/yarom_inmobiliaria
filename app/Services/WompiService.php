<?php

namespace App\Services;

use App\Models\RentBill;

class WompiService
{
    public function checkoutUrl(RentBill $bill): string
    {
        $amountCents  = (int) round($bill->saldo_pendiente * 100);
        if (!$bill->wompi_reference) {
            $bill->update(['wompi_reference' => $bill->numero . '-' . substr($bill->payment_token, 0, 8)]);
            $bill->refresh();
        }
        $reference = $bill->wompi_reference;
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

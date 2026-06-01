<?php

namespace App\Services;

use App\Models\RentBill;

class WompiService
{
    public function checkoutUrl(RentBill $bill): string
    {
        $amountCents  = (int) round($bill->saldo_pendiente * 100);
        $reference    = $bill->wompi_reference ?? ($bill->numero . '-' . substr($bill->payment_token, 0, 8));
        $currency     = 'COP';
        $integrity    = config('wompi.integrity_secret');
        $signature    = hash('sha256', $reference . $amountCents . $currency . $integrity);
        $redirectUrl  = route('payment.resultado');
        $base         = config('wompi.env') === 'production'
            ? 'https://checkout.wompi.co/p/'
            : 'https://checkout.wompi.co/p/';

        return $base . '?' . http_build_query([
            'public-key'          => config('wompi.public_key'),
            'currency'            => $currency,
            'amount-in-cents'     => $amountCents,
            'reference'           => $reference,
            'signature:integrity' => $signature,
            'redirect-url'        => $redirectUrl,
        ]);
    }

    public function verifyWebhook(array $data, string $checksum): bool
    {
        $t  = $data['data']['transaction'] ?? [];
        $e  = config('wompi.events_secret');
        $sig = hash('sha256',
            ($t['id']                  ?? '') .
            ($t['status']              ?? '') .
            ($t['payment_method_type'] ?? '') .
            ($t['amount_in_cents']     ?? '') .
            $e
        );
        return hash_equals($sig, $checksum);
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

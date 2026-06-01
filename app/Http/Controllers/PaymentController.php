<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\RentBill;
use App\Models\RentPayment;
use App\Services\WompiService;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function show(string $token)
    {
        $bill = RentBill::where('payment_token', $token)
            ->with(['arrendatario', 'property', 'rentalContract'])
            ->firstOrFail();

        $company = Company::first();

        if ($bill->estado === 'pagada') {
            return view('payment.show', compact('bill', 'company', 'token'))
                ->with('status', 'pagada');
        }

        if ($bill->payment_token_expires_at && $bill->payment_token_expires_at->isPast()) {
            return view('payment.show', compact('bill', 'company', 'token'))
                ->with('status', 'expirado');
        }

        $wompiUrl = app(WompiService::class)->checkoutUrl($bill);

        return view('payment.show', compact('bill', 'company', 'token', 'wompiUrl'))
            ->with('status', 'activo');
    }

    public function resultado(Request $request)
    {
        $transactionId = $request->get('id');
        $transaction   = null;
        $bill          = null;

        if ($transactionId) {
            $transaction = app(WompiService::class)->transactionStatus($transactionId);

            if ($transaction) {
                $reference = $transaction['reference'] ?? null;
                if ($reference) {
                    $numero = explode('-', $reference);
                    $billNumero = implode('-', array_slice($numero, 0, 3));
                    $bill = RentBill::where('numero', $billNumero)->first();
                }
            }
        }

        $company = Company::first();
        return view('payment.resultado', compact('transaction', 'bill', 'company'));
    }

    public function webhook(Request $request)
    {
        $payload   = $request->all();
        $checksum  = $request->header('X-Event-Checksum') ?? '';

        if (!app(WompiService::class)->verifyWebhook($payload, $checksum)) {
            Log::warning('Wompi webhook: firma inválida');
            return response()->json(['ok' => false], 401);
        }

        $event = $payload['event'] ?? '';
        if ($event !== 'transaction.updated') {
            return response()->json(['ok' => true]);
        }

        $t = $payload['data']['transaction'] ?? [];
        if (($t['status'] ?? '') !== 'APPROVED') {
            return response()->json(['ok' => true]);
        }

        DB::transaction(function () use ($t) {
            $reference = $t['reference'] ?? '';
            $numero    = implode('-', array_slice(explode('-', $reference), 0, 3));
            $bill      = RentBill::where('numero', $numero)
                ->whereIn('estado', ['pendiente', 'parcial', 'vencida', 'en_mora'])
                ->lockForUpdate()->first();

            if (!$bill) return;

            $total = round(($t['amount_in_cents'] ?? 0) / 100, 2);

            $year   = now()->year;
            $ultimo = RentPayment::whereYear('created_at', $year)->max('numero');
            $count  = $ultimo ? ((int) substr($ultimo, -4)) + 1 : 1;
            $numero_pago = 'PAG-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

            RentPayment::create([
                'numero'             => $numero_pago,
                'rent_bill_id'       => $bill->id,
                'rental_contract_id' => $bill->rental_contract_id,
                'arrendatario_id'    => $bill->arrendatario_id,
                'total_pagado'       => $total,
                'forma_pago'         => $this->mapPaymentMethod($t['payment_method_type'] ?? ''),
                'fecha_pago'         => now()->toDateString(),
                'referencia_pago'    => $t['id'] ?? null,
            ]);

            $bill->update([
                'total_pagado'        => $bill->total_pagado + $total,
                'saldo_pendiente'     => max(0, $bill->saldo_pendiente - $total),
                'estado'              => 'pagada',
                'fecha_pago'          => now()->toDateString(),
                'wompi_transaction_id'=> $t['id'] ?? null,
            ]);

            // Confirmación por WhatsApp
            if ($bill->arrendatario?->celular) {
                $msg = "✅ *Pago recibido*\n\n"
                    . "Factura: *{$bill->numero}*\n"
                    . "Valor: *$" . number_format($total, 0, ',', '.') . " COP*\n"
                    . "Fecha: " . now()->format('d/m/Y') . "\n\n"
                    . "Gracias por su pago. — Serviarrendar S.A.S";

                app(WhatsAppService::class)->enviar($bill->arrendatario->celular, $msg);
            }
        });

        return response()->json(['ok' => true]);
    }

    private function mapPaymentMethod(string $type): string
    {
        return match ($type) {
            'NEQUI'        => 'nequi',
            'PSE'          => 'pse',
            'CARD'         => 'transferencia',
            'BANCOLOMBIA_TRANSFER' => 'transferencia',
            default        => 'otro',
        };
    }
}

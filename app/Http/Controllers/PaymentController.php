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

        if ($bill->estado === 'anulada') {
            return view('payment.show', compact('bill', 'company', 'token'))
                ->with('status', 'expirado');
        }

        if ($bill->payment_token_expires_at && $bill->payment_token_expires_at->isPast()) {
            return view('payment.show', compact('bill', 'company', 'token'))
                ->with('status', 'expirado');
        }

        // Meses pendientes del mismo contrato (incluye el mes actual aunque
        // todavía esté en período de gracia, para que el inquilino vea el
        // panorama completo), igual que en el aviso de WhatsApp consolidado.
        $pendientes = RentBill::where('rental_contract_id', $bill->rental_contract_id)
            ->whereIn('estado', ['pendiente', 'parcial', 'en_mora'])
            ->where('saldo_pendiente', '>', 0)
            ->where('fecha_limite_pago', '<', now())
            ->orderBy('periodo_inicio')
            ->get();

        if ($pendientes->isEmpty()) {
            $pendientes = collect([$bill]);
        }

        $wompi        = app(WompiService::class);
        $totalGeneral = (float) $pendientes->sum('saldo_pendiente');
        $billAncla    = $pendientes->first();

        $wompiUrlTodos = $wompi->checkoutUrl($billAncla, $totalGeneral);
        foreach ($pendientes as $p) {
            $p->wompiUrlMes = $wompi->checkoutUrl($p, (float) $p->saldo_pendiente);
        }

        return view('payment.show', compact('bill', 'company', 'token', 'pendientes', 'totalGeneral', 'wompiUrlTodos'))
            ->with('status', 'activo');
    }

    public function abono(Request $request, string $token)
    {
        $bill = RentBill::where('payment_token', $token)->firstOrFail();

        $pendientes = RentBill::where('rental_contract_id', $bill->rental_contract_id)
            ->whereIn('estado', ['pendiente', 'parcial', 'en_mora'])
            ->where('saldo_pendiente', '>', 0)
            ->orderBy('periodo_inicio')
            ->get();

        if ($pendientes->isEmpty()) {
            return redirect()->route('payment.show', ['token' => $token]);
        }

        $totalGeneral = (float) $pendientes->sum('saldo_pendiente');

        $monto = (float) $request->input('monto', 0);
        $monto = max(1000, min($monto, $totalGeneral));

        $wompiUrl = app(WompiService::class)->checkoutUrl($pendientes->first(), $monto);

        return redirect()->away($wompiUrl);
    }

    public function resultado(Request $request)
    {
        $transactionId = $request->query('id');
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
        $payload = $request->all();

        if (!app(WompiService::class)->verifyWebhook($payload)) {
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

            // Evitar procesar el mismo transaction_id dos veces (reenvíos del webhook)
            if ($bill->wompi_transaction_id === ($t['id'] ?? null)) return;

            $restante = round(($t['amount_in_cents'] ?? 0) / 100, 2);

            $bill->update(['wompi_transaction_id' => $t['id'] ?? null]);

            // El monto puede corresponder a varios meses a la vez ("pagar
            // todo") o a un abono libre — se aplica primero a esta factura
            // (la que ancla la referencia) y el sobrante se abona en cascada
            // a los siguientes meses pendientes del mismo contrato, del más
            // antiguo al más reciente, hasta agotar el monto pagado.
            $facturas = collect([$bill])->merge(
                RentBill::where('rental_contract_id', $bill->rental_contract_id)
                    ->where('id', '!=', $bill->id)
                    ->whereIn('estado', ['pendiente', 'parcial', 'vencida', 'en_mora'])
                    ->where('saldo_pendiente', '>', 0)
                    ->orderBy('periodo_inicio')
                    ->lockForUpdate()
                    ->get()
            );

            foreach ($facturas as $f) {
                if ($restante <= 0) break;

                $aplicar = min($restante, (float) $f->saldo_pendiente);
                if ($aplicar <= 0) continue;

                // RentPayment::booted() actualiza la factura y genera liquidación al propietario automáticamente
                RentPayment::create([
                    'rent_bill_id'       => $f->id,
                    'rental_contract_id' => $f->rental_contract_id,
                    'arrendatario_id'    => $f->arrendatario_id,
                    'total_pagado'       => $aplicar,
                    'valor_canon'        => min($aplicar, $f->canon_base),
                    'valor_mora'         => max(0, $aplicar - $f->canon_base - $f->cuota_administracion),
                    'valor_administracion' => $f->cuota_administracion,
                    'forma_pago'         => $this->mapPaymentMethod($t['payment_method_type'] ?? ''),
                    'fecha_pago'         => now()->toDateString(),
                    'referencia_pago'    => $t['id'] ?? null,
                    'banco_origen'       => $t['payment_method']['financial_institution_code'] ?? null,
                ]);

                $restante = round($restante - $aplicar, 2);
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

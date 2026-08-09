<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\TicketTier;
use App\Services\FlutterwaveService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use RuntimeException;

class PaymentController extends Controller
{
    public function initiate(Request $request, FlutterwaveService $flutterwave): JsonResponse
    {
        $data = $request->validate([
            'type' => ['required', 'in:donation,ticket'],
            'amount' => ['required_if:type,donation', 'nullable', 'integer', 'min:100'],
            'currency' => ['nullable', 'string', 'max:10'],
            'ticket_tier_id' => ['required_if:type,ticket', 'nullable', 'integer', 'exists:ticket_tiers,id'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:40'],
            'method' => ['nullable', 'string', 'max:80'],
        ]);

        $tier = null;
        $amount = (int) ($data['amount'] ?? 0);
        $currency = $data['currency'] ?? 'TZS';

        if ($data['type'] === 'ticket') {
            $tier = TicketTier::published()->findOrFail($data['ticket_tier_id']);
            $amount = (int) $tier->price;
            $currency = $tier->currency ?: $currency;
        }

        $user = auth('sanctum')->user();

        $order = Order::create([
            'user_id' => $user?->id,
            'type' => $data['type'],
            'ticket_tier_id' => $tier?->id,
            'amount' => $amount,
            'currency' => $currency,
            'status' => 'pending',
            'customer_name' => $data['customer_name'] ?? $user?->name,
            'customer_email' => $data['customer_email'] ?? $user?->email,
            'customer_phone' => $data['customer_phone'] ?? null,
            'reference' => 'JKY-'.strtoupper(Str::random(10)).'-'.time(),
            'provider' => 'flutterwave',
            'meta' => [
                'method' => $data['method'] ?? null,
            ],
        ]);

        try {
            $initiated = $flutterwave->initiate($order);
            $order->payment_link = $initiated['link'];
            $order->meta = array_merge($order->meta ?? [], ['flutterwave' => $initiated['raw']]);
            $order->save();
        } catch (RuntimeException $e) {
            // Missing keys: in local/testing, mark paid so checkout is testable without credentials.
            if (! $flutterwave->configured() && app()->environment(['local', 'testing'])) {
                $order->markPaid('LOCAL-DEMO');
                $order->refresh();

                return response()->json([
                    'data' => [
                        'order_id' => $order->id,
                        'reference' => $order->reference,
                        'status' => $order->status,
                        'amount' => $order->amount,
                        'currency' => $order->currency,
                        'payment_link' => null,
                        'requires_configuration' => false,
                        'demo' => true,
                        'message' => 'Local demo payment recorded. Set FLUTTERWAVE_SECRET_KEY for live charges.',
                    ],
                    'meta' => (object) [],
                ], 201);
            }

            if (! $flutterwave->configured()) {
                return response()->json([
                    'data' => [
                        'order_id' => $order->id,
                        'reference' => $order->reference,
                        'status' => $order->status,
                        'amount' => $order->amount,
                        'currency' => $order->currency,
                        'payment_link' => null,
                        'requires_configuration' => true,
                        'message' => $e->getMessage(),
                    ],
                    'meta' => (object) [],
                ], 422);
            }

            $order->status = 'failed';
            $order->save();

            return response()->json([
                'message' => $e->getMessage(),
                'errors' => (object) [],
            ], 502);
        }

        return response()->json([
            'data' => [
                'order_id' => $order->id,
                'reference' => $order->reference,
                'status' => $order->status,
                'amount' => $order->amount,
                'currency' => $order->currency,
                'payment_link' => $order->payment_link,
                'requires_configuration' => false,
            ],
            'meta' => (object) [],
        ], 201);
    }

    public function show(string $reference): JsonResponse
    {
        $order = Order::where('reference', $reference)->firstOrFail();

        return response()->json([
            'data' => $this->orderPayload($order),
            'meta' => (object) [],
        ]);
    }

    public function verify(Request $request, FlutterwaveService $flutterwave): JsonResponse
    {
        $data = $request->validate([
            'reference' => ['required', 'string'],
            'transaction_id' => ['nullable', 'string'],
        ]);

        $order = Order::where('reference', $data['reference'])->firstOrFail();

        if ($order->isPaid()) {
            return response()->json(['data' => $this->orderPayload($order), 'meta' => (object) []]);
        }

        $txn = $data['transaction_id'] ?? null;
        if (! $txn) {
            return response()->json(['data' => $this->orderPayload($order), 'meta' => (object) []]);
        }

        try {
            $result = $flutterwave->verify($txn);
            $flutterwave->applySuccessfulVerification($order, $result);
            $order->refresh();
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage(), 'errors' => (object) []], 502);
        }

        return response()->json(['data' => $this->orderPayload($order), 'meta' => (object) []]);
    }

    public function callback(Request $request, FlutterwaveService $flutterwave): RedirectResponse
    {
        $reference = (string) $request->query('tx_ref', '');
        $txnId = (string) $request->query('transaction_id', $request->query('id', ''));
        $status = (string) $request->query('status', '');

        $order = $reference !== '' ? Order::where('reference', $reference)->first() : null;

        if ($order && ! $order->isPaid() && $txnId !== '' && $flutterwave->configured()) {
            try {
                $result = $flutterwave->verify($txnId);
                $flutterwave->applySuccessfulVerification($order, $result);
            } catch (RuntimeException) {
                // fall through
            }
        }

        if ($order && ! $order->isPaid() && strtolower($status) === 'successful' && ! $flutterwave->configured()) {
            $order->markPaid($txnId ?: null);
        }

        $target = url('/site/Donate');
        if ($order?->type === 'ticket') {
            $target = url('/site/Tickets');
        }

        return redirect()->away($target.'?payment='.($order?->status ?? 'unknown').'&ref='.urlencode($reference));
    }

    public function webhook(Request $request, FlutterwaveService $flutterwave): JsonResponse
    {
        $secretHash = (string) config('services.flutterwave.secret_hash');
        if ($secretHash !== '') {
            $signature = (string) $request->header('verif-hash', '');
            if (! hash_equals($secretHash, $signature)) {
                return response()->json(['message' => 'Invalid signature'], 401);
            }
        }

        $payload = $request->all();
        $event = (string) data_get($payload, 'event', '');
        $data = data_get($payload, 'data', []);
        $reference = (string) ($data['tx_ref'] ?? '');

        if ($reference === '') {
            return response()->json(['message' => 'ignored'], 200);
        }

        $order = Order::where('reference', $reference)->first();
        if (! $order) {
            return response()->json(['message' => 'order not found'], 200);
        }

        if (str_contains(strtolower($event), 'success') || strtolower((string) ($data['status'] ?? '')) === 'successful') {
            $txnId = (string) ($data['id'] ?? '');
            if ($txnId !== '' && $flutterwave->configured()) {
                try {
                    $result = $flutterwave->verify($txnId);
                    $flutterwave->applySuccessfulVerification($order, $result);
                } catch (RuntimeException) {
                    if (strtolower((string) ($data['status'] ?? '')) === 'successful') {
                        $order->markPaid($txnId);
                    }
                }
            } else {
                $order->markPaid($txnId ?: null);
            }
        }

        return response()->json(['message' => 'ok']);
    }

    /**
     * @return array<string, mixed>
     */
    private function orderPayload(Order $order): array
    {
        return [
            'id' => $order->id,
            'type' => $order->type,
            'status' => $order->status,
            'amount' => $order->amount,
            'currency' => $order->currency,
            'reference' => $order->reference,
            'payment_link' => $order->payment_link,
            'qr_payload' => $order->qr_payload,
            'ticket_tier_id' => $order->ticket_tier_id,
            'paid_at' => optional($order->paid_at)?->toIso8601String(),
        ];
    }
}

<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class FlutterwaveService
{
    public function configured(): bool
    {
        return filled(config('services.flutterwave.secret_key'));
    }

    /**
     * @return array{link: ?string, raw: array}
     */
    public function initiate(Order $order): array
    {
        if (! $this->configured()) {
            throw new RuntimeException('Flutterwave is not configured. Set FLUTTERWAVE_SECRET_KEY in .env');
        }

        $payload = [
            'tx_ref' => $order->reference,
            'amount' => $order->amount,
            'currency' => $order->currency ?: 'TZS',
            'redirect_url' => url('/api/v1/payments/callback'),
            'customer' => [
                'email' => $order->customer_email ?: 'guest@jukanye.com',
                'phonenumber' => $order->customer_phone ?: '',
                'name' => $order->customer_name ?: 'Jukanye Guest',
            ],
            'customizations' => [
                'title' => 'Jukanye Festival',
                'description' => $order->type === 'donation' ? 'Donation' : 'Ticket payment',
            ],
            'meta' => [
                'order_id' => $order->id,
                'type' => $order->type,
            ],
        ];

        $response = Http::withToken((string) config('services.flutterwave.secret_key'))
            ->acceptJson()
            ->timeout(30)
            ->post('https://api.flutterwave.com/v3/payments', $payload);

        $json = $response->json() ?? [];

        if (! $response->successful() || ($json['status'] ?? '') !== 'success') {
            $message = $json['message'] ?? 'Flutterwave initiation failed';
            throw new RuntimeException($message);
        }

        $link = data_get($json, 'data.link');

        return ['link' => is_string($link) ? $link : null, 'raw' => $json];
    }

    /**
     * @return array{status: string, raw: array}
     */
    public function verify(string $transactionId): array
    {
        if (! $this->configured()) {
            throw new RuntimeException('Flutterwave is not configured');
        }

        $response = Http::withToken((string) config('services.flutterwave.secret_key'))
            ->acceptJson()
            ->timeout(30)
            ->get('https://api.flutterwave.com/v3/transactions/'.$transactionId.'/verify');

        $json = $response->json() ?? [];
        $status = (string) data_get($json, 'data.status', '');

        return ['status' => $status, 'raw' => $json];
    }

    public function applySuccessfulVerification(Order $order, array $verifyPayload): void
    {
        $data = $verifyPayload['raw']['data'] ?? [];
        $amountOk = (float) ($data['amount'] ?? 0) >= (float) $order->amount;
        $currencyOk = strtoupper((string) ($data['currency'] ?? '')) === strtoupper($order->currency);
        $refOk = (string) ($data['tx_ref'] ?? '') === $order->reference;
        $paid = strtolower((string) ($data['status'] ?? '')) === 'successful';

        if ($paid && $amountOk && $currencyOk && $refOk) {
            $order->markPaid((string) ($data['id'] ?? $data['flw_ref'] ?? ''));
        }
    }
}

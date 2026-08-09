<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MyOrderController extends Controller
{
    public function tickets(Request $request): JsonResponse
    {
        $user = $request->user();
        $orders = Order::query()
            ->with('ticketTier')
            ->where('type', 'ticket')
            ->where('status', 'paid')
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhere('customer_email', $user->email);
            })
            ->orderByDesc('paid_at')
            ->get()
            ->map(fn (Order $o) => [
                'id' => $o->id,
                'reference' => $o->reference,
                'amount' => $o->amount,
                'currency' => $o->currency,
                'qr_payload' => $o->qr_payload,
                'paid_at' => optional($o->paid_at)?->toIso8601String(),
                'customer_name' => $o->customer_name ?: $user->name,
                'tier_name_en' => $o->ticketTier?->name_en,
                'tier_name_sw' => $o->ticketTier?->name_sw,
            ]);

        return response()->json(['data' => $orders, 'meta' => ['total' => $orders->count()]]);
    }

    public function donations(Request $request): JsonResponse
    {
        $user = $request->user();
        $orders = Order::query()
            ->where('type', 'donation')
            ->where('status', 'paid')
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhere('customer_email', $user->email);
            })
            ->orderByDesc('paid_at')
            ->get()
            ->map(fn (Order $o) => [
                'id' => $o->id,
                'reference' => $o->reference,
                'amount' => $o->amount,
                'currency' => $o->currency,
                'paid_at' => optional($o->paid_at)?->toIso8601String(),
            ]);

        return response()->json(['data' => $orders, 'meta' => ['total' => $orders->count()]]);
    }
}

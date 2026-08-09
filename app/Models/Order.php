<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'ticket_tier_id',
        'amount',
        'currency',
        'status',
        'customer_name',
        'customer_email',
        'customer_phone',
        'reference',
        'provider',
        'provider_txn_id',
        'payment_link',
        'qr_payload',
        'meta',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'paid_at' => 'datetime',
            'amount' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ticketTier(): BelongsTo
    {
        return $this->belongsTo(TicketTier::class);
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function markPaid(?string $providerTxnId = null): void
    {
        if ($this->isPaid()) {
            return;
        }

        $this->forceFill([
            'status' => 'paid',
            'provider_txn_id' => $providerTxnId ?: $this->provider_txn_id,
            'paid_at' => now(),
            'qr_payload' => $this->qr_payload ?: $this->reference.'|'.($this->customer_email ?: 'guest'),
        ])->save();

        if ($this->type === 'donation' && Schema::hasColumn('site_settings', 'total_raised')) {
            $settings = SiteSetting::current();
            $settings->total_raised = (int) $settings->total_raised + (int) $this->amount;
            $settings->save();
        }
    }
}

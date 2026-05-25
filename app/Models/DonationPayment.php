<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DonationPayment extends Model
{
    protected $fillable = [
        'donation_id',
        'payment_method',
        'payment_channel',
        'external_ref',
        'gross_amount',
        'fee_amount',
        'net_amount',
        'status',
        'payment_url',
        'snap_token',
        'raw_response',
        'paid_at',
        'expired_at',
    ];

    protected $casts = [
        'gross_amount' => 'integer',
        'fee_amount' => 'integer',
        'net_amount' => 'integer',
        'paid_at' => 'datetime',
        'expired_at' => 'datetime',
        'raw_response' => 'array',
    ];

    public function donation(): BelongsTo
    {
        return $this->belongsTo(Donation::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Withdrawal extends Model
{
    use LogsActivity;

    protected $fillable = [
        'campaign_id',
        'user_id',
        'amount',
        'bank_name',
        'account_number',
        'account_name',
        'status',
        'rejection_reason',
        'transfer_proof_url',
        'processed_by',
        'processed_at',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['amount', 'status', 'processed_by'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    protected $casts = [
        'amount' => 'integer',
        'processed_at' => 'datetime',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'processed_by');
    }
}

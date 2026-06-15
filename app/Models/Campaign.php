<?php

namespace App\Models;

use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Campaign extends Model
{
    use HasFactory, LogsActivity, HasSlug;

    protected $slugSourceField = 'title';

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'slug',
        'description',
        'story',
        'cover_image_url',
        'goal_amount',
        'collected_amount',
        'withdrawn_amount',
        'donor_count',
        'deadline',
        'status',
        'verified_status',
        'verified_by',
        'verified_at',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'goal_amount', 'status', 'verified_status', 'verified_by'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    protected $casts = [
        'goal_amount' => 'integer',
        'collected_amount' => 'integer',
        'donor_count' => 'integer',
        'deadline' => 'date',
        'verified_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(CampaignCategory::class, 'category_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'campaign_tags');
    }

    public function images(): HasMany
    {
        return $this->hasMany(CampaignImage::class);
    }

    public function updates(): HasMany
    {
        return $this->hasMany(CampaignUpdate::class);
    }

    public function donations(): HasMany
    {
        return $this->hasMany(Donation::class);
    }

    public function withdrawals(): HasMany
    {
        return $this->hasMany(Withdrawal::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'verified_by');
    }
}

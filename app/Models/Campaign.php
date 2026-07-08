<?php

namespace App\Models;

use App\Enums\CampaignStatus;
use Database\Factories\CampaignFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Campaign extends Model implements HasMedia
{
    /** @use HasFactory<CampaignFactory> */
    use HasFactory, InteractsWithMedia, SoftDeletes;

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumbnail')
            ->width(400)
            ->height(225)
            ->nonQueued();

        $this->addMediaConversion('medium')
            ->width(800)
            ->height(450)
            ->nonQueued();

        $this->addMediaConversion('webp')
            ->format('webp')
            ->quality(80)
            ->nonQueued();

        $this->addMediaConversion('blur')
            ->width(20)
            ->blur(50)
            ->nonQueued();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')
            ->singleFile()
            ->useDisk('public');
    }

    protected $fillable = [
        'owner_id',
        'beneficiary_id',
        'category_id',
        'title',
        'slug',
        'story',
        'goal_amount',
        'raised_amount',
        'currency',
        'cover_media',
        'status',
        'starts_at',
        'ends_at',
        'rejection_reason',
        'rejected_at',
    ];

    protected $appends = ['cover_url', 'days_remaining'];

    protected function casts(): array
    {
        return [
            'goal_amount' => 'decimal:2',
            'raised_amount' => 'decimal:2',
            'status' => CampaignStatus::class,
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }

    public function getCoverUrlAttribute(): ?string
    {
        if ($this->cover_media) {
            return $this->cover_media;
        }

        $url = $this->getFirstMediaUrl('cover', 'webp');

        return $url ?: ($this->getFirstMediaUrl('cover') ?: null);
    }

    public function getCoverThumbnailAttribute(): ?string
    {
        return $this->getFirstMediaUrl('cover', 'thumbnail') ?: $this->cover_url;
    }

    public function getCoverBlurAttribute(): ?string
    {
        return $this->getFirstMediaUrl('cover', 'blur') ?: null;
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function beneficiary(): BelongsTo
    {
        return $this->belongsTo(User::class, 'beneficiary_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
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

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function isActive(): bool
    {
        return $this->status === CampaignStatus::Active;
    }

    public function canAcceptDonations(): bool
    {
        return $this->status === CampaignStatus::Active;
    }

    public function percentageRaised(): float
    {
        if ((float) $this->goal_amount === 0.0) {
            return 0;
        }

        return round(((float) $this->raised_amount / (float) $this->goal_amount) * 100, 1);
    }

    public function getDaysRemainingAttribute(): ?int
    {
        if ($this->ends_at === null) {
            return null;
        }

        return max(0, (int) now()->diffInDays($this->ends_at, false));
    }
}

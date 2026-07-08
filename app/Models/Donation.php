<?php

namespace App\Models;

use App\Enums\DonationPaymentMethod;
use App\Enums\DonationStatus;
use Database\Factories\DonationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Donation extends Model
{
    /** @use HasFactory<DonationFactory> */
    use HasFactory;

    protected $fillable = [
        'campaign_id',
        'donor_id',
        'donor_name',
        'donor_email',
        'donor_phone',
        'is_anonymous',
        'amount',
        'tip_amount',
        'currency',
        'payment_method',
        'payment_provider',
        'payment_provider_ref',
        'status',
        'message',
        'claimed_by_id',
        'claimed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'tip_amount' => 'decimal:2',
            'is_anonymous' => 'boolean',
            'status' => DonationStatus::class,
            'payment_method' => DonationPaymentMethod::class,
            'claimed_at' => 'datetime',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function donor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'donor_id');
    }

    public function claimedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'claimed_by_id');
    }

    public function isCompleted(): bool
    {
        return $this->status === DonationStatus::Completed;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wallet extends Model
{
    protected $fillable = ['campaign_id', 'total_raised', 'available_balance', 'pending_balance', 'processing_withdrawals', 'completed_withdrawals'];

    protected function casts(): array
    {
        return [
            'total_raised' => 'decimal:2',
            'available_balance' => 'decimal:2',
            'pending_balance' => 'decimal:2',
            'processing_withdrawals' => 'decimal:2',
            'completed_withdrawals' => 'decimal:2',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }
}

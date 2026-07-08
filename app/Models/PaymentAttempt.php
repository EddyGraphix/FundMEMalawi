<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentAttempt extends Model
{
    protected $fillable = ['donation_id', 'provider', 'provider_ref', 'status', 'request_data', 'response_data', 'error_message'];

    public function donation(): BelongsTo
    {
        return $this->belongsTo(Donation::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignShare extends Model
{
    protected $fillable = ['campaign_id', 'platform', 'url', 'clicks', 'donations_from_shares'];

    protected function casts(): array
    {
        return [
            'clicks' => 'integer',
            'donations_from_shares' => 'integer',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }
}

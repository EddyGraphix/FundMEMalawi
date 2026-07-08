<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignHealthScore extends Model
{
    protected $fillable = ['campaign_id', 'score', 'suggestions', 'checks'];

    protected function casts(): array
    {
        return [
            'score' => 'integer',
            'suggestions' => 'array',
            'checks' => 'array',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }
}

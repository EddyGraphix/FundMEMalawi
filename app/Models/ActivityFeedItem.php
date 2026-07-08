<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityFeedItem extends Model
{
    protected $fillable = ['campaign_id', 'actor_type', 'actor_id', 'type', 'data'];

    protected function casts(): array
    {
        return [
            'data' => 'array',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function actor(): MorphTo
    {
        return $this->morphTo();
    }
}

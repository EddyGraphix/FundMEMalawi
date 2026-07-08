<?php

namespace App\Models;

use Database\Factories\CampaignUpdateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignUpdate extends Model
{
    /** @use HasFactory<CampaignUpdateFactory> */
    use HasFactory;

    protected $fillable = ['campaign_id', 'title', 'body', 'media'];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }
}

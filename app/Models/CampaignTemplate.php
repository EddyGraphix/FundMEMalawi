<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignTemplate extends Model
{
    protected $fillable = ['key', 'name', 'category', 'suggested_title', 'story_outline', 'suggested_image', 'suggested_goal', 'category_id', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return [
            'suggested_goal' => 'decimal:2',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}

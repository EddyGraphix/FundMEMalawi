<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Church extends Model
{
    protected $fillable = ['organization_id', 'pastor_id', 'name', 'slug', 'denomination', 'city', 'district', 'description'];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function pastor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pastor_id');
    }
}

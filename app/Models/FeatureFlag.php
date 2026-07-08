<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeatureFlag extends Model
{
    protected $fillable = ['key', 'name', 'description', 'enabled'];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
        ];
    }

    public static function isEnabled(string $key): bool
    {
        return static::where('key', $key)->value('enabled') ?? false;
    }
}

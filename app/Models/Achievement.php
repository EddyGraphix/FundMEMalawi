<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Achievement extends Model
{
    protected $fillable = ['key', 'name', 'description', 'icon', 'group', 'criteria'];

    protected function casts(): array
    {
        return [
            'criteria' => 'array',
        ];
    }
}

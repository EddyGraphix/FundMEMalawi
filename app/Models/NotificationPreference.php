<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model
{
    protected $fillable = ['user_id', 'email_updates', 'email_milestones', 'email_comments', 'email_announcements', 'sms_updates', 'sms_donations', 'push_enabled'];

    protected function casts(): array
    {
        return [
            'email_updates' => 'boolean',
            'email_milestones' => 'boolean',
            'email_comments' => 'boolean',
            'email_announcements' => 'boolean',
            'sms_updates' => 'boolean',
            'sms_donations' => 'boolean',
            'push_enabled' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

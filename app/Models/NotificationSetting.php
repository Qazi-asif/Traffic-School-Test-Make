<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationSetting extends Model
{
    protected $fillable = [
        'user_id',
        'email_course_updates',
        'email_promotional',
        'email_system',
        'sms_reminders',
        'in_app_notifications',
        'push_notifications',
    ];

    protected $casts = [
        'email_course_updates' => 'boolean',
        'email_promotional' => 'boolean',
        'email_system' => 'boolean',
        'sms_reminders' => 'boolean',
        'in_app_notifications' => 'boolean',
        'push_notifications' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

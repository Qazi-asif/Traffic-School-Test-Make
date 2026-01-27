<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FloridaNotificationSetting extends Model
{
    protected $fillable = [
        'user_id', 'email_course_updates', 'email_payment_receipts', 'email_certificate_alerts',
        'email_dicds_status', 'email_compliance_alerts', 'sms_reminders', 'in_app_notifications',
    ];

    protected $casts = [
        'email_course_updates' => 'boolean',
        'email_payment_receipts' => 'boolean',
        'email_certificate_alerts' => 'boolean',
        'email_dicds_status' => 'boolean',
        'email_compliance_alerts' => 'boolean',
        'sms_reminders' => 'boolean',
        'in_app_notifications' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

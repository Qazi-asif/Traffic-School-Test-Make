<?php

namespace App\Http\Controllers;

use App\Models\NotificationSetting;
use Illuminate\Http\Request;

class NotificationSettingController extends Controller
{
    public function show()
    {
        $settings = NotificationSetting::firstOrCreate(
            ['user_id' => auth()->id()],
            [
                'email_course_updates' => true,
                'email_promotional' => false,
                'email_system' => true,
                'sms_reminders' => false,
                'in_app_notifications' => true,
                'push_notifications' => true,
            ]
        );

        return response()->json($settings);
    }

    public function update(Request $request)
    {
        $settings = NotificationSetting::firstOrCreate(['user_id' => auth()->id()]);

        $settings->update([
            'email_course_updates' => $request->boolean('email_course_updates'),
            'email_promotional' => $request->boolean('email_promotional'),
            'email_system' => $request->boolean('email_system'),
            'sms_reminders' => $request->boolean('sms_reminders'),
            'in_app_notifications' => $request->boolean('in_app_notifications'),
            'push_notifications' => $request->boolean('push_notifications'),
        ]);

        return response()->json($settings);
    }
}

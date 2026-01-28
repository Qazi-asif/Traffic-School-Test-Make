<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class SystemSettingSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $settings = [
            // General Settings
            ['key' => 'site_name', 'value' => 'Dummies Traffic School', 'type' => 'string', 'group' => 'general', 'description' => 'Site name displayed throughout the application'],
            ['key' => 'site_url', 'value' => 'https://dummiestrafficschool.com', 'type' => 'string', 'group' => 'general', 'description' => 'Main site URL'],
            ['key' => 'admin_email', 'value' => 'admin@dummiestrafficschool.com', 'type' => 'string', 'group' => 'general', 'description' => 'Primary admin email address'],
            ['key' => 'support_email', 'value' => 'support@dummiestrafficschool.com', 'type' => 'string', 'group' => 'general', 'description' => 'Support email address'],
            ['key' => 'maintenance_mode', 'value' => false, 'type' => 'boolean', 'group' => 'general', 'description' => 'Enable/disable maintenance mode'],
            
            // File Upload Settings
            ['key' => 'max_file_size', 'value' => 10240, 'type' => 'integer', 'group' => 'files', 'description' => 'Maximum file upload size in KB'],
            ['key' => 'allowed_file_types', 'value' => 'jpg,jpeg,png,gif,pdf,doc,docx,mp4,avi,mov', 'type' => 'string', 'group' => 'files', 'description' => 'Allowed file extensions (comma separated)'],
            ['key' => 'file_storage_disk', 'value' => 'local', 'type' => 'string', 'group' => 'files', 'description' => 'Default storage disk for file uploads'],
            
            // Email Settings
            ['key' => 'mail_from_name', 'value' => 'Dummies Traffic School', 'type' => 'string', 'group' => 'email', 'description' => 'Default sender name for emails'],
            ['key' => 'mail_from_address', 'value' => 'noreply@dummiestrafficschool.com', 'type' => 'string', 'group' => 'email', 'description' => 'Default sender email address'],
            
            // Course Settings
            ['key' => 'default_passing_score', 'value' => 80, 'type' => 'integer', 'group' => 'courses', 'description' => 'Default passing score percentage for courses'],
            ['key' => 'max_quiz_attempts', 'value' => 3, 'type' => 'integer', 'group' => 'courses', 'description' => 'Maximum number of quiz attempts allowed'],
            ['key' => 'certificate_auto_generate', 'value' => true, 'type' => 'boolean', 'group' => 'courses', 'description' => 'Automatically generate certificates upon course completion'],
            
            // Payment Settings
            ['key' => 'default_currency', 'value' => 'USD', 'type' => 'string', 'group' => 'payments', 'description' => 'Default currency for payments'],
            ['key' => 'tax_rate', 'value' => 0.00, 'type' => 'decimal', 'group' => 'payments', 'description' => 'Default tax rate (as decimal, e.g., 0.08 for 8%)'],
            
            // State-Specific Settings
            ['key' => 'florida_dicds_enabled', 'value' => true, 'type' => 'boolean', 'group' => 'states', 'description' => 'Enable Florida DICDS integration'],
            ['key' => 'missouri_form4444_enabled', 'value' => true, 'type' => 'boolean', 'group' => 'states', 'description' => 'Enable Missouri Form 4444 generation'],
            
            // Security Settings
            ['key' => 'session_timeout', 'value' => 120, 'type' => 'integer', 'group' => 'security', 'description' => 'Session timeout in minutes'],
            ['key' => 'password_min_length', 'value' => 8, 'type' => 'integer', 'group' => 'security', 'description' => 'Minimum password length'],
            ['key' => 'login_max_attempts', 'value' => 5, 'type' => 'integer', 'group' => 'security', 'description' => 'Maximum login attempts before lockout'],
            
            // Notification Settings
            ['key' => 'email_notifications_enabled', 'value' => true, 'type' => 'boolean', 'group' => 'notifications', 'description' => 'Enable email notifications'],
            ['key' => 'sms_notifications_enabled', 'value' => false, 'type' => 'boolean', 'group' => 'notifications', 'description' => 'Enable SMS notifications'],
            
            // Analytics Settings
            ['key' => 'google_analytics_id', 'value' => '', 'type' => 'string', 'group' => 'analytics', 'description' => 'Google Analytics tracking ID'],
            ['key' => 'facebook_pixel_id', 'value' => '', 'type' => 'string', 'group' => 'analytics', 'description' => 'Facebook Pixel ID'],
        ];

        foreach ($settings as $setting) {
            SystemSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
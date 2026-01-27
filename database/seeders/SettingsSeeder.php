<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // General Settings
            ['key' => 'site_name', 'value' => config('app.name', 'Traffic School'), 'type' => 'string', 'group' => 'general', 'description' => 'Site name displayed in header and emails'],
            ['key' => 'site_url', 'value' => config('app.url'), 'type' => 'string', 'group' => 'general', 'description' => 'Main site URL'],
            ['key' => 'admin_email', 'value' => config('mail.from.address'), 'type' => 'string', 'group' => 'general', 'description' => 'Administrator email address'],
            ['key' => 'timezone', 'value' => config('app.timezone'), 'type' => 'string', 'group' => 'general', 'description' => 'Default timezone'],
            ['key' => 'maintenance_mode', 'value' => false, 'type' => 'boolean', 'group' => 'general', 'description' => 'Enable/disable maintenance mode'],
            ['key' => 'maintenance_message', 'value' => 'Site is under maintenance. Please check back later.', 'type' => 'string', 'group' => 'general', 'description' => 'Message shown during maintenance'],

            // Email Settings
            ['key' => 'mail_driver', 'value' => config('mail.default'), 'type' => 'string', 'group' => 'email', 'description' => 'Email driver (smtp, sendmail, etc.)'],
            ['key' => 'mail_host', 'value' => config('mail.mailers.smtp.host'), 'type' => 'string', 'group' => 'email', 'description' => 'SMTP host'],
            ['key' => 'mail_port', 'value' => config('mail.mailers.smtp.port'), 'type' => 'integer', 'group' => 'email', 'description' => 'SMTP port'],
            ['key' => 'mail_username', 'value' => config('mail.mailers.smtp.username'), 'type' => 'string', 'group' => 'email', 'description' => 'SMTP username'],
            ['key' => 'mail_encryption', 'value' => config('mail.mailers.smtp.encryption'), 'type' => 'string', 'group' => 'email', 'description' => 'SMTP encryption (tls, ssl)'],
            ['key' => 'mail_from_name', 'value' => config('mail.from.name'), 'type' => 'string', 'group' => 'email', 'description' => 'From name for emails'],
            ['key' => 'mail_from_address', 'value' => config('mail.from.address'), 'type' => 'string', 'group' => 'email', 'description' => 'From address for emails'],

            // Security Settings
            ['key' => 'session_lifetime', 'value' => config('session.lifetime'), 'type' => 'integer', 'group' => 'security', 'description' => 'Session lifetime in minutes'],
            ['key' => 'password_timeout', 'value' => config('auth.password_timeout'), 'type' => 'integer', 'group' => 'security', 'description' => 'Password confirmation timeout'],
            ['key' => 'login_throttle', 'value' => 5, 'type' => 'integer', 'group' => 'security', 'description' => 'Maximum login attempts'],
            ['key' => 'two_factor_enabled', 'value' => false, 'type' => 'boolean', 'group' => 'security', 'description' => 'Enable two-factor authentication'],

            // Payment Settings
            ['key' => 'stripe_enabled', 'value' => !empty(config('services.stripe.key')), 'type' => 'boolean', 'group' => 'payment', 'description' => 'Enable Stripe payments'],
            ['key' => 'paypal_enabled', 'value' => !empty(config('services.paypal.client_id')), 'type' => 'boolean', 'group' => 'payment', 'description' => 'Enable PayPal payments'],
            ['key' => 'authorizenet_enabled', 'value' => !empty(config('services.authorizenet.login_id')), 'type' => 'boolean', 'group' => 'payment', 'description' => 'Enable Authorize.Net payments'],
            ['key' => 'currency', 'value' => 'USD', 'type' => 'string', 'group' => 'payment', 'description' => 'Default currency'],
            ['key' => 'tax_rate', 'value' => 0, 'type' => 'float', 'group' => 'payment', 'description' => 'Default tax rate (decimal)'],

            // Notification Settings
            ['key' => 'email_notifications', 'value' => true, 'type' => 'boolean', 'group' => 'notifications', 'description' => 'Enable email notifications'],
            ['key' => 'sms_notifications', 'value' => false, 'type' => 'boolean', 'group' => 'notifications', 'description' => 'Enable SMS notifications'],
            ['key' => 'push_notifications', 'value' => false, 'type' => 'boolean', 'group' => 'notifications', 'description' => 'Enable push notifications'],
            ['key' => 'admin_alerts', 'value' => true, 'type' => 'boolean', 'group' => 'notifications', 'description' => 'Enable admin alerts'],

            // Integration Settings
            ['key' => 'florida_dicds_enabled', 'value' => !empty(config('flhsmv.username')), 'type' => 'boolean', 'group' => 'integrations', 'description' => 'Enable Florida DICDS integration'],
            ['key' => 'california_tvcc_enabled', 'value' => false, 'type' => 'boolean', 'group' => 'integrations', 'description' => 'Enable California TVCC integration'],
            ['key' => 'nevada_ntsa_enabled', 'value' => false, 'type' => 'boolean', 'group' => 'integrations', 'description' => 'Enable Nevada NTSA integration'],
            ['key' => 'analytics_enabled', 'value' => !empty(config('services.google.analytics_id')), 'type' => 'boolean', 'group' => 'integrations', 'description' => 'Enable Google Analytics'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
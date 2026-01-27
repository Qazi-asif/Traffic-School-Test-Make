<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class FloridaMailService
{
    public static function send($to, $subject, $content, $data = [])
    {
        try {
            Mail::send([], [], function ($message) use ($to, $subject, $content) {
                $message->to($to)
                    ->subject($subject)
                    ->html($content);
            });

            return true;
        } catch (\Exception $e) {
            Log::error('Florida Mail Error: '.$e->getMessage());

            return false;
        }
    }

    public static function sendTemplate($to, $templateSlug, $variables = [])
    {
        $template = \App\Models\FloridaEmailTemplate::where('slug', $templateSlug)->where('is_active', true)->first();

        if (! $template) {
            Log::error("Florida Email Template not found: {$templateSlug}");

            return false;
        }

        $content = self::replaceVariables($template->content, $variables);
        $subject = self::replaceVariables($template->subject, $variables);

        $sent = self::send($to, $subject, $content);

        // Log email
        \App\Models\FloridaEmailLog::create([
            'template_id' => $template->id,
            'recipient_email' => $to,
            'subject' => $subject,
            'content' => $content,
            'florida_variables_used' => $variables,
            'status' => $sent ? 'sent' : 'failed',
            'sent_at' => now(),
        ]);

        return $sent;
    }

    private static function replaceVariables($content, $variables)
    {
        foreach ($variables as $key => $value) {
            $content = str_replace("{{{$key}}}", $value, $content);
        }

        return $content;
    }
}

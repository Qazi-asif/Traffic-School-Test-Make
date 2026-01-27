<?php

namespace App\Http\Controllers;

use App\Models\EmailTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EmailTemplateController extends Controller
{
    public function index()
    {
        $templates = EmailTemplate::with('creator')->orderBy('name')->get();

        return response()->json($templates);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'category' => 'required|in:user,admin,system,marketing',
        ]);

        $template = EmailTemplate::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'subject' => $request->subject,
            'content' => $request->content,
            'variables' => $request->variables ?? [],
            'category' => $request->category,
            'is_active' => $request->is_active ?? true,
            'created_by' => auth()->id(),
        ]);

        return response()->json($template->load('creator'));
    }

    public function show(EmailTemplate $emailTemplate)
    {
        return response()->json($emailTemplate->load('creator'));
    }

    public function update(Request $request, EmailTemplate $emailTemplate)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'category' => 'required|in:user,admin,system,marketing',
        ]);

        $emailTemplate->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'subject' => $request->subject,
            'content' => $request->content,
            'variables' => $request->variables ?? [],
            'category' => $request->category,
            'is_active' => $request->is_active ?? true,
        ]);

        return response()->json($emailTemplate->load('creator'));
    }

    public function destroy(EmailTemplate $emailTemplate)
    {
        $emailTemplate->delete();

        return response()->json(['message' => 'Template deleted successfully']);
    }

    public function test(Request $request, EmailTemplate $emailTemplate)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        // Send test email
        $variables = [
            'user_name' => 'Test User',
            'course_title' => 'Sample Course',
            'completion_date' => now()->format('M d, Y'),
        ];

        $content = $this->replaceVariables($emailTemplate->content, $variables);

        \Mail::raw($content, function ($message) use ($request, $emailTemplate) {
            $message->to($request->email)
                ->subject('[TEST] '.$emailTemplate->subject);
        });

        return response()->json(['message' => 'Test email sent successfully']);
    }

    public function variables()
    {
        $variables = [
            'user' => ['user_name', 'user_email', 'user_phone'],
            'course' => ['course_title', 'course_description', 'completion_date'],
            'system' => ['site_name', 'site_url', 'current_date'],
        ];

        return response()->json($variables);
    }

    private function replaceVariables($content, $variables)
    {
        foreach ($variables as $key => $value) {
            $content = str_replace('{{'.$key.'}}', $value, $content);
        }

        return $content;
    }
}

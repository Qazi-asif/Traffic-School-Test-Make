<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SecurityQuestion;
use Illuminate\Http\Request;

class SecurityQuestionController extends Controller
{
    public function index()
    {
        $questions = SecurityQuestion::ordered()->get();
        return view('admin.security-questions.index', compact('questions'));
    }

    public function create()
    {
        return view('admin.security-questions.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'question_key' => 'required|string|unique:security_questions,question_key',
            'question_text' => 'required|string|max:500',
            'answer_type' => 'required|in:text,number,date',
            'help_text' => 'nullable|string|max:200',
            'is_active' => 'boolean',
            'order_index' => 'required|integer|min:0',
        ]);

        SecurityQuestion::create($request->all());

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Security question created successfully.']);
        }

        return redirect()->route('admin.security-questions.index')
            ->with('success', 'Security question created successfully.');
    }

    public function show(SecurityQuestion $securityQuestion)
    {
        return view('admin.security-questions.show', compact('securityQuestion'));
    }

    public function edit(SecurityQuestion $securityQuestion)
    {
        return view('admin.security-questions.edit', compact('securityQuestion'));
    }

    public function update(Request $request, SecurityQuestion $securityQuestion)
    {
        $request->validate([
            'question_key' => 'required|string|unique:security_questions,question_key,' . $securityQuestion->id,
            'question_text' => 'required|string|max:500',
            'answer_type' => 'required|in:text,number,date',
            'help_text' => 'nullable|string|max:200',
            'is_active' => 'boolean',
            'order_index' => 'required|integer|min:0',
        ]);

        $securityQuestion->update($request->all());

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Security question updated successfully.']);
        }

        return redirect()->route('admin.security-questions.index')
            ->with('success', 'Security question updated successfully.');
    }

    public function destroy(SecurityQuestion $securityQuestion)
    {
        $securityQuestion->delete();

        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Security question deleted successfully.']);
        }

        return redirect()->route('admin.security-questions.index')
            ->with('success', 'Security question deleted successfully.');
    }

    public function toggleActive(SecurityQuestion $securityQuestion)
    {
        $securityQuestion->update(['is_active' => !$securityQuestion->is_active]);

        $status = $securityQuestion->is_active ? 'activated' : 'deactivated';
        
        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'message' => "Security question {$status} successfully."]);
        }
        
        return back()->with('success', "Security question {$status} successfully.");
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'questions' => 'required|array',
            'questions.*.id' => 'required|exists:security_questions,id',
            'questions.*.order_index' => 'required|integer|min:0',
        ]);

        foreach ($request->questions as $questionData) {
            SecurityQuestion::where('id', $questionData['id'])
                ->update(['order_index' => $questionData['order_index']]);
        }

        return response()->json(['success' => true, 'message' => 'Questions reordered successfully.']);
    }
}

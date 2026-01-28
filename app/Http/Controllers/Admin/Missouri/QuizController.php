<?php

namespace App\Http\Controllers\Admin\Missouri;

use App\Http\Controllers\Controller;
use App\Models\Chapter;
use App\Models\ChapterQuestion;
use App\Models\Course;
use App\Models\MissouriQuizBank;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    public function index(Request $request)
    {
        $query = ChapterQuestion::with(['chapter.course'])
            ->whereHas('chapter.course', function($q) {
                $q->where('state', 'missouri');
            })
            ->orderBy('created_at', 'desc');

        if ($request->filled('course_id')) {
            $query->whereHas('chapter', function($q) use ($request) {
                $q->where('course_id', $request->course_id);
            });
        }

        if ($request->filled('chapter_id')) {
            $query->where('chapter_id', $request->chapter_id);
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('question_text', 'like', '%' . $request->search . '%')
                  ->orWhere('option_a', 'like', '%' . $request->search . '%')
                  ->orWhere('option_b', 'like', '%' . $request->search . '%')
                  ->orWhere('option_c', 'like', '%' . $request->search . '%')
                  ->orWhere('option_d', 'like', '%' . $request->search . '%');
            });
        }

        $questions = $query->paginate(20);
        $courses = Course::where('state', 'missouri')->orderBy('title')->get();

        // Missouri-specific quiz bank stats
        $quizBankStats = [
            'total_bank_questions' => MissouriQuizBank::count(),
            'active_bank_questions' => MissouriQuizBank::where('is_active', true)->count(),
        ];

        return view('admin.missouri.quizzes.index', compact('questions', 'courses', 'quizBankStats'));
    }

    public function create(Request $request)
    {
        $chapterId = $request->get('chapter_id');
        $chapter = $chapterId ? Chapter::findOrFail($chapterId) : null;
        $courses = Course::where('state', 'missouri')->with('chapters')->orderBy('title')->get();

        return view('admin.missouri.quizzes.create', compact('chapter', 'courses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'chapter_id' => 'required|exists:chapters,id',
            'question_text' => 'required|string',
            'option_a' => 'required|string|max:255',
            'option_b' => 'required|string|max:255',
            'option_c' => 'required|string|max:255',
            'option_d' => 'required|string|max:255',
            'correct_answer' => 'required|in:A,B,C,D',
            'explanation' => 'nullable|string',
            'difficulty_level' => 'nullable|in:easy,medium,hard',
            'points' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]);

        // Verify chapter belongs to Missouri course
        $chapter = Chapter::with('course')->findOrFail($request->chapter_id);
        if ($chapter->course->state !== 'missouri') {
            return redirect()->back()->with('error', 'Invalid chapter selection.');
        }

        $question = ChapterQuestion::create([
            'chapter_id' => $request->chapter_id,
            'question_text' => $request->question_text,
            'option_a' => $request->option_a,
            'option_b' => $request->option_b,
            'option_c' => $request->option_c,
            'option_d' => $request->option_d,
            'correct_answer' => $request->correct_answer,
            'explanation' => $request->explanation,
            'difficulty_level' => $request->difficulty_level ?? 'medium',
            'points' => $request->points ?? 1,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.missouri.quizzes.show', $question)
            ->with('success', 'Missouri quiz question created successfully.');
    }

    public function show(ChapterQuestion $quiz)
    {
        $quiz->load(['chapter.course']);
        
        $stats = [
            'total_attempts' => 0,
            'correct_attempts' => 0,
            'average_score' => 0,
            'difficulty_level' => $quiz->difficulty_level,
        ];

        return view('admin.missouri.quizzes.show', compact('quiz', 'stats'));
    }

    public function edit(ChapterQuestion $quiz)
    {
        $courses = Course::where('state', 'missouri')->with('chapters')->orderBy('title')->get();
        return view('admin.missouri.quizzes.edit', compact('quiz', 'courses'));
    }

    public function update(Request $request, ChapterQuestion $quiz)
    {
        $request->validate([
            'chapter_id' => 'required|exists:chapters,id',
            'question_text' => 'required|string',
            'option_a' => 'required|string|max:255',
            'option_b' => 'required|string|max:255',
            'option_c' => 'required|string|max:255',
            'option_d' => 'required|string|max:255',
            'correct_answer' => 'required|in:A,B,C,D',
            'explanation' => 'nullable|string',
            'difficulty_level' => 'nullable|in:easy,medium,hard',
            'points' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]);

        // Verify chapter belongs to Missouri course
        $chapter = Chapter::with('course')->findOrFail($request->chapter_id);
        if ($chapter->course->state !== 'missouri') {
            return redirect()->back()->with('error', 'Invalid chapter selection.');
        }

        $quiz->update([
            'chapter_id' => $request->chapter_id,
            'question_text' => $request->question_text,
            'option_a' => $request->option_a,
            'option_b' => $request->option_b,
            'option_c' => $request->option_c,
            'option_d' => $request->option_d,
            'correct_answer' => $request->correct_answer,
            'explanation' => $request->explanation,
            'difficulty_level' => $request->difficulty_level,
            'points' => $request->points,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.missouri.quizzes.show', $quiz)
            ->with('success', 'Missouri quiz question updated successfully.');
    }

    public function destroy(ChapterQuestion $quiz)
    {
        $quiz->delete();

        return redirect()->route('admin.missouri.quizzes.index')
            ->with('success', 'Missouri quiz question deleted successfully.');
    }

    // Missouri-specific: Quiz Bank Management
    public function quizBank(Request $request)
    {
        $query = MissouriQuizBank::orderBy('chapter', 'asc')->orderBy('created_at', 'desc');

        if ($request->filled('chapter')) {
            $query->where('chapter', $request->chapter);
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('question', 'like', '%' . $request->search . '%')
                  ->orWhere('option_a', 'like', '%' . $request->search . '%')
                  ->orWhere('option_b', 'like', '%' . $request->search . '%')
                  ->orWhere('option_c', 'like', '%' . $request->search . '%')
                  ->orWhere('option_d', 'like', '%' . $request->search . '%');
            });
        }

        $bankQuestions = $query->paginate(20);
        $chapters = MissouriQuizBank::distinct('chapter')->pluck('chapter')->sort();

        return view('admin.missouri.quiz-bank.index', compact('bankQuestions', 'chapters'));
    }

    public function importFromBank(Request $request)
    {
        $request->validate([
            'chapter_id' => 'required|exists:chapters,id',
            'bank_question_ids' => 'required|array',
            'bank_question_ids.*' => 'integer|exists:missouri_quiz_banks,id',
        ]);

        // Verify chapter belongs to Missouri course
        $chapter = Chapter::with('course')->findOrFail($request->chapter_id);
        if ($chapter->course->state !== 'missouri') {
            return redirect()->back()->with('error', 'Invalid chapter selection.');
        }

        $bankQuestions = MissouriQuizBank::whereIn('id', $request->bank_question_ids)->get();
        $importedCount = 0;

        foreach ($bankQuestions as $bankQuestion) {
            ChapterQuestion::create([
                'chapter_id' => $request->chapter_id,
                'question_text' => $bankQuestion->question,
                'option_a' => $bankQuestion->option_a,
                'option_b' => $bankQuestion->option_b,
                'option_c' => $bankQuestion->option_c,
                'option_d' => $bankQuestion->option_d,
                'correct_answer' => $bankQuestion->correct_answer,
                'explanation' => $bankQuestion->explanation,
                'difficulty_level' => 'medium',
                'points' => 1,
                'is_active' => true,
            ]);
            $importedCount++;
        }

        return redirect()->route('admin.missouri.quizzes.index')
            ->with('success', "{$importedCount} questions imported from Missouri quiz bank.");
    }
}
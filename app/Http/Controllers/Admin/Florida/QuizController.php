<?php

namespace App\Http\Controllers\Admin\Florida;

use App\Http\Controllers\Controller;
use App\Models\Chapter;
use App\Models\ChapterQuestion;
use App\Models\FloridaCourse;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    public function index(Request $request)
    {
        $query = ChapterQuestion::with(['chapter.course'])
            ->whereHas('chapter.course', function($q) {
                // Only Florida courses
                $q->whereRaw('1=1'); // Adjust based on your schema
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
        $courses = FloridaCourse::orderBy('title')->get();

        return view('admin.florida.quizzes.index', compact('questions', 'courses'));
    }

    public function create(Request $request)
    {
        $chapterId = $request->get('chapter_id');
        $chapter = $chapterId ? Chapter::findOrFail($chapterId) : null;
        $courses = FloridaCourse::with('chapters')->orderBy('title')->get();

        return view('admin.florida.quizzes.create', compact('chapter', 'courses'));
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

        return redirect()->route('admin.florida.quizzes.show', $question)
            ->with('success', 'Quiz question created successfully.');
    }

    public function show(ChapterQuestion $quiz)
    {
        $quiz->load(['chapter.course']);
        
        // Get question statistics
        $stats = [
            'total_attempts' => 0, // You can implement this based on your quiz attempt tracking
            'correct_attempts' => 0,
            'average_score' => 0,
            'difficulty_level' => $quiz->difficulty_level,
        ];

        return view('admin.florida.quizzes.show', compact('quiz', 'stats'));
    }

    public function edit(ChapterQuestion $quiz)
    {
        $courses = FloridaCourse::with('chapters')->orderBy('title')->get();
        return view('admin.florida.quizzes.edit', compact('quiz', 'courses'));
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

        return redirect()->route('admin.florida.quizzes.show', $quiz)
            ->with('success', 'Quiz question updated successfully.');
    }

    public function destroy(ChapterQuestion $quiz)
    {
        $quiz->delete();

        return redirect()->route('admin.florida.quizzes.index')
            ->with('success', 'Quiz question deleted successfully.');
    }

    public function bulkImport(Request $request)
    {
        $request->validate([
            'chapter_id' => 'required|exists:chapters,id',
            'questions_file' => 'required|file|mimes:csv,txt',
        ]);

        // Process CSV file
        $file = $request->file('questions_file');
        $questions = [];
        
        if (($handle = fopen($file->getPathname(), 'r')) !== false) {
            $header = fgetcsv($handle); // Skip header row
            
            while (($data = fgetcsv($handle)) !== false) {
                if (count($data) >= 6) { // Minimum required columns
                    $questions[] = [
                        'chapter_id' => $request->chapter_id,
                        'question_text' => $data[0],
                        'option_a' => $data[1],
                        'option_b' => $data[2],
                        'option_c' => $data[3],
                        'option_d' => $data[4],
                        'correct_answer' => strtoupper($data[5]),
                        'explanation' => $data[6] ?? null,
                        'difficulty_level' => $data[7] ?? 'medium',
                        'points' => (int)($data[8] ?? 1),
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
            fclose($handle);
        }

        if (!empty($questions)) {
            ChapterQuestion::insert($questions);
        }

        return redirect()->route('admin.florida.quizzes.index')
            ->with('success', count($questions) . ' questions imported successfully.');
    }

    public function export(Request $request)
    {
        $query = ChapterQuestion::with(['chapter.course']);

        if ($request->filled('course_id')) {
            $query->whereHas('chapter', function($q) use ($request) {
                $q->where('course_id', $request->course_id);
            });
        }

        if ($request->filled('chapter_id')) {
            $query->where('chapter_id', $request->chapter_id);
        }

        $questions = $query->get();

        $filename = 'florida_quiz_questions_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($questions) {
            $file = fopen('php://output', 'w');
            
            // Header row
            fputcsv($file, [
                'Question Text',
                'Option A',
                'Option B', 
                'Option C',
                'Option D',
                'Correct Answer',
                'Explanation',
                'Difficulty Level',
                'Points',
                'Chapter',
                'Course'
            ]);

            // Data rows
            foreach ($questions as $question) {
                fputcsv($file, [
                    $question->question_text,
                    $question->option_a,
                    $question->option_b,
                    $question->option_c,
                    $question->option_d,
                    $question->correct_answer,
                    $question->explanation,
                    $question->difficulty_level,
                    $question->points,
                    $question->chapter->title,
                    $question->chapter->course->title,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
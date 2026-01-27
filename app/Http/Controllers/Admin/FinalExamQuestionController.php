<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FinalExamQuestionController extends Controller
{
    /**
     * Display a listing of final exam questions
     */
    public function index(Request $request)
    {
        $courseId = $request->get('course_id');
        $search = $request->get('search');
        $perPage = $request->get('per_page', 25);

        // Get available courses from both tables
        $regularCourses = DB::table('courses')
            ->select('id', 'title', 'state_code', DB::raw("'courses' as table_type"))
            ->orderBy('title')
            ->get();

        $floridaCourses = DB::table('florida_courses')
            ->select('id', 'title', 'state_code', DB::raw("'florida_courses' as table_type"))
            ->orderBy('title')
            ->get();

        // Combine both course collections
        $courses = $regularCourses->concat($floridaCourses)->sortBy('title');

        // If no course selected, default to first available course
        if (!$courseId && $courses->isNotEmpty()) {
            $courseId = $courses->first()->id;
        }

        // Build query for final exam questions with course information
        $query = DB::table('final_exam_questions')
            ->leftJoin('courses', 'final_exam_questions.course_id', '=', 'courses.id')
            ->leftJoin('florida_courses', 'final_exam_questions.course_id', '=', 'florida_courses.id')
            ->select(
                'final_exam_questions.*',
                DB::raw('COALESCE(courses.title, florida_courses.title) as course_title'),
                DB::raw('COALESCE(courses.state_code, florida_courses.state_code) as course_state_code'),
                DB::raw('CASE 
                    WHEN courses.id IS NOT NULL THEN "Regular" 
                    WHEN florida_courses.id IS NOT NULL THEN "Florida" 
                    ELSE "Unknown" 
                END as course_type')
            );
        
        if ($courseId) {
            $query->where('final_exam_questions.course_id', $courseId);
        }

        if ($search) {
            $query->where('final_exam_questions.question_text', 'LIKE', "%{$search}%");
        }

        $questions = $query->orderBy('final_exam_questions.order_index')
            ->paginate($perPage);

        $totalQuestions = DB::table('final_exam_questions')
            ->when($courseId, function($q) use ($courseId) {
                return $q->where('course_id', $courseId);
            })
            ->count();

        // Get total questions across all courses
        $totalAllQuestions = DB::table('final_exam_questions')->count();

        return view('admin.final-exam-questions.index', compact(
            'questions', 
            'courses', 
            'courseId', 
            'search', 
            'totalQuestions',
            'totalAllQuestions',
            'perPage'
        ));
    }

    /**
     * Show the form for creating a new question
     */
    public function create(Request $request)
    {
        $courseId = $request->get('course_id');
        
        // Get available courses from both tables
        $regularCourses = DB::table('courses')
            ->select('id', 'title', 'state_code', DB::raw("'courses' as table_type"))
            ->orderBy('title')
            ->get();

        $floridaCourses = DB::table('florida_courses')
            ->select('id', 'title', 'state_code', DB::raw("'florida_courses' as table_type"))
            ->orderBy('title')
            ->get();

        // Combine both course collections
        $courses = $regularCourses->concat($floridaCourses)->sortBy('title');

        // If no course selected, default to first available course
        if (!$courseId && $courses->isNotEmpty()) {
            $courseId = $courses->first()->id;
        }

        // Get next order index
        $nextOrder = DB::table('final_exam_questions')
            ->when($courseId, function($q) use ($courseId) {
                return $q->where('course_id', $courseId);
            })
            ->max('order_index') + 1;

        return view('admin.final-exam-questions.create', compact('courses', 'courseId', 'nextOrder'));
    }

    /**
     * Store a newly created question
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'course_id' => 'required|integer',
            'question_text' => 'required|string|max:2000',
            'question_type' => 'required|in:multiple_choice,true_false',
            'option_a' => 'required_if:question_type,multiple_choice|string|max:500',
            'option_b' => 'required_if:question_type,multiple_choice|string|max:500',
            'option_c' => 'nullable|string|max:500',
            'option_d' => 'nullable|string|max:500',
            'correct_answer' => 'required|string|max:500',
            'explanation' => 'nullable|string|max:1000',
            'points' => 'integer|min:1|max:10',
            'order_index' => 'required|integer|min:1'
        ]);

        try {
            // Prepare options based on question type
            if ($validated['question_type'] === 'multiple_choice') {
                $options = [
                    'A' => $validated['option_a'],
                    'B' => $validated['option_b'],
                ];
                
                if (!empty($validated['option_c'])) {
                    $options['C'] = $validated['option_c'];
                }
                
                if (!empty($validated['option_d'])) {
                    $options['D'] = $validated['option_d'];
                }
            } else {
                $options = [
                    'A' => 'True',
                    'B' => 'False'
                ];
            }

            DB::table('final_exam_questions')->insert([
                'course_id' => $validated['course_id'],
                'question_text' => $validated['question_text'],
                'question_type' => $validated['question_type'],
                'options' => json_encode($options),
                'correct_answer' => $validated['correct_answer'],
                'explanation' => $validated['explanation'],
                'points' => $validated['points'] ?? 1,
                'order_index' => $validated['order_index'],
                'created_at' => now(),
                'updated_at' => now()
            ]);

            Log::info('Final exam question created', [
                'course_id' => $validated['course_id'],
                'question_text' => substr($validated['question_text'], 0, 50) . '...'
            ]);

            return redirect()
                ->route('admin.final-exam-questions.index', ['course_id' => $validated['course_id']])
                ->with('success', 'Final exam question created successfully!');

        } catch (\Exception $e) {
            Log::error('Error creating final exam question: ' . $e->getMessage());
            
            return back()
                ->withInput()
                ->with('error', 'Failed to create question. Please try again.');
        }
    }

    /**
     * Show the form for editing a question
     */
    public function edit($id)
    {
        $question = DB::table('final_exam_questions')->where('id', $id)->first();
        
        if (!$question) {
            return redirect()
                ->route('admin.final-exam-questions.index')
                ->with('error', 'Question not found.');
        }

        // Get available courses from both tables
        $regularCourses = DB::table('courses')
            ->select('id', 'title', 'state_code', DB::raw("'courses' as table_type"))
            ->orderBy('title')
            ->get();

        $floridaCourses = DB::table('florida_courses')
            ->select('id', 'title', 'state_code', DB::raw("'florida_courses' as table_type"))
            ->orderBy('title')
            ->get();

        // Combine both course collections
        $courses = $regularCourses->concat($floridaCourses)->sortBy('title');

        // Decode options
        $options = json_decode($question->options, true) ?? [];

        return view('admin.final-exam-questions.edit', compact('question', 'courses', 'options'));
    }

    /**
     * Update the specified question
     */
    public function update(Request $request, $id)
    {
        $question = DB::table('final_exam_questions')->where('id', $id)->first();
        
        if (!$question) {
            return redirect()
                ->route('admin.final-exam-questions.index')
                ->with('error', 'Question not found.');
        }

        $validated = $request->validate([
            'course_id' => 'required|integer',
            'question_text' => 'required|string|max:2000',
            'question_type' => 'required|in:multiple_choice,true_false',
            'option_a' => 'required_if:question_type,multiple_choice|string|max:500',
            'option_b' => 'required_if:question_type,multiple_choice|string|max:500',
            'option_c' => 'nullable|string|max:500',
            'option_d' => 'nullable|string|max:500',
            'correct_answer' => 'required|string|max:500',
            'explanation' => 'nullable|string|max:1000',
            'points' => 'integer|min:1|max:10',
            'order_index' => 'required|integer|min:1'
        ]);

        try {
            // Prepare options based on question type
            if ($validated['question_type'] === 'multiple_choice') {
                $options = [
                    'A' => $validated['option_a'],
                    'B' => $validated['option_b'],
                ];
                
                if (!empty($validated['option_c'])) {
                    $options['C'] = $validated['option_c'];
                }
                
                if (!empty($validated['option_d'])) {
                    $options['D'] = $validated['option_d'];
                }
            } else {
                $options = [
                    'A' => 'True',
                    'B' => 'False'
                ];
            }

            DB::table('final_exam_questions')
                ->where('id', $id)
                ->update([
                    'course_id' => $validated['course_id'],
                    'question_text' => $validated['question_text'],
                    'question_type' => $validated['question_type'],
                    'options' => json_encode($options),
                    'correct_answer' => $validated['correct_answer'],
                    'explanation' => $validated['explanation'],
                    'points' => $validated['points'] ?? 1,
                    'order_index' => $validated['order_index'],
                    'updated_at' => now()
                ]);

            Log::info('Final exam question updated', [
                'id' => $id,
                'course_id' => $validated['course_id']
            ]);

            return redirect()
                ->route('admin.final-exam-questions.index', ['course_id' => $validated['course_id']])
                ->with('success', 'Final exam question updated successfully!');

        } catch (\Exception $e) {
            Log::error('Error updating final exam question: ' . $e->getMessage());
            
            return back()
                ->withInput()
                ->with('error', 'Failed to update question. Please try again.');
        }
    }

    /**
     * Remove the specified question
     */
    public function destroy($id)
    {
        try {
            $question = DB::table('final_exam_questions')->where('id', $id)->first();
            
            if (!$question) {
                return response()->json(['error' => 'Question not found.'], 404);
            }

            DB::table('final_exam_questions')->where('id', $id)->delete();

            Log::info('Final exam question deleted', ['id' => $id]);

            return response()->json(['success' => 'Question deleted successfully!']);

        } catch (\Exception $e) {
            Log::error('Error deleting final exam question: ' . $e->getMessage());
            
            return response()->json(['error' => 'Failed to delete question.'], 500);
        }
    }

    /**
     * Bulk import questions from text file
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:txt|max:2048',
            'course_id' => 'required|integer'
        ]);

        try {
            $file = $request->file('file');
            $courseId = $request->course_id;
            $content = file_get_contents($file->getPathname());
            
            // Parse questions from text file
            $lines = explode("\n", $content);
            $questions = [];
            $currentQuestion = '';
            $currentOptions = [];
            $correctAnswer = null;

            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;

                // Check if it's a question (doesn't start with A), B), C), D))
                if (!preg_match('/^[A-D]\)/', $line)) {
                    // Save previous question if exists
                    if (!empty($currentQuestion) && !empty($currentOptions) && $correctAnswer) {
                        $questions[] = [
                            'question' => $currentQuestion,
                            'options' => $currentOptions,
                            'correct_answer' => $correctAnswer
                        ];
                    }
                    
                    // Start new question
                    $currentQuestion = $line;
                    $currentOptions = [];
                    $correctAnswer = null;
                } else {
                    // It's an option
                    preg_match('/^([A-D])\)\s*(.+)/', $line, $matches);
                    if (count($matches) >= 3) {
                        $optionLetter = $matches[1];
                        $optionText = trim($matches[2]);
                        
                        // Check if this is the correct answer (marked with *)
                        $isCorrect = str_contains($optionText, '*');
                        if ($isCorrect) {
                            $optionText = str_replace('*', '', $optionText);
                            $correctAnswer = $optionLetter;
                        }
                        
                        $currentOptions[$optionLetter] = trim($optionText);
                    }
                }
            }

            // Don't forget the last question
            if (!empty($currentQuestion) && !empty($currentOptions) && $correctAnswer) {
                $questions[] = [
                    'question' => $currentQuestion,
                    'options' => $currentOptions,
                    'correct_answer' => $correctAnswer
                ];
            }

            // Get next order index
            $nextOrder = DB::table('final_exam_questions')
                ->where('course_id', $courseId)
                ->max('order_index') ?? 0;

            // Import questions
            $imported = 0;
            foreach ($questions as $questionData) {
                $nextOrder++;
                
                DB::table('final_exam_questions')->insert([
                    'course_id' => $courseId,
                    'question_text' => $questionData['question'],
                    'question_type' => 'multiple_choice',
                    'options' => json_encode($questionData['options']),
                    'correct_answer' => $questionData['correct_answer'],
                    'explanation' => null,
                    'points' => 1,
                    'order_index' => $nextOrder,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                $imported++;
            }

            Log::info('Final exam questions imported', [
                'course_id' => $courseId,
                'count' => $imported
            ]);

            return redirect()
                ->route('admin.final-exam-questions.index', ['course_id' => $courseId])
                ->with('success', "Successfully imported {$imported} questions!");

        } catch (\Exception $e) {
            Log::error('Error importing final exam questions: ' . $e->getMessage());
            
            return back()->with('error', 'Failed to import questions. Please check the file format.');
        }
    }
}
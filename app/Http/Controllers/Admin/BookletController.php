<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookletOrder;
use App\Models\BookletTemplate;
use App\Models\Course;
use App\Models\CourseBooklet;
use App\Services\BookletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BookletController extends Controller
{
    protected BookletService $bookletService;

    public function __construct(BookletService $bookletService)
    {
        $this->bookletService = $bookletService;
    }

    // Booklet Management
    public function index()
    {
        $booklets = CourseBooklet::with(['course', 'creator'])
            ->latest()
            ->paginate(20);

        return view('admin.booklets.index', compact('booklets'));
    }

    public function create()
    {
        // Get all courses from different tables
        $courses = collect();

        // Main courses table - check if state_code column exists
        try {
            if (\Schema::hasColumn('courses', 'state_code')) {
                $mainCourses = Course::select('id', 'title', 'state_code')->orderBy('title')->get();
            } else {
                $mainCourses = Course::select('id', 'title', \DB::raw('NULL as state_code'))->orderBy('title')->get();
            }
            $courses = $courses->merge($mainCourses);
        } catch (\Exception $e) {
            // If courses table doesn't exist, skip
        }

        // Florida courses
        if (\Schema::hasTable('florida_courses')) {
            try {
                $floridaCourses = \App\Models\FloridaCourse::select('id', 'title', \DB::raw("'FL' as state_code"))
                    ->orderBy('title')
                    ->get();
                $courses = $courses->merge($floridaCourses);
            } catch (\Exception $e) {
                // Skip if error
            }
        }

        // Sort all courses by title
        $courses = $courses->sortBy('title')->values();

        return view('admin.booklets.create', compact('courses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'course_id' => 'required|integer',
            'version' => 'required|string|max:50',
            'title' => 'required|string|max:255',
            'state_code' => 'nullable|string|size:2',
            'is_active' => 'boolean',
        ]);

        // Try to find course in main courses table first
        $course = Course::find($validated['course_id']);

        // If not found, try Florida courses
        if (! $course && \Schema::hasTable('florida_courses')) {
            $course = \App\Models\FloridaCourse::find($validated['course_id']);
        }

        if (! $course) {
            return back()
                ->withInput()
                ->with('error', 'Course not found');
        }

        try {
            $booklet = $this->bookletService->createBooklet($course, $validated);

            return redirect()
                ->route('admin.booklets.show', $booklet)
                ->with('success', 'Booklet created successfully');
        } catch (\Exception $e) {
            \Log::error('Booklet creation failed: '.$e->getMessage());
            \Log::error($e->getTraceAsString());

            return back()
                ->withInput()
                ->with('error', 'Failed to create booklet: '.$e->getMessage());
        }
    }

    public function show(CourseBooklet $booklet)
    {
        $booklet->load(['course', 'creator', 'orders']);

        return view('admin.booklets.show', compact('booklet'));
    }

    public function edit(CourseBooklet $booklet)
    {
        $courses = Course::orderBy('title')->get();

        return view('admin.booklets.edit', compact('booklet', 'courses'));
    }

    public function update(Request $request, CourseBooklet $booklet)
    {
        $validated = $request->validate([
            'version' => 'required|string|max:50',
            'title' => 'required|string|max:255',
            'state_code' => 'nullable|string|size:2',
            'is_active' => 'boolean',
        ]);

        try {
            $this->bookletService->updateBooklet($booklet, $validated);

            return redirect()
                ->route('admin.booklets.show', $booklet)
                ->with('success', 'Booklet updated successfully');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to update booklet: '.$e->getMessage());
        }
    }

    public function destroy(CourseBooklet $booklet)
    {
        try {
            // Delete file
            if ($booklet->file_path && Storage::exists($booklet->file_path)) {
                Storage::delete($booklet->file_path);
            }

            $booklet->delete();

            return redirect()
                ->route('admin.booklets.index')
                ->with('success', 'Booklet deleted successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete booklet: '.$e->getMessage());
        }
    }

    public function preview(CourseBooklet $booklet)
    {
        if (! Storage::exists($booklet->file_path)) {
            abort(404, 'Booklet file not found');
        }

        return response()->file(Storage::path($booklet->file_path));
    }

    public function download(CourseBooklet $booklet)
    {
        if (! Storage::exists($booklet->file_path)) {
            abort(404, 'Booklet file not found');
        }

        return Storage::download($booklet->file_path, $booklet->title.'.pdf');
    }

    public function regenerate(CourseBooklet $booklet)
    {
        try {
            $this->bookletService->updateBooklet($booklet, ['regenerate' => true]);

            return back()->with('success', 'Booklet regenerated successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to regenerate booklet: '.$e->getMessage());
        }
    }

    // Orders
    public function orders(Request $request)
    {
        $query = BookletOrder::with(['enrollment.user', 'enrollment.course', 'booklet']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('format')) {
            $query->where('format', $request->format);
        }

        $orders = $query->latest()->paginate(20);

        return view('admin.booklets.orders.index', compact('orders'));
    }

    public function pendingOrders()
    {
        $orders = BookletOrder::pending()
            ->with(['enrollment.user', 'enrollment.course', 'booklet'])
            ->latest()
            ->paginate(20);

        return view('admin.booklets.orders.pending', compact('orders'));
    }

    public function viewOrder(BookletOrder $order)
    {
        $order->load(['enrollment.user', 'enrollment.course', 'booklet']);

        return view('admin.booklets.orders.show', compact('order'));
    }

    public function generateOrder(BookletOrder $order)
    {
        try {
            $this->bookletService->processOrder($order);

            return back()->with('success', 'Booklet generated successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to generate booklet: '.$e->getMessage());
        }
    }

    public function markPrinted(Request $request, BookletOrder $order)
    {
        $order->markPrinted();

        return back()->with('success', 'Order marked as printed');
    }

    public function markShipped(Request $request, BookletOrder $order)
    {
        $validated = $request->validate([
            'tracking_number' => 'nullable|string|max:100',
        ]);

        $order->markShipped($validated['tracking_number'] ?? null);

        return back()->with('success', 'Order marked as shipped');
    }

    public function bulkGenerate(Request $request)
    {
        $validated = $request->validate([
            'order_ids' => 'required|array',
            'order_ids.*' => 'exists:booklet_orders,id',
        ]);

        $processed = 0;
        $failed = 0;

        foreach ($validated['order_ids'] as $orderId) {
            try {
                $order = BookletOrder::find($orderId);
                $this->bookletService->processOrder($order);
                $processed++;
            } catch (\Exception $e) {
                $failed++;
                \Log::error('Bulk generate failed for order '.$orderId.': '.$e->getMessage());
            }
        }

        return back()->with('success', "Generated {$processed} booklets. Failed: {$failed}");
    }

    public function bulkPrint(Request $request)
    {
        $validated = $request->validate([
            'order_ids' => 'required|array',
            'order_ids.*' => 'exists:booklet_orders,id',
        ]);

        $orders = BookletOrder::whereIn('id', $validated['order_ids'])->get();

        foreach ($orders as $order) {
            $order->markPrinted();
        }

        return back()->with('success', count($orders).' orders marked as printed');
    }

    // Templates
    public function templates()
    {
        $templates = BookletTemplate::orderBy('type')->orderBy('name')->get();

        return view('admin.booklets.templates.index', compact('templates'));
    }

    public function editTemplate(BookletTemplate $template)
    {
        return view('admin.booklets.templates.edit', compact('template'));
    }

    public function updateTemplate(Request $request, BookletTemplate $template)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'content' => 'required|string',
            'css' => 'nullable|string',
            'variables' => 'nullable|json',
            'is_active' => 'boolean',
        ]);

        if (isset($validated['variables'])) {
            $validated['variables'] = json_decode($validated['variables'], true);
        }

        $template->update($validated);

        return back()->with('success', 'Template updated successfully');
    }

    public function previewTemplate(Request $request, BookletTemplate $template)
    {
        $sampleData = json_decode($request->input('sample_data', '{}'), true);

        try {
            $html = $template->render($sampleData);

            return response()->json([
                'success' => true,
                'html' => $html,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }
}

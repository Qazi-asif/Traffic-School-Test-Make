<?php

namespace App\Http\Controllers;

use App\Models\BookletOrder;
use App\Models\UserCourseEnrollment;
use App\Services\BookletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BookletOrderController extends Controller
{
    protected BookletService $bookletService;

    public function __construct(BookletService $bookletService)
    {
        $this->bookletService = $bookletService;
    }

    /**
     * Show booklet order form
     */
    public function create(UserCourseEnrollment $enrollment)
    {
        // Check if user owns this enrollment
        if ($enrollment->user_id !== auth()->id()) {
            abort(403);
        }

        // Check if course has booklets available
        $booklet = \App\Models\CourseBooklet::where('course_id', $enrollment->course_id)
            ->active()
            ->latest()
            ->first();

        if (! $booklet) {
            return redirect()->back()->with('error', 'No booklet available for this course');
        }

        return view('booklets.order', compact('enrollment', 'booklet'));
    }

    /**
     * Store a new booklet order
     */
    public function store(Request $request, UserCourseEnrollment $enrollment)
    {
        // Check if user owns this enrollment
        if ($enrollment->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'format' => 'required|in:pdf_download,print_mail,print_pickup',
        ]);

        try {
            $order = $this->bookletService->createOrder($enrollment, $validated['format']);

            // If PDF download, queue generation immediately
            if ($validated['format'] === 'pdf_download') {
                \App\Jobs\GenerateBookletOrder::dispatch($order);
            }

            return redirect()
                ->route('booklets.show', $order)
                ->with('success', 'Booklet order placed successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to place order: '.$e->getMessage());
        }
    }

    /**
     * Show booklet order details
     */
    public function show(BookletOrder $order)
    {
        // Check if user owns this order
        if ($order->enrollment->user_id !== auth()->id()) {
            abort(403);
        }

        $order->load(['enrollment.course', 'booklet']);

        return view('booklets.show', compact('order'));
    }

    /**
     * Download personalized booklet
     */
    public function download(BookletOrder $order)
    {
        // Check if user owns this order
        if ($order->enrollment->user_id !== auth()->id()) {
            abort(403);
        }

        if (! $order->isDownloadable()) {
            return back()->with('error', 'Booklet is not ready for download');
        }

        if (! Storage::exists($order->file_path)) {
            return back()->with('error', 'Booklet file not found');
        }

        return Storage::download(
            $order->file_path,
            $order->booklet->title.'.pdf'
        );
    }

    /**
     * List user's booklet orders
     */
    public function index()
    {
        $orders = BookletOrder::whereHas('enrollment', function ($query) {
            $query->where('user_id', auth()->id());
        })
            ->with(['enrollment.course', 'booklet'])
            ->latest()
            ->paginate(10);

        return view('booklets.index', compact('orders'));
    }
}

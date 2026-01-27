<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourtMailing;
use App\Models\MailingBatch;
use App\Services\CourtMailingService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CourtMailingController extends Controller
{
    protected $mailingService;

    public function __construct(CourtMailingService $mailingService)
    {
        $this->mailingService = $mailingService;
    }

    public function index()
    {
        $stats = [
            'pending' => CourtMailing::pending()->count(),
            'printed' => CourtMailing::printed()->count(),
            'mailed' => CourtMailing::mailed()->count(),
            'delivered_this_month' => CourtMailing::delivered()
                ->whereMonth('delivered_at', now()->month)
                ->count(),
            'returned' => CourtMailing::returned()->count(),
        ];

        $recentActivity = CourtMailing::with(['enrollment.user', 'court'])
            ->latest()
            ->take(10)
            ->get();

        return view('admin.mail-court.index', compact('stats', 'recentActivity'));
    }

    public function pending(Request $request)
    {
        $filters = $request->only(['state', 'court_id']);
        $mailings = $this->mailingService->getPendingQueue($filters);

        return view('admin.mail-court.pending', compact('mailings', 'filters'));
    }

    public function printed()
    {
        $mailings = $this->mailingService->getPrintedQueue();

        return view('admin.mail-court.printed', compact('mailings'));
    }

    public function mailed()
    {
        $mailings = CourtMailing::with(['enrollment.user', 'court'])
            ->mailed()
            ->orderBy('mailed_at', 'desc')
            ->paginate(50);

        return view('admin.mail-court.mailed', compact('mailings'));
    }

    public function completed()
    {
        $mailings = CourtMailing::with(['enrollment.user', 'court'])
            ->delivered()
            ->orderBy('delivered_at', 'desc')
            ->paginate(50);

        return view('admin.mail-court.completed', compact('mailings'));
    }

    public function returned()
    {
        $mailings = CourtMailing::with(['enrollment.user', 'court'])
            ->returned()
            ->orderBy('returned_at', 'desc')
            ->paginate(50);

        return view('admin.mail-court.returned', compact('mailings'));
    }

    public function show(CourtMailing $mailing)
    {
        $mailing->load(['enrollment.user', 'court', 'logs.performedBy']);

        return view('admin.mail-court.show', compact('mailing'));
    }

    public function markPrinted(CourtMailing $mailing)
    {
        $this->mailingService->markAsPrinted($mailing);

        return back()->with('success', 'Mailing marked as printed');
    }

    public function markMailed(Request $request, CourtMailing $mailing)
    {
        $request->validate([
            'tracking_number' => 'nullable|string|max:255',
            'carrier' => 'nullable|in:usps,fedex,ups,other',
        ]);

        $this->mailingService->markAsMailed($mailing, $request->tracking_number);

        if ($request->carrier) {
            $mailing->update(['carrier' => $request->carrier]);
        }

        return back()->with('success', 'Mailing marked as mailed');
    }

    public function markDelivered(CourtMailing $mailing)
    {
        $this->mailingService->markAsDelivered($mailing);

        return back()->with('success', 'Mailing marked as delivered');
    }

    public function markReturned(Request $request, CourtMailing $mailing)
    {
        $request->validate([
            'return_reason' => 'required|string|max:255',
        ]);

        $this->mailingService->markAsReturned($mailing, $request->return_reason);

        return back()->with('success', 'Mailing marked as returned');
    }

    public function batches()
    {
        $batches = MailingBatch::with('creator')
            ->latest()
            ->paginate(20);

        return view('admin.mail-court.batches.index', compact('batches'));
    }

    public function createBatch(Request $request)
    {
        $batch = $this->mailingService->createBatch($request->notes);

        return redirect()->route('admin.mail-court.batches.show', $batch->id)
            ->with('success', 'Batch created successfully');
    }

    public function viewBatch(MailingBatch $batch)
    {
        $batch->load(['courtMailings.enrollment.user', 'courtMailings.court']);

        return view('admin.mail-court.batches.show', compact('batch'));
    }

    public function addToBatch(Request $request, MailingBatch $batch)
    {
        $request->validate([
            'mailing_ids' => 'required|array',
            'mailing_ids.*' => 'exists:court_mailings,id',
        ]);

        $count = $this->mailingService->addToBatch($batch, $request->mailing_ids);

        return back()->with('success', "Added {$count} items to batch");
    }

    public function printBatch(MailingBatch $batch)
    {
        $count = $this->mailingService->printBatch($batch);

        return back()->with('success', "Printed {$count} items");
    }

    public function mailBatch(Request $request, MailingBatch $batch)
    {
        $count = $this->mailingService->mailBatch($batch, $request->tracking_data ?? []);

        return back()->with('success', "Marked {$count} items as mailed");
    }

    public function closeBatch(MailingBatch $batch)
    {
        $batch->close();

        return back()->with('success', 'Batch closed');
    }

    public function bulkPrint(Request $request)
    {
        $request->validate([
            'mailing_ids' => 'required|array',
            'mailing_ids.*' => 'exists:court_mailings,id',
        ]);

        $count = 0;
        foreach ($request->mailing_ids as $id) {
            $mailing = CourtMailing::find($id);
            if ($mailing && $mailing->status === 'pending') {
                $this->mailingService->markAsPrinted($mailing);
                $count++;
            }
        }

        return back()->with('success', "Printed {$count} mailings");
    }

    public function reports()
    {
        $from = Carbon::now()->startOfMonth();
        $to = Carbon::now()->endOfMonth();

        $stats = $this->mailingService->getMailingStats($from, $to);
        $postage = $this->mailingService->getPostageReport($from, $to);
        $returns = $this->mailingService->getReturnAnalysis();

        return view('admin.mail-court.reports.index', compact('stats', 'postage', 'returns'));
    }
}

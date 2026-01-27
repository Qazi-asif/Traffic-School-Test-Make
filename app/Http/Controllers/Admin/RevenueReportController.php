<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\RevenueReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RevenueReportController extends Controller
{
    protected $revenueService;

    public function __construct(RevenueReportService $revenueService)
    {
        $this->revenueService = $revenueService;
    }

    public function dashboard(Request $request)
    {
        // Get date range from request or default to this month
        $start = $request->filled('start')
            ? Carbon::parse($request->start)
            : Carbon::now()->startOfMonth();

        $end = $request->filled('end')
            ? Carbon::parse($request->end)
            : Carbon::now()->endOfMonth();

        $stats = $this->revenueService->getDashboardStats();
        $currentPeriod = $this->revenueService->getStatsForPeriod($start, $end);
        $comparison = $this->revenueService->compareWithPreviousPeriod($start, $end);

        $byState = $this->revenueService->getRevenueByState($start, $end);
        $byCourse = $this->revenueService->getRevenueByCourse($start, $end);
        $byPaymentMethod = $this->revenueService->getRevenueByPaymentMethod($start, $end);
        $trend = $this->revenueService->getRevenueTrend($start, $end, 'day');

        return view('admin.revenue.dashboard', compact(
            'stats',
            'currentPeriod',
            'comparison',
            'byState',
            'byCourse',
            'byPaymentMethod',
            'trend',
            'start',
            'end'
        ));
    }

    public function byState(Request $request)
    {
        $start = $request->filled('start')
            ? Carbon::parse($request->start)
            : Carbon::now()->startOfMonth();

        $end = $request->filled('end')
            ? Carbon::parse($request->end)
            : Carbon::now()->endOfMonth();

        $byState = $this->revenueService->getRevenueByState($start, $end);
        $stats = $this->revenueService->getStatsForPeriod($start, $end);

        return view('admin.revenue.by-state', compact('byState', 'stats', 'start', 'end'));
    }

    public function byCourse(Request $request)
    {
        $start = $request->filled('start')
            ? Carbon::parse($request->start)
            : Carbon::now()->startOfMonth();

        $end = $request->filled('end')
            ? Carbon::parse($request->end)
            : Carbon::now()->endOfMonth();

        $byCourse = $this->revenueService->getRevenueByCourse($start, $end);
        $stats = $this->revenueService->getStatsForPeriod($start, $end);

        return view('admin.revenue.by-course', compact('byCourse', 'stats', 'start', 'end'));
    }

    public function export(Request $request)
    {
        $start = $request->filled('start')
            ? Carbon::parse($request->start)
            : Carbon::now()->startOfMonth();

        $end = $request->filled('end')
            ? Carbon::parse($request->end)
            : Carbon::now()->endOfMonth();

        $filepath = $this->revenueService->exportToCsv($start, $end);

        return response()->download($filepath)->deleteFileAfterSend(true);
    }
}

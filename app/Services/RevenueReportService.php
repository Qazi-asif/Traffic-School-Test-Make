<?php

namespace App\Services;

use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RevenueReportService
{
    public function getDashboardStats(): array
    {
        return [
            'today' => $this->getTodayStats(),
            'this_week' => $this->getThisWeekStats(),
            'this_month' => $this->getThisMonthStats(),
            'this_year' => $this->getThisYearStats(),
        ];
    }

    public function getTodayStats(): array
    {
        $today = Carbon::today();

        return $this->getStatsForPeriod($today, $today->copy()->endOfDay());
    }

    public function getThisWeekStats(): array
    {
        $start = Carbon::now()->startOfWeek();
        $end = Carbon::now()->endOfWeek();

        return $this->getStatsForPeriod($start, $end);
    }

    public function getThisMonthStats(): array
    {
        $start = Carbon::now()->startOfMonth();
        $end = Carbon::now()->endOfMonth();

        return $this->getStatsForPeriod($start, $end);
    }

    public function getThisYearStats(): array
    {
        $start = Carbon::now()->startOfYear();
        $end = Carbon::now()->endOfYear();

        return $this->getStatsForPeriod($start, $end);
    }

    public function getStatsForPeriod(Carbon $start, Carbon $end): array
    {
        $payments = Payment::where('status', 'completed')
            ->whereBetween('created_at', [$start, $end]);

        $grossRevenue = $payments->sum('amount');
        $transactionCount = $payments->count();

        $refunds = Payment::where('status', 'refunded')
            ->whereBetween('refunded_at', [$start, $end])
            ->sum('amount');

        $netRevenue = $grossRevenue - $refunds;
        $averageOrder = $transactionCount > 0 ? $grossRevenue / $transactionCount : 0;

        return [
            'gross_revenue' => round($grossRevenue, 2),
            'refunds' => round($refunds, 2),
            'net_revenue' => round($netRevenue, 2),
            'transaction_count' => $transactionCount,
            'average_order' => round($averageOrder, 2),
            'period' => [
                'start' => $start->format('Y-m-d'),
                'end' => $end->format('Y-m-d'),
            ],
        ];
    }

    public function getRevenueByState(Carbon $start, Carbon $end): array
    {
        return Payment::where('status', 'completed')
            ->whereBetween('created_at', [$start, $end])
            ->select('state', DB::raw('SUM(amount) as revenue'), DB::raw('COUNT(*) as count'))
            ->groupBy('state')
            ->orderByDesc('revenue')
            ->get()
            ->map(function ($item) {
                return [
                    'state' => $item->state ?? 'Unknown',
                    'revenue' => round($item->revenue, 2),
                    'count' => $item->count,
                ];
            })
            ->toArray();
    }

    public function getRevenueByCourse(Carbon $start, Carbon $end): array
    {
        return Payment::where('status', 'completed')
            ->whereBetween('created_at', [$start, $end])
            ->with('enrollment.course')
            ->get()
            ->groupBy(function ($payment) {
                return $payment->enrollment->course->title ?? 'Unknown';
            })
            ->map(function ($group, $course) {
                return [
                    'course' => $course,
                    'revenue' => round($group->sum('amount'), 2),
                    'count' => $group->count(),
                ];
            })
            ->sortByDesc('revenue')
            ->values()
            ->toArray();
    }

    public function getRevenueByPaymentMethod(Carbon $start, Carbon $end): array
    {
        return Payment::where('status', 'completed')
            ->whereBetween('created_at', [$start, $end])
            ->select('payment_method', DB::raw('SUM(amount) as revenue'), DB::raw('COUNT(*) as count'))
            ->groupBy('payment_method')
            ->orderByDesc('revenue')
            ->get()
            ->map(function ($item) {
                return [
                    'method' => $item->payment_method ?? 'Unknown',
                    'revenue' => round($item->revenue, 2),
                    'count' => $item->count,
                ];
            })
            ->toArray();
    }

    public function getRevenueTrend(Carbon $start, Carbon $end, string $groupBy = 'day'): array
    {
        $format = match ($groupBy) {
            'hour' => '%Y-%m-%d %H:00:00',
            'day' => '%Y-%m-%d',
            'week' => '%Y-%u',
            'month' => '%Y-%m',
            default => '%Y-%m-%d',
        };

        return Payment::where('status', 'completed')
            ->whereBetween('created_at', [$start, $end])
            ->select(
                DB::raw("DATE_FORMAT(created_at, '{$format}') as period"),
                DB::raw('SUM(amount) as revenue'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('period')
            ->orderBy('period')
            ->get()
            ->map(function ($item) {
                return [
                    'period' => $item->period,
                    'revenue' => round($item->revenue, 2),
                    'count' => $item->count,
                ];
            })
            ->toArray();
    }

    public function exportToCsv(Carbon $start, Carbon $end): string
    {
        $payments = Payment::where('status', 'completed')
            ->whereBetween('created_at', [$start, $end])
            ->with(['user', 'enrollment.course'])
            ->get();

        $filename = 'revenue_report_'.$start->format('Y-m-d').'_to_'.$end->format('Y-m-d').'.csv';
        $filepath = storage_path('app/exports/'.$filename);

        if (! file_exists(dirname($filepath))) {
            mkdir(dirname($filepath), 0755, true);
        }

        $file = fopen($filepath, 'w');

        // Headers
        fputcsv($file, [
            'Date',
            'Transaction ID',
            'Customer',
            'Email',
            'Course',
            'State',
            'Amount',
            'Payment Method',
            'Status',
        ]);

        // Data
        foreach ($payments as $payment) {
            fputcsv($file, [
                $payment->created_at->format('Y-m-d H:i:s'),
                $payment->id,
                $payment->user->name ?? 'N/A',
                $payment->billing_email ?? $payment->user->email ?? 'N/A',
                $payment->enrollment->course->title ?? 'N/A',
                $payment->state ?? 'N/A',
                $payment->amount,
                $payment->payment_method ?? 'N/A',
                $payment->status,
            ]);
        }

        fclose($file);

        return $filepath;
    }

    public function compareWithPreviousPeriod(Carbon $start, Carbon $end): array
    {
        $currentStats = $this->getStatsForPeriod($start, $end);

        $days = $start->diffInDays($end);
        $previousStart = $start->copy()->subDays($days + 1);
        $previousEnd = $start->copy()->subDay();

        $previousStats = $this->getStatsForPeriod($previousStart, $previousEnd);

        $change = $currentStats['net_revenue'] - $previousStats['net_revenue'];
        $changePercent = $previousStats['net_revenue'] > 0
            ? ($change / $previousStats['net_revenue']) * 100
            : 0;

        return [
            'current' => $currentStats,
            'previous' => $previousStats,
            'change' => round($change, 2),
            'change_percent' => round($changePercent, 2),
        ];
    }
}

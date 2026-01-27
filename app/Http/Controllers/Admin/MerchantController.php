<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MerchantAccount;
use App\Models\MerchantReconciliation;
use App\Services\MerchantService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MerchantController extends Controller
{
    public function __construct(protected MerchantService $merchantService) {}

    public function index()
    {
        $accounts = MerchantAccount::with('gateway')->orderBy('is_primary', 'desc')->get();

        return view('admin.merchants.index', compact('accounts'));
    }

    public function show(MerchantAccount $account)
    {
        $account->load(['gateway', 'transactions' => fn ($q) => $q->latest()->limit(10)]);

        $balance = $account->balance;
        $pendingPayout = $account->pending_payout;
        $recentPayouts = $account->payouts()->latest()->limit(5)->get();

        $start = now()->startOfMonth();
        $end = now()->endOfMonth();
        $summary = $this->merchantService->getTransactionSummary($account, $start, $end);

        return view('admin.merchants.show', compact('account', 'balance', 'pendingPayout', 'recentPayouts', 'summary'));
    }

    public function transactions(MerchantAccount $account, Request $request)
    {
        $query = $account->transactions()->with('payment');

        if ($request->type) {
            $query->where('transaction_type', $request->type);
        }

        if ($request->start_date) {
            $query->whereDate('processed_at', '>=', $request->start_date);
        }

        if ($request->end_date) {
            $query->whereDate('processed_at', '<=', $request->end_date);
        }

        $transactions = $query->orderByDesc('processed_at')->paginate(50);

        return view('admin.merchants.transactions', compact('account', 'transactions'));
    }

    public function payouts(MerchantAccount $account)
    {
        $payouts = $account->payouts()->orderByDesc('initiated_at')->paginate(20);

        return view('admin.merchants.payouts', compact('account', 'payouts'));
    }

    public function reconciliationIndex(MerchantAccount $account)
    {
        $reconciliations = $account->reconciliations()->with('reconciledBy')->orderByDesc('period_end')->paginate(20);

        return view('admin.merchants.reconciliation.index', compact('account', 'reconciliations'));
    }

    public function createReconciliation(Request $request, MerchantAccount $account)
    {
        $request->validate([
            'period_start' => 'required|date',
            'period_end' => 'required|date|after:period_start',
        ]);

        $reconciliation = $this->merchantService->createReconciliation(
            $account,
            Carbon::parse($request->period_start),
            Carbon::parse($request->period_end)
        );

        return redirect()->route('admin.merchants.reconciliation.show', $reconciliation)
            ->with('success', 'Reconciliation created successfully');
    }

    public function showReconciliation(MerchantReconciliation $reconciliation)
    {
        $reconciliation->load(['merchantAccount.gateway', 'reconciledBy']);

        return view('admin.merchants.reconciliation.show', compact('reconciliation'));
    }

    public function resolveReconciliation(Request $request, MerchantReconciliation $reconciliation)
    {
        $request->validate(['notes' => 'required|string']);

        $this->merchantService->resolveDiscrepancy($reconciliation, $request->notes);

        return redirect()->route('admin.merchants.reconciliation.show', $reconciliation)
            ->with('success', 'Reconciliation marked as resolved');
    }

    public function syncWithGateway(MerchantAccount $account)
    {
        $start = now()->subDays(30);
        $end = now();

        $synced = $this->merchantService->syncTransactions($account, $start, $end);

        return back()->with('success', "Synced {$synced} transactions from gateway");
    }

    public function reportsSummary(Request $request)
    {
        $start = $request->start ? Carbon::parse($request->start) : now()->startOfMonth();
        $end = $request->end ? Carbon::parse($request->end) : now()->endOfMonth();

        $accounts = MerchantAccount::with('gateway')->active()->get();

        $summaries = $accounts->map(function ($account) use ($start, $end) {
            return [
                'account' => $account,
                'summary' => $this->merchantService->getTransactionSummary($account, $start, $end),
            ];
        });

        return view('admin.merchants.reports.summary', compact('summaries', 'start', 'end'));
    }
}

<?php

namespace App\Services;

use App\Models\MerchantAccount;
use App\Models\MerchantFee;
use App\Models\MerchantPayout;
use App\Models\MerchantReconciliation;
use App\Models\MerchantTransaction;
use App\Models\Payment;
use App\Models\PaymentGateway;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MerchantService
{
    // Account Management
    public function createAccount(PaymentGateway $gateway, array $data): MerchantAccount
    {
        if (! empty($data['is_primary'])) {
            MerchantAccount::where('gateway_id', $gateway->id)->update(['is_primary' => false]);
        }

        return MerchantAccount::create(array_merge($data, ['gateway_id' => $gateway->id]));
    }

    public function updateAccount(MerchantAccount $account, array $data): MerchantAccount
    {
        if (! empty($data['is_primary']) && ! $account->is_primary) {
            MerchantAccount::where('gateway_id', $account->gateway_id)->update(['is_primary' => false]);
        }

        $account->update($data);

        return $account->fresh();
    }

    public function setAsPrimary(MerchantAccount $account): void
    {
        DB::transaction(function () use ($account) {
            MerchantAccount::where('gateway_id', $account->gateway_id)->update(['is_primary' => false]);
            $account->update(['is_primary' => true]);
        });
    }

    // Transaction Recording
    public function recordCharge(Payment $payment, array $gatewayResponse): MerchantTransaction
    {
        $account = $this->getPrimaryAccountForGateway($payment->gateway ?? 'authorize_net');

        if (! $account) {
            throw new \Exception('No merchant account found for gateway');
        }

        $grossAmount = $payment->amount;
        $feeAmount = $this->calculateFee($grossAmount, $account);
        $netAmount = $grossAmount - $feeAmount;

        return MerchantTransaction::create([
            'merchant_account_id' => $account->id,
            'payment_id' => $payment->id,
            'transaction_type' => 'charge',
            'gross_amount' => $grossAmount,
            'fee_amount' => $feeAmount,
            'net_amount' => $netAmount,
            'currency' => $account->currency,
            'gateway_transaction_id' => $gatewayResponse['transaction_id'] ?? null,
            'status' => 'completed',
            'description' => 'Payment for enrollment #'.($payment->enrollment_id ?? 'N/A'),
            'processed_at' => now(),
            'metadata' => $gatewayResponse,
        ]);
    }

    public function recordRefund(Payment $payment, float $amount, array $gatewayResponse): MerchantTransaction
    {
        $account = $this->getPrimaryAccountForGateway($payment->gateway ?? 'authorize_net');

        if (! $account) {
            throw new \Exception('No merchant account found for gateway');
        }

        return MerchantTransaction::create([
            'merchant_account_id' => $account->id,
            'payment_id' => $payment->id,
            'transaction_type' => 'refund',
            'gross_amount' => -$amount,
            'fee_amount' => 0,
            'net_amount' => -$amount,
            'currency' => $account->currency,
            'gateway_transaction_id' => $gatewayResponse['transaction_id'] ?? null,
            'status' => 'completed',
            'description' => 'Refund for payment #'.$payment->id,
            'processed_at' => now(),
            'metadata' => $gatewayResponse,
        ]);
    }

    public function recordFee(MerchantAccount $account, string $type, float $amount, string $description): MerchantFee
    {
        return MerchantFee::create([
            'merchant_account_id' => $account->id,
            'fee_type' => $type,
            'description' => $description,
            'amount' => $amount,
        ]);
    }

    // Payouts
    public function getExpectedPayout(MerchantAccount $account): float
    {
        $balance = $account->transactions()
            ->completed()
            ->unsettled()
            ->sum('net_amount');

        $reserve = $account->reserve_percent ? ($balance * $account->reserve_percent / 100) : 0;

        return max(0, $balance - $reserve);
    }

    public function recordPayout(MerchantAccount $account, array $payoutData): MerchantPayout
    {
        $payout = MerchantPayout::create(array_merge($payoutData, [
            'merchant_account_id' => $account->id,
            'payout_reference' => $payoutData['payout_reference'] ?? 'PO-'.time(),
            'initiated_at' => now(),
        ]));

        $account->update(['last_payout_at' => now()]);

        return $payout;
    }

    public function updatePayoutStatus(MerchantPayout $payout, string $status): void
    {
        $payout->update(['status' => $status]);

        if ($status === 'paid') {
            $payout->update(['arrived_at' => now()]);

            // Mark transactions as settled
            if ($payout->transaction_ids) {
                MerchantTransaction::whereIn('id', $payout->transaction_ids)
                    ->update(['settled_at' => now()]);
            }
        }
    }

    // Reconciliation
    public function createReconciliation(MerchantAccount $account, Carbon $start, Carbon $end): MerchantReconciliation
    {
        $expectedRevenue = $this->calculateExpectedRevenue($account, $start, $end);
        $expectedFees = $this->calculateExpectedFees($account, $start, $end);

        return MerchantReconciliation::create([
            'merchant_account_id' => $account->id,
            'period_start' => $start,
            'period_end' => $end,
            'expected_revenue' => $expectedRevenue,
            'expected_fees' => $expectedFees,
            'status' => 'pending',
        ]);
    }

    public function calculateExpectedRevenue(MerchantAccount $account, Carbon $start, Carbon $end): float
    {
        return $account->transactions()
            ->where('transaction_type', 'charge')
            ->whereBetween('processed_at', [$start, $end])
            ->sum('gross_amount');
    }

    protected function calculateExpectedFees(MerchantAccount $account, Carbon $start, Carbon $end): float
    {
        return $account->transactions()
            ->whereBetween('processed_at', [$start, $end])
            ->sum('fee_amount');
    }

    public function fetchActualFromGateway(MerchantAccount $account, Carbon $start, Carbon $end): array
    {
        // Placeholder - implement gateway-specific API calls
        return [
            'revenue' => 0,
            'fees' => 0,
        ];
    }

    public function resolveDiscrepancy(MerchantReconciliation $reconciliation, string $notes): void
    {
        $reconciliation->update([
            'status' => 'resolved',
            'notes' => $notes,
            'reconciled_by' => auth()->id(),
            'reconciled_at' => now(),
        ]);
    }

    // Reporting
    public function getTransactionSummary(MerchantAccount $account, Carbon $start, Carbon $end): array
    {
        $transactions = $account->transactions()
            ->whereBetween('processed_at', [$start, $end])
            ->get();

        return [
            'total_charges' => $transactions->where('transaction_type', 'charge')->sum('gross_amount'),
            'total_refunds' => abs($transactions->where('transaction_type', 'refund')->sum('gross_amount')),
            'total_fees' => $transactions->sum('fee_amount'),
            'net_revenue' => $transactions->sum('net_amount'),
            'transaction_count' => $transactions->count(),
        ];
    }

    public function getFeesSummary(MerchantAccount $account, Carbon $start, Carbon $end): array
    {
        $fees = $account->fees()
            ->whereBetween('created_at', [$start, $end])
            ->get();

        return [
            'total_fees' => $fees->sum('amount'),
            'by_type' => $fees->groupBy('fee_type')->map->sum('amount'),
        ];
    }

    public function getPayoutHistory(MerchantAccount $account, int $limit = 10): Collection
    {
        return $account->payouts()
            ->orderByDesc('initiated_at')
            ->limit($limit)
            ->get();
    }

    // Sync with Gateway (placeholder methods)
    public function syncTransactions(MerchantAccount $account, Carbon $start, Carbon $end): int
    {
        // Implement gateway-specific sync logic
        return 0;
    }

    public function syncPayouts(MerchantAccount $account): int
    {
        // Implement gateway-specific sync logic
        return 0;
    }

    public function syncFees(MerchantAccount $account, Carbon $start, Carbon $end): int
    {
        // Implement gateway-specific sync logic
        return 0;
    }

    // Helper Methods
    protected function getPrimaryAccountForGateway(string $gatewayCode): ?MerchantAccount
    {
        $gateway = PaymentGateway::where('code', $gatewayCode)->first();

        if (! $gateway) {
            return null;
        }

        return MerchantAccount::where('gateway_id', $gateway->id)
            ->where('is_primary', true)
            ->where('is_active', true)
            ->first();
    }

    protected function calculateFee(float $amount, MerchantAccount $account): float
    {
        $gateway = $account->gateway;

        if (! $gateway) {
            return 0;
        }

        return $gateway->calculateFee($amount);
    }
}

<?php

namespace App\Console\Commands;

use App\Models\MerchantAccount;
use App\Services\MerchantService;
use Illuminate\Console\Command;

class MerchantPayoutReportCommand extends Command
{
    protected $signature = 'merchant:payout-report';

    protected $description = 'Generate daily payout status report';

    public function handle(MerchantService $merchantService)
    {
        $accounts = MerchantAccount::active()->get();

        if ($accounts->isEmpty()) {
            $this->info('No active merchant accounts');

            return 0;
        }

        $this->info('Merchant Payout Status Report - '.now()->format('Y-m-d'));
        $this->line('');

        $data = [];

        foreach ($accounts as $account) {
            $expectedPayout = $merchantService->getExpectedPayout($account);
            $recentPayouts = $account->payouts()->where('status', 'pending')->count();

            $data[] = [
                $account->account_name,
                $account->gateway->display_name,
                '$'.number_format($account->balance, 2),
                '$'.number_format($expectedPayout, 2),
                $recentPayouts,
            ];
        }

        $this->table(
            ['Account', 'Gateway', 'Balance', 'Expected Payout', 'Pending Payouts'],
            $data
        );

        return 0;
    }
}

<?php

namespace Database\Seeders;

use App\Models\Payment;
use App\Models\User;
use App\Models\UserCourseEnrollment;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    public function run()
    {
        $users = User::all();
        $enrollments = UserCourseEnrollment::all();

        if ($users->isEmpty() || $enrollments->isEmpty()) {
            $this->command->warn('Skipping PaymentSeeder: No users or enrollments found.');

            return;
        }

        $paymentMethods = ['credit_card', 'debit_card', 'paypal', 'stripe', 'bank_transfer'];
        $gateways = ['stripe', 'paypal', 'square', 'authorize_net'];
        $statuses = ['pending', 'completed', 'failed', 'refunded'];

        foreach ($enrollments as $enrollment) {
            Payment::create([
                'user_id' => $enrollment->user_id,
                'enrollment_id' => $enrollment->id,
                'amount' => rand(2500, 15000) / 100, // $25.00 to $150.00
                'currency' => 'USD',
                'payment_method' => $paymentMethods[array_rand($paymentMethods)],
                'gateway' => $gateways[array_rand($gateways)],
                'gateway_transaction_id' => 'txn_'.uniqid(),
                'status' => $statuses[array_rand($statuses)],
                'gateway_response' => json_encode([
                    'transaction_id' => 'txn_'.uniqid(),
                    'response_code' => '00',
                    'message' => 'Transaction approved',
                ]),
                'processed_at' => now()->subDays(rand(1, 30)),
                'created_at' => now()->subDays(rand(1, 60)),
                'updated_at' => now()->subDays(rand(1, 30)),
            ]);
        }

        // Create some additional random payments
        for ($i = 0; $i < 50; $i++) {
            Payment::create([
                'user_id' => $users->random()->id,
                'enrollment_id' => $enrollments->random()->id,
                'amount' => rand(2500, 15000) / 100,
                'currency' => 'USD',
                'payment_method' => $paymentMethods[array_rand($paymentMethods)],
                'gateway' => $gateways[array_rand($gateways)],
                'gateway_transaction_id' => 'txn_'.uniqid(),
                'status' => $statuses[array_rand($statuses)],
                'gateway_response' => json_encode([
                    'transaction_id' => 'txn_'.uniqid(),
                    'response_code' => rand(0, 1) ? '00' : '05',
                    'message' => rand(0, 1) ? 'Transaction approved' : 'Transaction declined',
                ]),
                'processed_at' => now()->subDays(rand(1, 90)),
                'created_at' => now()->subDays(rand(1, 120)),
                'updated_at' => now()->subDays(rand(1, 60)),
            ]);
        }
    }
}

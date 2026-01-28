<?php

namespace App\Console\Commands;

use App\Models\FloridaCertificate;
use App\Models\Payment;
use App\Models\StateTransmission;
use App\Models\User;
use App\Models\UserCourseEnrollment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;

class ProductionReadinessCheck extends Command
{
    protected $signature = 'system:production-readiness';
    protected $description = 'Comprehensive production readiness check for the traffic school platform';

    public function handle()
    {
        $this->info('🚀 PRODUCTION READINESS CHECK');
        $this->info('Multi-State Traffic School Platform');
        $this->info('Generated: ' . now()->format('Y-m-d H:i:s'));
        $this->line('');
        
        $checks = [
            'Certificate Email Delivery' => $this->checkCertificateEmails(),
            'Citation Number Validation' => $this->checkCitationNumbers(),
            'State API Integration' => $this->checkStateApis(),
            'Payment Gateway Configuration' => $this->checkPaymentGateways(),
            'Security Configuration' => $this->checkSecurity(),
            'Database Integrity' => $this->checkDatabase(),
            'System Performance' => $this->checkPerformance(),
        ];
        
        $this->displayResults($checks);
        
        return 0;
    }
    
    private function checkCertificateEmails()
    {
        $totalCertificates = FloridaCertificate::count();
        $emailedCertificates = FloridaCertificate::where('is_sent_to_student', true)->count();
        $pendingEmails = $totalCertificates - $emailedCertificates;
        
        $emailSuccessRate = $totalCertificates > 0 ? ($emailedCertificates / $totalCertificates) * 100 : 0;
        
        return [
            'status' => $emailSuccessRate >= 95 ? 'PASS' : ($emailSuccessRate >= 50 ? 'WARN' : 'FAIL'),
            'score' => round($emailSuccessRate, 1),
            'details' => [
                "Total certificates: {$totalCertificates}",
                "Emailed successfully: {$emailedCertificates}",
                "Pending emails: {$pendingEmails}",
                "Success rate: " . round($emailSuccessRate, 1) . "%"
            ],
            'recommendations' => $emailSuccessRate < 95 ? [
                'Run: php artisan certificates:send-pending-emails',
                'Check email configuration (SMTP settings)',
                'Verify email templates exist',
                'Set up email delivery monitoring'
            ] : []
        ];
    }
    
    private function checkCitationNumbers()
    {
        $totalEnrollments = UserCourseEnrollment::count();
        $enrollmentsWithCitation = UserCourseEnrollment::whereNotNull('citation_number')
            ->where('citation_number', '!=', '')
            ->count();
        
        $failedTransmissions = StateTransmission::where('status', 'error')
            ->where('response_message', 'like', '%Citation number is required%')
            ->count();
        
        $citationCompleteness = $totalEnrollments > 0 ? ($enrollmentsWithCitation / $totalEnrollments) * 100 : 0;
        
        return [
            'status' => $citationCompleteness >= 95 && $failedTransmissions === 0 ? 'PASS' : 'FAIL',
            'score' => round($citationCompleteness, 1),
            'details' => [
                "Total enrollments: {$totalEnrollments}",
                "With citation numbers: {$enrollmentsWithCitation}",
                "Failed transmissions: {$failedTransmissions}",
                "Completeness: " . round($citationCompleteness, 1) . "%"
            ],
            'recommendations' => $citationCompleteness < 95 || $failedTransmissions > 0 ? [
                'Run: php artisan enrollments:fix-citation-numbers',
                'Check insurance discount only cases',
                'Validate registration form citation collection',
                'Reset failed state transmissions'
            ] : []
        ];
    }
    
    private function checkStateApis()
    {
        $totalTransmissions = StateTransmission::count();
        $successfulTransmissions = StateTransmission::where('status', 'success')->count();
        $failedTransmissions = StateTransmission::where('status', 'error')->count();
        $pendingTransmissions = StateTransmission::where('status', 'pending')->count();
        
        $successRate = $totalTransmissions > 0 ? ($successfulTransmissions / $totalTransmissions) * 100 : 0;
        
        return [
            'status' => $successRate >= 90 ? 'PASS' : ($successRate >= 50 ? 'WARN' : 'FAIL'),
            'score' => round($successRate, 1),
            'details' => [
                "Total transmissions: {$totalTransmissions}",
                "Successful: {$successfulTransmissions}",
                "Failed: {$failedTransmissions}",
                "Pending: {$pendingTransmissions}",
                "Success rate: " . round($successRate, 1) . "%"
            ],
            'recommendations' => $successRate < 90 ? [
                'Run: php artisan state:test-apis',
                'Contact Florida FLHSMV for IP whitelisting',
                'Contact California DMV for TVCC service status',
                'Get correct URLs for Nevada NTSA and CCS',
                'Enable fallback/mock mode for production'
            ] : []
        ];
    }
    
    private function checkPaymentGateways()
    {
        $totalPayments = Payment::count();
        $dummyPayments = Payment::where('payment_method', 'dummy')->count();
        $realPayments = $totalPayments - $dummyPayments;
        
        $stripeConfigured = config('services.stripe.secret') ? true : false;
        $paypalConfigured = config('services.paypal.client_id') && config('services.paypal.client_secret') ? true : false;
        $authorizeNetConfigured = config('services.authorizenet.login_id') && config('services.authorizenet.transaction_key') ? true : false;
        
        $configuredGateways = ($stripeConfigured ? 1 : 0) + ($paypalConfigured ? 1 : 0) + ($authorizeNetConfigured ? 1 : 0);
        $realPaymentRate = $totalPayments > 0 ? ($realPayments / $totalPayments) * 100 : 0;
        
        return [
            'status' => $configuredGateways >= 2 && $realPaymentRate > 0 ? 'PASS' : ($configuredGateways >= 1 ? 'WARN' : 'FAIL'),
            'score' => $configuredGateways * 33.33,
            'details' => [
                "Configured gateways: {$configuredGateways}/3",
                "Stripe: " . ($stripeConfigured ? 'Yes' : 'No'),
                "PayPal: " . ($paypalConfigured ? 'Yes' : 'No'),
                "Authorize.Net: " . ($authorizeNetConfigured ? 'Yes' : 'No'),
                "Total payments: {$totalPayments}",
                "Real payments: {$realPayments} (" . round($realPaymentRate, 1) . "%)"
            ],
            'recommendations' => $configuredGateways < 2 || $realPaymentRate === 0 ? [
                'Run: php artisan payment:test-gateways',
                'Configure Stripe test keys',
                'Configure PayPal sandbox credentials',
                'Test payment flows end-to-end',
                'Set up webhook endpoints'
            ] : []
        ];
    }
    
    private function checkSecurity()
    {
        $adminRoutes = collect(Route::getRoutes())->filter(function ($route) {
            return strpos($route->uri(), 'admin/') !== false;
        });
        
        $protectedRoutes = $adminRoutes->filter(function ($route) {
            return in_array('auth', $route->middleware()) || in_array('admin', $route->middleware());
        });
        
        $protectionRate = $adminRoutes->count() > 0 ? ($protectedRoutes->count() / $adminRoutes->count()) * 100 : 100;
        
        $envSecure = config('app.env') === 'production' && config('app.debug') === false;
        
        return [
            'status' => $protectionRate >= 95 && $envSecure ? 'PASS' : 'WARN',
            'score' => round($protectionRate, 1),
            'details' => [
                "Admin routes: {$adminRoutes->count()}",
                "Protected routes: {$protectedRoutes->count()}",
                "Protection rate: " . round($protectionRate, 1) . "%",
                "Environment: " . config('app.env'),
                "Debug mode: " . (config('app.debug') ? 'ON' : 'OFF')
            ],
            'recommendations' => $protectionRate < 95 || !$envSecure ? [
                'Add auth middleware to unprotected admin routes',
                'Set APP_ENV=production for production',
                'Set APP_DEBUG=false for production',
                'Review and update security headers',
                'Enable HTTPS/SSL certificates'
            ] : []
        ];
    }
    
    private function checkDatabase()
    {
        $userCount = User::count();
        $enrollmentCount = UserCourseEnrollment::count();
        $certificateCount = FloridaCertificate::count();
        $paymentCount = Payment::count();
        
        $orphanedEnrollments = UserCourseEnrollment::whereDoesntHave('user')->count();
        $orphanedCertificates = FloridaCertificate::whereDoesntHave('enrollment')->count();
        
        $dataIntegrity = ($orphanedEnrollments + $orphanedCertificates) === 0;
        
        return [
            'status' => $dataIntegrity && $userCount > 0 ? 'PASS' : 'WARN',
            'score' => $dataIntegrity ? 100 : 75,
            'details' => [
                "Users: {$userCount}",
                "Enrollments: {$enrollmentCount}",
                "Certificates: {$certificateCount}",
                "Payments: {$paymentCount}",
                "Orphaned enrollments: {$orphanedEnrollments}",
                "Orphaned certificates: {$orphanedCertificates}"
            ],
            'recommendations' => !$dataIntegrity ? [
                'Clean up orphaned records',
                'Add foreign key constraints',
                'Run database integrity checks',
                'Set up regular database maintenance'
            ] : []
        ];
    }
    
    private function checkPerformance()
    {
        $cacheEnabled = config('cache.default') !== 'array';
        $queueEnabled = config('queue.default') !== 'sync';
        
        return [
            'status' => $cacheEnabled && $queueEnabled ? 'PASS' : 'WARN',
            'score' => ($cacheEnabled ? 50 : 0) + ($queueEnabled ? 50 : 0),
            'details' => [
                "Cache driver: " . config('cache.default'),
                "Queue driver: " . config('queue.default'),
                "Cache enabled: " . ($cacheEnabled ? 'Yes' : 'No'),
                "Queue enabled: " . ($queueEnabled ? 'Yes' : 'No')
            ],
            'recommendations' => !$cacheEnabled || !$queueEnabled ? [
                'Configure Redis or Memcached for caching',
                'Set up database or Redis queue driver',
                'Optimize database queries',
                'Set up performance monitoring'
            ] : []
        ];
    }
    
    private function displayResults($checks)
    {
        $this->line('');
        $this->info('📊 PRODUCTION READINESS RESULTS');
        $this->line(str_repeat('=', 60));
        
        $totalScore = 0;
        $maxScore = 0;
        $passCount = 0;
        $warnCount = 0;
        $failCount = 0;
        
        foreach ($checks as $checkName => $result) {
            $status = $result['status'];
            $score = $result['score'];
            
            $statusIcon = match($status) {
                'PASS' => '✅',
                'WARN' => '⚠️',
                'FAIL' => '❌',
                default => '❓'
            };
            
            $this->line("{$statusIcon} {$checkName}: {$status} ({$score}%)");
            
            foreach ($result['details'] as $detail) {
                $this->line("   • {$detail}");
            }
            
            if (!empty($result['recommendations'])) {
                $this->line("   📋 Recommendations:");
                foreach ($result['recommendations'] as $recommendation) {
                    $this->line("     - {$recommendation}");
                }
            }
            
            $this->line('');
            
            $totalScore += $score;
            $maxScore += 100;
            
            match($status) {
                'PASS' => $passCount++,
                'WARN' => $warnCount++,
                'FAIL' => $failCount++,
            };
        }
        
        $overallScore = round(($totalScore / $maxScore) * 100, 1);
        
        $this->line(str_repeat('=', 60));
        $this->info('🎯 OVERALL PRODUCTION READINESS');
        $this->line(str_repeat('=', 60));
        
        $totalChecks = $passCount + $warnCount + $failCount;
        $this->table(['Metric', 'Value'], [
            ['Overall Score', "{$overallScore}%"],
            ['Checks Passed', "{$passCount}/{$totalChecks}"],
            ['Warnings', $warnCount],
            ['Failures', $failCount],
        ]);
        
        if ($overallScore >= 90) {
            $this->info('🎉 PRODUCTION READY!');
            $this->info('Your platform is ready for production deployment.');
        } elseif ($overallScore >= 70) {
            $this->warn('⚠️  MOSTLY READY - Address warnings before production');
            $this->info('Your platform is mostly ready but has some issues to resolve.');
        } else {
            $this->error('❌ NOT PRODUCTION READY');
            $this->error('Critical issues must be resolved before production deployment.');
        }
        
        $this->line('');
        $this->info('🚀 Next Steps:');
        $this->info('1. Address all FAIL status items');
        $this->info('2. Review and fix WARN status items');
        $this->info('3. Run individual fix commands as recommended');
        $this->info('4. Re-run this check after fixes');
        $this->info('5. Set up monitoring and alerts for production');
    }
}
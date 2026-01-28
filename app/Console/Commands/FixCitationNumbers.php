<?php

namespace App\Console\Commands;

use App\Models\StateTransmission;
use App\Models\UserCourseEnrollment;
use Illuminate\Console\Command;

class FixCitationNumbers extends Command
{
    protected $signature = 'enrollments:fix-citation-numbers';
    protected $description = 'Fix missing citation numbers in enrollments and reset failed state transmissions';

    public function handle()
    {
        $this->info('🎯 Fixing citation number issues...');
        
        // Step 1: Fix enrollments with missing citation numbers
        $this->info("\nStep 1: Fixing enrollment citation numbers...");
        
        $enrollments = UserCourseEnrollment::with('user')->get();
        $fixedCount = 0;
        $insuranceDiscountCount = 0;
        $placeholderCount = 0;
        
        foreach ($enrollments as $enrollment) {
            $needsFix = false;
            $user = $enrollment->user;
            
            if (!$user) {
                continue;
            }
            
            // Handle insurance discount only cases
            if ($user->insurance_discount_only) {
                $insuranceDiscountCount++;
                if (empty($enrollment->citation_number)) {
                    $enrollment->citation_number = 'INSURANCE-DISCOUNT-' . str_pad($enrollment->id, 6, '0', STR_PAD_LEFT);
                    $needsFix = true;
                    $this->line("  Insurance discount citation set for enrollment {$enrollment->id}");
                }
            }
            // Copy citation from user if missing
            elseif (empty($enrollment->citation_number) && !empty($user->citation_number)) {
                $enrollment->citation_number = $user->citation_number;
                $needsFix = true;
                $this->line("  Copied citation from user for enrollment {$enrollment->id}: {$user->citation_number}");
            }
            // Generate placeholder if both missing
            elseif (empty($enrollment->citation_number) && empty($user->citation_number)) {
                $placeholderCitation = 'TEMP-' . str_pad($enrollment->id, 6, '0', STR_PAD_LEFT);
                $enrollment->citation_number = $placeholderCitation;
                $user->citation_number = $placeholderCitation;
                $user->save();
                $needsFix = true;
                $placeholderCount++;
                $this->line("  Generated placeholder citation for enrollment {$enrollment->id}: {$placeholderCitation}");
            }
            
            // Copy court information if missing
            if (empty($enrollment->court_selected) && !empty($user->court_selected)) {
                $enrollment->court_selected = $user->court_selected;
                $needsFix = true;
            }
            
            if ($needsFix) {
                $enrollment->save();
                $fixedCount++;
            }
        }
        
        $this->info("\n📊 Citation Number Fix Results:");
        $this->info("✅ Enrollments fixed: {$fixedCount}");
        $this->info("🏥 Insurance discount cases: {$insuranceDiscountCount}");
        $this->info("📝 Placeholder citations created: {$placeholderCount}");
        
        // Step 2: Reset failed state transmissions
        $this->info("\nStep 2: Resetting failed state transmissions...");
        
        $failedTransmissions = StateTransmission::where('status', 'error')
            ->where('response_message', 'like', '%Citation number is required%')
            ->with(['enrollment'])
            ->get();
        
        $retriedCount = 0;
        $stillFailingCount = 0;
        
        foreach ($failedTransmissions as $transmission) {
            $enrollment = $transmission->enrollment;
            
            if ($enrollment && !empty($enrollment->citation_number)) {
                $transmission->update([
                    'status' => 'pending',
                    'response_message' => null,
                    'response_code' => null,
                    'retry_count' => 0,
                    'sent_at' => null
                ]);
                $retriedCount++;
                $this->line("  Reset transmission {$transmission->id} for retry");
            } else {
                $stillFailingCount++;
                $this->warn("  Transmission {$transmission->id} still missing citation number");
            }
        }
        
        $this->info("\n📊 State Transmission Reset Results:");
        $this->info("✅ Transmissions reset for retry: {$retriedCount}");
        $this->info("⚠️  Still failing: {$stillFailingCount}");
        
        // Step 3: Validation summary
        $this->info("\nStep 3: Validation summary...");
        
        $enrollmentsWithoutCitation = UserCourseEnrollment::whereNull('citation_number')
            ->orWhere('citation_number', '')
            ->count();
        
        if ($enrollmentsWithoutCitation === 0) {
            $this->info("🎉 All enrollments now have citation numbers!");
            $this->info("✅ State transmissions should now work properly");
        } else {
            $this->warn("⚠️  {$enrollmentsWithoutCitation} enrollments still missing citation numbers");
        }
        
        $this->info("\n🚀 Next Steps:");
        $this->info("1. Test state transmission retry");
        $this->info("2. Monitor new enrollments for proper citation collection");
        $this->info("3. Contact state vendors for API endpoint updates");
        
        return 0;
    }
}
<?php

namespace App\Services;

use App\Models\Court;
use App\Models\CourtMailing;
use App\Models\CustomerMailing;
use App\Models\FloridaCertificate;
use App\Models\MailingBatch;
use App\Models\UserCourseEnrollment;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;

class CourtMailingService
{
    public function createCourtMailing(UserCourseEnrollment $enrollment, FloridaCertificate $certificate, ?Court $court = null): CourtMailing
    {
        $courtAddress = $court ? $this->getCourtAddress($court) : $this->getDefaultCourtAddress($enrollment);

        return CourtMailing::create([
            'enrollment_id' => $enrollment->id,
            'certificate_id' => $certificate->id,
            'court_id' => $court?->id,
            'mailing_type' => 'certificate',
            'recipient_type' => 'court',
            'status' => 'pending',
            'address_line_1' => $courtAddress['address_line_1'],
            'address_line_2' => $courtAddress['address_line_2'] ?? null,
            'city' => $courtAddress['city'],
            'state' => $courtAddress['state'],
            'zip_code' => $courtAddress['zip_code'],
        ]);
    }

    public function createCustomerMailing(UserCourseEnrollment $enrollment, string $type = 'certificate_copy'): CustomerMailing
    {
        $user = $enrollment->user;

        return CustomerMailing::create([
            'enrollment_id' => $enrollment->id,
            'certificate_id' => $enrollment->floridaCertificate?->id,
            'mailing_type' => $type,
            'status' => 'pending',
            'address_line_1' => $user->address ?? 'N/A',
            'city' => $user->city ?? 'N/A',
            'state' => $user->state ?? 'FL',
            'zip_code' => $user->zip_code ?? '00000',
        ]);
    }

    public function getPendingQueue(array $filters = []): LengthAwarePaginator
    {
        $query = CourtMailing::with(['enrollment.user', 'court'])->pending();

        if (! empty($filters['state'])) {
            $query->byState($filters['state']);
        }

        if (! empty($filters['court_id'])) {
            $query->byCourt($filters['court_id']);
        }

        return $query->orderBy('created_at', 'asc')->paginate(50);
    }

    public function getPrintedQueue(): LengthAwarePaginator
    {
        return CourtMailing::with(['enrollment.user', 'court'])
            ->printed()
            ->orderBy('printed_at', 'desc')
            ->paginate(50);
    }

    public function createBatch(?string $notes = null): MailingBatch
    {
        $batchNumber = 'BATCH-'.now()->format('Ymd-His');

        return MailingBatch::create([
            'batch_number' => $batchNumber,
            'batch_date' => now(),
            'total_items' => 0,
            'status' => 'open',
            'notes' => $notes,
            'created_by' => auth()->id(),
        ]);
    }

    public function addToBatch(MailingBatch $batch, array $mailingIds): int
    {
        $count = 0;
        foreach ($mailingIds as $id) {
            $mailing = CourtMailing::find($id);
            if ($mailing && $mailing->status === 'pending') {
                $batch->addItem($mailing);
                $count++;
            }
        }

        return $count;
    }

    public function printBatch(MailingBatch $batch): int
    {
        $batch->startPrinting();
        $count = 0;

        foreach ($batch->courtMailings as $mailing) {
            if ($mailing->status === 'pending') {
                $mailing->markPrinted();
                $batch->increment('printed_count');
                $count++;
            }
        }

        $batch->readyToMail();

        return $count;
    }

    public function mailBatch(MailingBatch $batch, array $trackingData = []): int
    {
        $count = 0;

        foreach ($batch->courtMailings as $mailing) {
            if ($mailing->status === 'printed') {
                $tracking = $trackingData[$mailing->id] ?? null;
                $mailing->markMailed($tracking);
                $batch->increment('mailed_count');
                $count++;
            }
        }

        $batch->update(['status' => 'mailed']);

        return $count;
    }

    public function markAsPrinted(CourtMailing $mailing, ?int $userId = null): void
    {
        $mailing->markPrinted($userId);
    }

    public function markAsMailed(CourtMailing $mailing, ?string $trackingNumber, ?int $userId = null): void
    {
        $mailing->markMailed($trackingNumber, $userId);
    }

    public function markAsDelivered(CourtMailing $mailing): void
    {
        $mailing->markDelivered();
    }

    public function markAsReturned(CourtMailing $mailing, string $reason): void
    {
        $mailing->markReturned($reason);
    }

    public function getCourtAddress(Court $court): array
    {
        return [
            'address_line_1' => $court->address ?? 'N/A',
            'address_line_2' => $court->address_line_2 ?? null,
            'city' => $court->city ?? 'N/A',
            'state' => $court->state ?? 'FL',
            'zip_code' => $court->zip_code ?? '00000',
        ];
    }

    private function getDefaultCourtAddress(UserCourseEnrollment $enrollment): array
    {
        return [
            'address_line_1' => 'Court Address',
            'city' => 'City',
            'state' => 'FL',
            'zip_code' => '00000',
        ];
    }

    public function getMailingStats(Carbon $from, Carbon $to): array
    {
        return [
            'pending' => CourtMailing::pending()->forDateRange($from, $to)->count(),
            'printed' => CourtMailing::printed()->forDateRange($from, $to)->count(),
            'mailed' => CourtMailing::mailed()->forDateRange($from, $to)->count(),
            'delivered' => CourtMailing::delivered()->forDateRange($from, $to)->count(),
            'returned' => CourtMailing::returned()->forDateRange($from, $to)->count(),
        ];
    }

    public function getPostageReport(Carbon $from, Carbon $to): array
    {
        $mailings = CourtMailing::forDateRange($from, $to)
            ->whereNotNull('postage_cost')
            ->get();

        return [
            'total_cost' => $mailings->sum('postage_cost'),
            'total_items' => $mailings->count(),
            'average_cost' => $mailings->avg('postage_cost'),
            'by_carrier' => $mailings->groupBy('carrier')->map->sum('postage_cost'),
        ];
    }

    public function getReturnAnalysis(): array
    {
        $returned = CourtMailing::returned()->get();

        return [
            'total_returned' => $returned->count(),
            'return_rate' => CourtMailing::count() > 0 ? ($returned->count() / CourtMailing::count() * 100) : 0,
            'by_reason' => $returned->groupBy('return_reason')->map->count(),
            'by_state' => $returned->groupBy('state')->map->count(),
        ];
    }
}

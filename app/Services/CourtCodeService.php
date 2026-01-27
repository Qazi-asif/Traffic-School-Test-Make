<?php

namespace App\Services;

use App\Models\Court;
use App\Models\CourtCode;
use App\Models\CourtCodeMapping;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CourtCodeService
{
    public function createCode(Court $court, array $data): CourtCode
    {
        $data['court_id'] = $court->id;
        $data['created_by'] = auth()->id();

        $code = CourtCode::create($data);

        $code->logChange('created', null, $data);

        return $code;
    }

    public function updateCode(CourtCode $code, array $data, ?string $reason = null): CourtCode
    {
        $oldValues = $code->only(array_keys($data));

        $code->update($data);

        $code->logChange('updated', $oldValues, $data, $reason);

        return $code->fresh();
    }

    public function deactivateCode(CourtCode $code, string $reason): void
    {
        $oldValues = ['is_active' => $code->is_active];

        $code->update(['is_active' => false]);

        $code->logChange('deactivated', $oldValues, ['is_active' => false], $reason);
    }

    public function reactivateCode(CourtCode $code): void
    {
        $oldValues = ['is_active' => $code->is_active];

        $code->update(['is_active' => true]);

        $code->logChange('reactivated', $oldValues, ['is_active' => true]);
    }

    public function findByCode(string $codeValue, ?string $type = null): ?CourtCode
    {
        $query = CourtCode::where('code_value', $codeValue);

        if ($type) {
            $query->where('code_type', $type);
        }

        return $query->first();
    }

    public function findForCourt(Court $court, ?string $type = null): Collection
    {
        $query = CourtCode::where('court_id', $court->id);

        if ($type) {
            $query->where('code_type', $type);
        }

        return $query->get();
    }

    public function getActiveCodesForState(string $stateCode): Collection
    {
        return CourtCode::active()
            ->effective()
            ->forState($stateCode)
            ->with('court')
            ->get();
    }

    public function searchCodes(string $query, array $filters = []): LengthAwarePaginator
    {
        $builder = CourtCode::query()
            ->with(['court', 'createdBy']);

        if (! empty($query)) {
            $builder->where(function ($q) use ($query) {
                $q->where('code_value', 'like', "%{$query}%")
                    ->orWhere('code_name', 'like', "%{$query}%")
                    ->orWhereHas('court', function ($cq) use ($query) {
                        $cq->where('court', 'like', "%{$query}%")
                            ->orWhere('county', 'like', "%{$query}%");
                    });
            });
        }

        if (! empty($filters['state'])) {
            $builder->forState($filters['state']);
        }

        if (! empty($filters['type'])) {
            $builder->where('code_type', $filters['type']);
        }

        if (isset($filters['is_active'])) {
            $builder->where('is_active', $filters['is_active']);
        }

        if (! empty($filters['court_id'])) {
            $builder->where('court_id', $filters['court_id']);
        }

        return $builder->orderBy('created_at', 'desc')->paginate(50);
    }

    public function addMapping(CourtCode $code, string $system, string $externalCode, ?string $name = null): CourtCodeMapping
    {
        return $code->mappings()->create([
            'external_system' => $system,
            'external_code' => $externalCode,
            'external_name' => $name,
            'is_verified' => false,
        ]);
    }

    public function verifyMapping(CourtCodeMapping $mapping): void
    {
        $mapping->update([
            'is_verified' => true,
            'verified_at' => now(),
            'verified_by' => auth()->id(),
        ]);
    }

    public function translateCode(string $code, string $fromSystem, string $toSystem): ?string
    {
        $mapping = CourtCodeMapping::where('external_code', $code)
            ->where('external_system', $fromSystem)
            ->where('is_verified', true)
            ->first();

        if (! $mapping) {
            return null;
        }

        return $mapping->courtCode
            ->mappings()
            ->where('external_system', $toSystem)
            ->where('is_verified', true)
            ->first()
            ?->external_code;
    }

    public function validateCode(string $code, string $type): bool
    {
        $pattern = match ($type) {
            'tvcc' => '/^[A-Z]{2}\d{3,6}$/',
            'court_id' => '/^[A-Z0-9]{4,10}$/',
            'location_code' => '/^\d{3,5}$/',
            'branch_code' => '/^[A-Z0-9]{2,8}$/',
            'state_code' => '/^[A-Z]{2}$/',
            default => '/^[A-Z0-9]{1,50}$/',
        };

        return preg_match($pattern, $code) === 1;
    }

    public function checkForDuplicates(string $code, string $type, ?int $excludeId = null): bool
    {
        $query = CourtCode::where('code_value', $code)
            ->where('code_type', $type);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    public function importFromCsv(string $filePath, string $stateCode): array
    {
        $stats = ['imported' => 0, 'skipped' => 0, 'errors' => []];

        if (! file_exists($filePath)) {
            $stats['errors'][] = 'File not found';

            return $stats;
        }

        $file = fopen($filePath, 'r');
        $headers = fgetcsv($file);

        while (($row = fgetcsv($file)) !== false) {
            try {
                $data = array_combine($headers, $row);

                $court = Court::where('state', $stateCode)
                    ->where('court', $data['court_name'] ?? '')
                    ->first();

                if (! $court) {
                    $stats['skipped']++;

                    continue;
                }

                if ($this->checkForDuplicates($data['code_value'], $data['code_type'])) {
                    $stats['skipped']++;

                    continue;
                }

                CourtCode::create([
                    'court_id' => $court->id,
                    'code_type' => $data['code_type'],
                    'code_value' => $data['code_value'],
                    'code_name' => $data['code_name'] ?? null,
                    'is_active' => $data['is_active'] ?? true,
                    'effective_date' => $data['effective_date'] ?? null,
                    'expiration_date' => $data['expiration_date'] ?? null,
                    'notes' => $data['notes'] ?? null,
                    'created_by' => auth()->id(),
                ]);

                $stats['imported']++;
            } catch (\Exception $e) {
                $stats['errors'][] = $e->getMessage();
            }
        }

        fclose($file);

        return $stats;
    }

    public function exportToCsv(array $filters = []): string
    {
        $codes = CourtCode::with('court')
            ->when(! empty($filters['state']), fn ($q) => $q->forState($filters['state']))
            ->when(! empty($filters['type']), fn ($q) => $q->where('code_type', $filters['type']))
            ->when(isset($filters['is_active']), fn ($q) => $q->where('is_active', $filters['is_active']))
            ->get();

        $filename = storage_path('app/exports/court_codes_'.now()->format('Y-m-d_His').'.csv');

        if (! is_dir(dirname($filename))) {
            mkdir(dirname($filename), 0755, true);
        }

        $file = fopen($filename, 'w');

        fputcsv($file, ['Code Value', 'Code Type', 'Code Name', 'Court', 'State', 'County', 'Active', 'Effective Date', 'Expiration Date', 'Notes']);

        foreach ($codes as $code) {
            fputcsv($file, [
                $code->code_value,
                $code->code_type,
                $code->code_name,
                $code->court?->court,
                $code->court?->state,
                $code->court?->county,
                $code->is_active ? 'Yes' : 'No',
                $code->effective_date?->format('Y-m-d'),
                $code->expiration_date?->format('Y-m-d'),
                $code->notes,
            ]);
        }

        fclose($file);

        return $filename;
    }

    public function exportForState(string $stateCode): string
    {
        return $this->exportToCsv(['state' => $stateCode]);
    }

    public function syncWithStateSystem(string $stateCode, string $system): array
    {
        // Placeholder for state system integration
        return ['synced' => 0, 'errors' => []];
    }

    public function getCodeStatistics(): array
    {
        return [
            'total' => CourtCode::count(),
            'active' => CourtCode::active()->count(),
            'inactive' => CourtCode::where('is_active', false)->count(),
            'by_type' => CourtCode::select('code_type', DB::raw('count(*) as count'))
                ->groupBy('code_type')
                ->pluck('count', 'code_type')
                ->toArray(),
            'by_state' => CourtCode::join('courts', 'court_codes.court_id', '=', 'courts.id')
                ->select('courts.state', DB::raw('count(*) as count'))
                ->groupBy('courts.state')
                ->pluck('count', 'state')
                ->toArray(),
            'expiring_soon' => $this->getExpiringCodes(30)->count(),
            'unmapped' => $this->getUnmappedCodes()->count(),
        ];
    }

    public function getExpiringCodes(int $daysAhead = 30): Collection
    {
        return CourtCode::active()
            ->whereNotNull('expiration_date')
            ->whereBetween('expiration_date', [now(), now()->addDays($daysAhead)])
            ->with('court')
            ->orderBy('expiration_date')
            ->get();
    }

    public function getUnmappedCodes(): Collection
    {
        return CourtCode::active()
            ->doesntHave('mappings')
            ->with('court')
            ->get();
    }
}

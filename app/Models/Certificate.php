<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Certificate extends Model
{
    protected $fillable = [
        'enrollment_id',
        'user_id',
        'certificate_number',
        'certificate_type',
        'state_code',
        'student_name',
        'course_title',
        'course_description',
        'completion_date',
        'final_exam_score',
        'passing_score_required',
        'driver_license_number',
        'citation_number',
        'citation_county',
        'court_name',
        'traffic_school_due_date',
        'student_address',
        'student_date_of_birth',
        'student_phone',
        'verification_hash',
        'pdf_path',
        'template_used',
        'is_sent_to_student',
        'sent_at',
        'is_sent_to_state',
        'state_submission_id',
        'state_submission_status',
        'state_submission_date',
        'generated_at',
        'status',
        'metadata',
    ];

    protected $casts = [
        'completion_date' => 'date',
        'traffic_school_due_date' => 'date',
        'student_date_of_birth' => 'date',
        'final_exam_score' => 'decimal:2',
        'passing_score_required' => 'integer',
        'is_sent_to_student' => 'boolean',
        'sent_at' => 'datetime',
        'is_sent_to_state' => 'boolean',
        'state_submission_date' => 'datetime',
        'generated_at' => 'datetime',
        'metadata' => 'array',
    ];

    // Certificate types
    const TYPE_FLORIDA_BDI = 'florida_bdi';
    const TYPE_FLORIDA_ADI = 'florida_adi';
    const TYPE_MISSOURI_DD = 'missouri_defensive_driving';
    const TYPE_TEXAS_DD = 'texas_defensive_driving';
    const TYPE_DELAWARE_DD = 'delaware_defensive_driving';
    const TYPE_GENERIC = 'generic';

    // Certificate statuses
    const STATUS_GENERATED = 'generated';
    const STATUS_SENT = 'sent';
    const STATUS_VERIFIED = 'verified';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_FAILED = 'failed';

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(UserCourseEnrollment::class, 'enrollment_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function verificationLogs(): HasMany
    {
        return $this->hasMany(CertificateVerificationLog::class);
    }

    public function stateTransmissions(): HasMany
    {
        return $this->hasMany(StateTransmission::class, 'certificate_id');
    }

    /**
     * Get the state-specific certificate template
     */
    public function getTemplateAttribute(): string
    {
        switch ($this->state_code) {
            case 'FL':
                return $this->certificate_type === self::TYPE_FLORIDA_ADI 
                    ? 'certificates.florida.adi' 
                    : 'certificates.florida.bdi';
            case 'MO':
                return 'certificates.missouri.defensive-driving';
            case 'TX':
                return 'certificates.texas.defensive-driving';
            case 'DE':
                return 'certificates.delaware.defensive-driving';
            default:
                return 'certificates.generic';
        }
    }

    /**
     * Get state-specific requirements
     */
    public function getStateRequirementsAttribute(): array
    {
        switch ($this->state_code) {
            case 'FL':
                return [
                    'dicds_submission_required' => true,
                    'minimum_score' => 80,
                    'certificate_format' => 'florida_standard',
                    'state_seal_required' => true,
                ];
            case 'MO':
                return [
                    'form_4444_required' => true,
                    'minimum_score' => 70,
                    'certificate_format' => 'missouri_standard',
                    'state_seal_required' => true,
                ];
            case 'TX':
                return [
                    'tdlr_submission_required' => true,
                    'minimum_score' => 75,
                    'certificate_format' => 'texas_standard',
                    'state_seal_required' => true,
                ];
            case 'DE':
                return [
                    'minimum_score' => 80,
                    'certificate_format' => 'delaware_standard',
                    'state_seal_required' => true,
                    'course_variations' => ['3hr', '6hr'],
                ];
            default:
                return [
                    'minimum_score' => 80,
                    'certificate_format' => 'generic',
                    'state_seal_required' => false,
                ];
        }
    }

    /**
     * Generate state-specific certificate number
     */
    public static function generateCertificateNumber(string $stateCode, ?int $enrollmentId = null): string
    {
        $year = date('Y');
        $stateCode = strtoupper($stateCode);
        
        // Get the last certificate for this state and year
        $lastCertificate = static::where('state_code', $stateCode)
            ->whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastCertificate ? 
            (int) substr($lastCertificate->certificate_number, -6) + 1 : 1;

        switch ($stateCode) {
            case 'FL':
                return "FL{$year}" . str_pad($sequence, 6, '0', STR_PAD_LEFT);
            case 'MO':
                return "MO{$year}" . str_pad($sequence, 6, '0', STR_PAD_LEFT);
            case 'TX':
                return "TX{$year}" . str_pad($sequence, 6, '0', STR_PAD_LEFT);
            case 'DE':
                return "DE{$year}" . str_pad($sequence, 6, '0', STR_PAD_LEFT);
            default:
                return "CERT{$year}" . str_pad($sequence, 6, '0', STR_PAD_LEFT);
        }
    }

    /**
     * Check if certificate meets state requirements
     */
    public function meetsStateRequirements(): bool
    {
        $requirements = $this->state_requirements;
        
        // Check minimum score
        if ($this->final_exam_score < $requirements['minimum_score']) {
            return false;
        }
        
        // Check required fields based on state
        switch ($this->state_code) {
            case 'FL':
                return !empty($this->driver_license_number) && !empty($this->citation_number);
            case 'MO':
                return !empty($this->student_address) && !empty($this->completion_date);
            case 'TX':
                return !empty($this->driver_license_number) && !empty($this->completion_date);
            case 'DE':
                return !empty($this->completion_date);
            default:
                return true;
        }
    }

    /**
     * Scope for certificates by state
     */
    public function scopeByState($query, string $stateCode)
    {
        return $query->where('state_code', strtoupper($stateCode));
    }

    /**
     * Scope for sent certificates
     */
    public function scopeSent($query)
    {
        return $query->where('is_sent_to_student', true);
    }

    /**
     * Scope for pending certificates
     */
    public function scopePending($query)
    {
        return $query->where('is_sent_to_student', false);
    }

    /**
     * Scope for state-submitted certificates
     */
    public function scopeSubmittedToState($query)
    {
        return $query->where('is_sent_to_state', true);
    }
}
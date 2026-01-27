<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MissouriForm4444 extends Model
{
    protected $fillable = [
        'user_id',
        'enrollment_id',
        'form_number',
        'completion_date',
        'submission_deadline',
        'submission_method',
        'court_signature_required',
        'submitted_to_dor',
        'dor_submission_date',
        'status',
        'pdf_path',
    ];

    protected $casts = [
        'completion_date' => 'datetime',
        'submission_deadline' => 'datetime',
        'dor_submission_date' => 'datetime',
        'court_signature_required' => 'boolean',
        'submitted_to_dor' => 'boolean',
    ];

    const STATUSES = [
        'pending_completion',
        'ready_for_submission',
        'awaiting_court_signature',
        'submitted_to_dor',
        'expired',
    ];

    const SUBMISSION_METHODS = [
        'point_reduction',
        'court_ordered',
        'insurance_discount',
        'voluntary',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function enrollment()
    {
        return $this->belongsTo(UserCourseEnrollment::class);
    }
}

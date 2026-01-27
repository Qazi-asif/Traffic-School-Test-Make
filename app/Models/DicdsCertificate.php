<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DicdsCertificate extends Model
{
    protected $table = 'dicds_certificates';

    protected $fillable = [
        'order_number', 'course_id', 'school_id', 'provider_id',
        'certificate_count', 'status', 'total_amount', 'student_name',
        'certificate_number', 'issued_at',
    ];

    protected $casts = ['issued_at' => 'datetime'];

    const STATUSES = ['Pending', 'Active', 'Issued'];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function provider()
    {
        return $this->belongsTo(DicdsUser::class, 'provider_id');
    }
}
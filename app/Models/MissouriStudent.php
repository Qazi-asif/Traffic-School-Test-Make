<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MissouriStudent extends Model
{
    protected $fillable = [
        'user_id',
        'missouri_license_number',
        'court_case_number',
        'county_id',
        'reason_attending',
        'completion_deadline',
        'certificate_mailed_date',
        'status',
    ];

    protected $casts = [
        'completion_deadline' => 'date',
        'certificate_mailed_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function county()
    {
        return $this->belongsTo(County::class);
    }
}

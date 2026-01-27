<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FloridaComplianceReport extends Model
{
    protected $fillable = [
        'report_type',
        'report_date',
        'data_range_start',
        'data_range_end',
        'generated_by',
        'file_path',
    ];

    protected $casts = [
        'report_date' => 'date',
        'data_range_start' => 'date',
        'data_range_end' => 'date',
    ];

    public function generator()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComplianceRule extends Model
{
    protected $fillable = [
        'state_config_id',
        'rule_type',
        'rule_name',
        'rule_value',
        'description',
        'is_required',
    ];

    protected $casts = [
        'is_required' => 'boolean',
    ];

    public function stateConfiguration()
    {
        return $this->belongsTo(StateConfiguration::class, 'state_config_id');
    }
}

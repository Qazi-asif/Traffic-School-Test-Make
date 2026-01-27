<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class StateConfiguration extends Model
{
    protected $fillable = [
        'state_code',
        'state_name',
        'compliance_rules',
        'submission_method',
        'api_endpoint',
        'api_credentials',
        'portal_url',
        'portal_credentials',
        'email_recipient',
        'certificate_template',
        'is_active',
    ];

    protected $casts = [
        'compliance_rules' => 'array',
        'is_active' => 'boolean',
    ];

    protected function apiCredentials(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? decrypt($value) : null,
            set: fn ($value) => $value ? encrypt($value) : null,
        );
    }

    protected function portalCredentials(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? decrypt($value) : null,
            set: fn ($value) => $value ? encrypt($value) : null,
        );
    }

    public function submissionQueue()
    {
        return $this->hasMany(StateSubmissionQueue::class, 'state_config_id');
    }

    public function complianceRules()
    {
        return $this->hasMany(ComplianceRule::class, 'state_config_id');
    }
}

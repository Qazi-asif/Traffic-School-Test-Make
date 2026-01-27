<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentGatewaySetting extends Model
{
    protected $fillable = [
        'gateway_id',
        'setting_key',
        'setting_value',
        'is_sensitive',
        'environment',
        'description',
    ];

    protected $casts = [
        'is_sensitive' => 'boolean',
    ];

    public function gateway(): BelongsTo
    {
        return $this->belongsTo(PaymentGateway::class, 'gateway_id');
    }

    public function setSettingValueAttribute($value)
    {
        $this->attributes['setting_value'] = $this->is_sensitive ? encrypt($value) : $value;
    }

    public function getSettingValueAttribute($value)
    {
        if (! $this->is_sensitive) {
            return $value;
        }

        try {
            return decrypt($value);
        } catch (\Exception $e) {
            return $value;
        }
    }

    public function getMaskedValue(): string
    {
        if (! $this->is_sensitive) {
            return $this->setting_value;
        }

        $value = $this->setting_value;
        $length = strlen($value);

        if ($length <= 4) {
            return str_repeat('•', $length);
        }

        return str_repeat('•', min($length - 4, 20)).substr($value, -4);
    }
}

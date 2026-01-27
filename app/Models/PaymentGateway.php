<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class PaymentGateway extends Model
{
    protected $fillable = [
        'name',
        'code',
        'is_active',
        'is_test_mode',
        'display_order',
        'display_name',
        'description',
        'icon',
        'supported_currencies',
        'min_amount',
        'max_amount',
        'transaction_fee_percent',
        'transaction_fee_fixed',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_test_mode' => 'boolean',
        'supported_currencies' => 'array',
        'min_amount' => 'decimal:2',
        'max_amount' => 'decimal:2',
        'transaction_fee_percent' => 'decimal:4',
        'transaction_fee_fixed' => 'decimal:2',
    ];

    public function settings(): HasMany
    {
        return $this->hasMany(PaymentGatewaySetting::class, 'gateway_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(PaymentGatewayLog::class, 'gateway_id');
    }

    public function webhooks(): HasMany
    {
        return $this->hasMany(PaymentGatewayWebhook::class, 'gateway_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order');
    }

    public function getSetting(string $key, ?string $environment = null): ?string
    {
        $environment = $environment ?? $this->getCurrentEnvironment();

        $setting = $this->settings()
            ->where('setting_key', $key)
            ->where('environment', $environment)
            ->first();

        return $setting?->setting_value;
    }

    public function setSetting(string $key, string $value, string $environment, bool $sensitive = false): void
    {
        $this->settings()->updateOrCreate(
            [
                'setting_key' => $key,
                'environment' => $environment,
            ],
            [
                'setting_value' => $value,
                'is_sensitive' => $sensitive,
            ]
        );
    }

    public function getAllSettings(?string $environment = null): Collection
    {
        $environment = $environment ?? $this->getCurrentEnvironment();

        return $this->settings()
            ->where('environment', $environment)
            ->get();
    }

    public function activate(): void
    {
        $this->update(['is_active' => true]);
        $this->logAction('activated');
    }

    public function deactivate(): void
    {
        $this->update(['is_active' => false]);
        $this->logAction('deactivated');
    }

    public function toggleTestMode(): void
    {
        $oldMode = $this->is_test_mode ? 'test' : 'production';
        $this->update(['is_test_mode' => ! $this->is_test_mode]);
        $newMode = $this->is_test_mode ? 'test' : 'production';

        $this->logAction('mode_changed', ['mode' => $oldMode], ['mode' => $newMode]);
    }

    public function getCurrentEnvironment(): string
    {
        return $this->is_test_mode ? 'test' : 'production';
    }

    public function calculateFee(float $amount): float
    {
        $percentFee = $amount * ($this->transaction_fee_percent / 100);
        $fixedFee = $this->transaction_fee_fixed ?? 0;

        return $percentFee + $fixedFee;
    }

    public function logAction(string $action, ?array $oldValues = null, ?array $newValues = null): void
    {
        $this->logs()->create([
            'action' => $action,
            'performed_by' => Auth::id(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
        ]);
    }
}

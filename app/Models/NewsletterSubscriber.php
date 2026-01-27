<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class NewsletterSubscriber extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'email',
        'first_name',
        'last_name',
        'user_id',
        'source',
        'state_code',
        'subscribed_at',
        'unsubscribed_at',
        'is_active',
        'ip_address',
        'confirmation_token',
        'confirmed_at',
        'unsubscribe_token',
        'bounce_count',
        'last_email_sent_at',
        'last_email_opened_at',
        'metadata',
    ];

    protected $casts = [
        'subscribed_at' => 'datetime',
        'unsubscribed_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'last_email_sent_at' => 'datetime',
        'last_email_opened_at' => 'datetime',
        'is_active' => 'boolean',
        'metadata' => 'array',
        'bounce_count' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($subscriber) {
            if (empty($subscriber->unsubscribe_token)) {
                $subscriber->unsubscribe_token = Str::random(64);
            }
            if (empty($subscriber->subscribed_at)) {
                $subscriber->subscribed_at = now();
            }
        });
    }

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->whereNull('unsubscribed_at');
    }

    public function scopeConfirmed(Builder $query): Builder
    {
        return $query->whereNotNull('confirmed_at');
    }

    public function scopeByState(Builder $query, string $state): Builder
    {
        return $query->where('state_code', $state);
    }

    public function scopeBySource(Builder $query, string $source): Builder
    {
        return $query->where('source', $source);
    }

    // Methods
    public function subscribe(): void
    {
        $this->update([
            'is_active' => true,
            'unsubscribed_at' => null,
            'subscribed_at' => now(),
        ]);
    }

    public function unsubscribe(): void
    {
        $this->update([
            'is_active' => false,
            'unsubscribed_at' => now(),
        ]);
    }

    public function confirm(): void
    {
        $this->update([
            'confirmed_at' => now(),
            'confirmation_token' => null,
        ]);
    }

    public function generateUnsubscribeToken(): string
    {
        $token = Str::random(64);
        $this->update(['unsubscribe_token' => $token]);

        return $token;
    }

    public function generateConfirmationToken(): string
    {
        $token = Str::random(64);
        $this->update(['confirmation_token' => $token]);

        return $token;
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}") ?: 'N/A';
    }

    public function incrementBounceCount(): void
    {
        $this->increment('bounce_count');

        // Deactivate if bounce count exceeds threshold
        if ($this->bounce_count >= config('newsletter.bounce_threshold', 3)) {
            $this->unsubscribe();
        }
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FloridaEmailTemplate extends Model
{
    protected $fillable = [
        'name', 'slug', 'subject', 'content', 'category', 'is_active', 'is_florida_required', 'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_florida_required' => 'boolean',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Court extends Model
{
    protected $fillable = ['state', 'county', 'court', 'primary_tvcc', 'secondary_codes'];

    protected $casts = [
        'secondary_codes' => 'array',
    ];

    public function courtCodes(): HasMany
    {
        return $this->hasMany(CourtCode::class);
    }

    public function getPrimaryTvccAttribute($value)
    {
        if ($value) {
            return $value;
        }

        return $this->courtCodes()
            ->where('code_type', 'tvcc')
            ->where('is_active', true)
            ->first()
            ?->code_value;
    }
}

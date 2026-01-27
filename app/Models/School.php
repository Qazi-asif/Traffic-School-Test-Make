<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    use HasFactory;

    protected $table = 'dicds_schools';

    protected $fillable = [
        'school_name', 'address', 'city', 'state', 'zip_code',
        'phone', 'fax', 'email', 'contact_person', 'provider_id',
        'disable_certificates', 'status',
    ];

    protected $casts = ['disable_certificates' => 'boolean'];

    public function provider()
    {
        return $this->belongsTo(DicdsUser::class, 'provider_id');
    }

    public function courses()
    {
        return $this->hasMany(SchoolCourse::class);
    }

    public function instructors()
    {
        return $this->hasMany(Instructor::class);
    }

    public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }
}

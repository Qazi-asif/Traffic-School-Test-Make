<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class DicdsUser extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'user_last_name', 'first_name', 'middle', 'suffix',
        'contact_email', 'retype_email', 'phone_number', 'phone_extension',
        'login_id', 'password', 'desired_application', 'desired_role',
        'user_group', 'status', 'approved_at',
    ];

    protected $hidden = ['password'];

    protected $casts = [
        'approved_at' => 'datetime',
        'password' => 'hashed',
    ];

    const ROLES = [
        'DRS_Provider_Admin' => 'Provider Administrator',
        'DRS_Provider_User' => 'Provider User',
        'DRS_School_Admin' => 'School Administrator',
    ];

    const STATUSES = ['Pending', 'Active', 'Denied', 'Revoked'];

    public function isProviderAdmin()
    {
        return $this->desired_role === 'DRS_Provider_Admin';
    }

    public function isProviderUser()
    {
        return $this->desired_role === 'DRS_Provider_User';
    }

    public function isSchoolAdmin()
    {
        return $this->desired_role === 'DRS_School_Admin';
    }
}

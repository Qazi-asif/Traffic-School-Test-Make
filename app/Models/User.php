<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\Passwords\CanResetPassword;

class User extends Authenticatable
{
    use HasFactory, Notifiable, CanResetPassword;

    protected $fillable = [
        'role_id',
        'first_name',
        'last_name',
        'email',
        'password',
        'phone',
        'address',
        'driver_license',
        'dicds_user_id',
        'dicds_password',
        'status',
        'mailing_address',
        'city',
        'state',
        'zip',
        'phone_1',
        'phone_2',
        'phone_3',
        'gender',
        'birth_month',
        'birth_day',
        'birth_year',
        'license_state',
        'license_class',
        'insurance_discount_only',
        'court_selected',
        'citation_number',
        'due_month',
        'due_day',
        'due_year',
        'security_q1',
        'security_q2',
        'security_q3',
        'security_q4',
        'security_q5',
        'security_q6',
        'security_q7',
        'security_q8',
        'security_q9',
        'security_q10',
        'agreement_name',
        'terms_agreement',
        'state_code',
        'name',
        'email_verified_at',
        'account_locked',
        'locked_at',
        'two_factor_enabled',
        'two_factor_code',
        'two_factor_expires_at',
        'two_factor_verified_at',
        'two_factor_attempts',
        'registration_completed_at',
    ];

    /**
     * Always load the role relationship
     */
    protected $with = ['role'];

    protected $hidden = [
        'password',
        'remember_token',
        'dicds_password',
        'two_factor_code',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_enabled' => 'boolean',
            'insurance_discount_only' => 'boolean',
            'two_factor_expires_at' => 'datetime',
            'two_factor_verified_at' => 'datetime',
            'registration_completed_at' => 'datetime',
            'locked_at' => 'datetime',
        ];
    }

    public function certificates()
    {
        return $this->hasManyThrough(
            FloridaCertificate::class,
            UserCourseEnrollment::class,
            'user_id',
            'enrollment_id',
            'id',
            'id'
        );
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Check if user is an admin (safely handles role relationship)
     */
    public function isAdmin()
    {
        if (!$this->role) {
            return false;
        }
        
        if (is_object($this->role)) {
            return in_array($this->role->slug ?? '', ['super-admin', 'admin']);
        }
        
        if (is_string($this->role)) {
            return in_array($this->role, ['super-admin', 'admin']);
        }
        
        return false;
    }

    /**
     * Get user role slug safely
     */
    public function getRoleSlug()
    {
        if (!$this->role) {
            return 'student';
        }
        
        if (is_object($this->role)) {
            return $this->role->slug ?? 'student';
        }
        
        if (is_string($this->role)) {
            return $this->role;
        }
        
        return 'student';
    }

    /**
     * Get user role name safely
     */
    public function getRoleName()
    {
        if (!$this->role) {
            return 'User';
        }
        
        if (is_object($this->role)) {
            return $this->role->name ?? 'User';
        }
        
        if (is_string($this->role)) {
            return ucfirst($this->role);
        }
        
        return 'User';
    }

    public function enrollments()
    {
        return $this->hasMany(UserCourseEnrollment::class);
    }

    public function createdCourses()
    {
        return $this->hasMany(Course::class, 'created_by');
    }

    public function scopeNotLocked($query)
    {
        return $query->where('account_locked', false);
    }

    // JWT methods (optional - only used if JWT package is properly configured)
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new \App\Notifications\ResetPasswordNotification($token));
    }
}

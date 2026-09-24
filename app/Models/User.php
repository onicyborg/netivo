<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasUuids, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'is_active' => 'boolean',
        ];
    }

    public function customer(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Customer::class);
    }

    public function verifiedPayments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Payment::class, 'verified_by');
    }

    public function reviewedUpgradeRequests(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ServiceUpgradeRequest::class, 'reviewed_by');
    }

    public function createdReports(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DailyReport::class, 'created_by');
    }

    public function reviewedReports(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DailyReport::class, 'reviewed_by');
    }

    public function notifications(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function systemLogs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SystemLog::class);
    }
}

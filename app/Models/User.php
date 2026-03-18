<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_manager',
        'days_allowed',
        'color',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_manager' => 'boolean',
    ];

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class, 'employee_id');
    }

    public function approvedLeave()
    {
        return $this->hasMany(LeaveRequest::class, 'approved_by_id');
    }

    public function usedDays(): int
    {
        return (int) $this->leaveRequests()->where('status', 'approved')->sum('days');
    }

    public function remainingDays(): int
    {
        return $this->days_allowed - $this->usedDays();
    }

    public function initials(): string
    {
        return collect(explode(' ', $this->name))
            ->map(fn($part) => strtoupper($part[0]))
            ->implode('');
    }
}

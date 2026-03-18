<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveBalance extends Model
{
    protected $fillable = [
        'user_id',
        'leave_type_id',
        'year',
        'allocated_days',
        'used_days',
        'pending_days',
    ];

    protected $casts = [
        'allocated_days' => 'decimal:1',
        'used_days' => 'decimal:1',
        'pending_days' => 'decimal:1',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function getRemainingDaysAttribute(): float
    {
        return $this->allocated_days - $this->used_days - $this->pending_days;
    }
}

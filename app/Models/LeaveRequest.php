<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'start_date',
        'end_date',
        'days',
        'is_half_day',
        'half_day_part',
        'reason',
        'status',
        'approved_by_id',
        'approved_at',
        'admin_override',
    ];

    protected $casts = [
        'start_date'     => 'date',
        'end_date'       => 'date',
        'approved_at'    => 'datetime',
        'admin_override' => 'boolean',
        'is_half_day'    => 'boolean',
        'days'           => 'float',
    ];

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by_id');
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }
}

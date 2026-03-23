<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    protected $fillable = [
        'name',
        'color',
        'counts_toward_allowance',
        'is_active',
    ];

    protected $casts = [
        'counts_toward_allowance' => 'boolean',
        'is_active'               => 'boolean',
    ];

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }
}

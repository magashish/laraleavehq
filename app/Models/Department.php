<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = [
        'name',
        'max_concurrent',
    ];

    protected $casts = [
        'max_concurrent' => 'integer',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'department_user');
    }

    /**
     * Count how many department members have approved leave overlapping the given date range,
     * optionally excluding a specific leave request (for edits).
     */
    public function concurrentAbsences(string $startDate, string $endDate, ?int $excludeLeaveId = null): int
    {
        return $this->users()
            ->whereHas('leaveRequests', function ($q) use ($startDate, $endDate, $excludeLeaveId) {
                $q->where('status', 'approved')
                  ->where('start_date', '<=', $endDate)
                  ->where('end_date', '>=', $startDate);
                if ($excludeLeaveId) {
                    $q->where('id', '!=', $excludeLeaveId);
                }
            })
            ->count();
    }
}

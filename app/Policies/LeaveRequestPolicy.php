<?php

namespace App\Policies;

use App\Models\LeaveRequest;
use App\Models\User;

class LeaveRequestPolicy
{
    public function view(User $user, LeaveRequest $leaveRequest): bool
    {
        if ($user->isAdmin()) return true;
        if ($user->id === $leaveRequest->user_id) return true;
        if ($user->isManager() && $leaveRequest->user->manager_id === $user->id) return true;
        return false;
    }

    public function cancel(User $user, LeaveRequest $leaveRequest): bool
    {
        return $user->id === $leaveRequest->user_id && $leaveRequest->isPending();
    }

    public function review(User $user, LeaveRequest $leaveRequest): bool
    {
        if (!$leaveRequest->isPending()) return false;
        if ($user->isAdmin()) return true;
        if ($user->isManager() && $leaveRequest->user->manager_id === $user->id) return true;
        return false;
    }
}

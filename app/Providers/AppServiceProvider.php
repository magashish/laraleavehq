<?php

namespace App\Providers;

use App\Models\LeaveRequest;
use App\Policies\LeaveRequestPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(LeaveRequest::class, LeaveRequestPolicy::class);
    }
}

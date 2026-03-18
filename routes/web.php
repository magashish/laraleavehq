<?php

use App\Http\Controllers\Admin\LeaveTypeController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Leave Requests
    Route::resource('leave-requests', LeaveRequestController::class)->only(['index', 'create', 'store', 'show']);
    Route::patch('leave-requests/{leaveRequest}/cancel', [LeaveRequestController::class, 'cancel'])->name('leave-requests.cancel');

    // Approvals (manager/admin)
    Route::middleware('manager')->group(function () {
        Route::get('/approvals', [ApprovalController::class, 'index'])->name('approvals.index');
        Route::patch('/approvals/{leaveRequest}/approve', [ApprovalController::class, 'approve'])->name('approvals.approve');
        Route::patch('/approvals/{leaveRequest}/reject', [ApprovalController::class, 'reject'])->name('approvals.reject');
    });

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Admin
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('leave-types', LeaveTypeController::class);
        Route::resource('users', UserController::class)->except(['show', 'destroy']);
    });
});

require __DIR__.'/auth.php';

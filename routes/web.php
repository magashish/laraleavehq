<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TeamController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('dashboard'));

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Leave management
    Route::get('/leave', [LeaveController::class, 'index'])->name('leave.index');
    Route::post('/leave', [LeaveController::class, 'store'])->name('leave.store');
    Route::patch('/leave/{leave}', [LeaveController::class, 'update'])->name('leave.update');
    Route::delete('/leave/{leave}', [LeaveController::class, 'destroy'])->name('leave.destroy');

    // Team (managers only)
    Route::get('/team', [TeamController::class, 'index'])->name('team.index');

    // Settings (managers only)
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings/employees', [SettingsController::class, 'addEmployee'])->name('settings.employees.add');
    Route::delete('/settings/employees/{user}', [SettingsController::class, 'removeEmployee'])->name('settings.employees.remove');
    Route::patch('/settings/employees/{user}/days', [SettingsController::class, 'updateDays'])->name('settings.employees.days');
    Route::post('/settings/bank-holidays', [SettingsController::class, 'addBankHoliday'])->name('settings.bank-holidays.add');
    Route::delete('/settings/bank-holidays/{bankHoliday}', [SettingsController::class, 'removeBankHoliday'])->name('settings.bank-holidays.remove');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

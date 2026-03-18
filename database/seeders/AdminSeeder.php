<?php

namespace Database\Seeders;

use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@leavehq.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'department' => 'HR',
                'position' => 'HR Manager',
                'hire_date' => now()->subYears(3),
            ]
        );

        $manager = User::firstOrCreate(
            ['email' => 'manager@leavehq.com'],
            [
                'name' => 'John Manager',
                'password' => Hash::make('password'),
                'role' => 'manager',
                'department' => 'Engineering',
                'position' => 'Engineering Manager',
                'hire_date' => now()->subYears(2),
                'manager_id' => $admin->id,
            ]
        );

        $employee = User::firstOrCreate(
            ['email' => 'employee@leavehq.com'],
            [
                'name' => 'Jane Employee',
                'password' => Hash::make('password'),
                'role' => 'employee',
                'department' => 'Engineering',
                'position' => 'Software Engineer',
                'hire_date' => now()->subYear(),
                'manager_id' => $manager->id,
            ]
        );

        $leaveTypes = LeaveType::all();
        $users = [$admin, $manager, $employee];

        foreach ($users as $user) {
            foreach ($leaveTypes as $leaveType) {
                LeaveBalance::firstOrCreate(
                    ['user_id' => $user->id, 'leave_type_id' => $leaveType->id, 'year' => now()->year],
                    ['allocated_days' => $leaveType->days_per_year, 'used_days' => 0, 'pending_days' => 0]
                );
            }
        }
    }
}

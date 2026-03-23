<?php

namespace Database\Seeders;

use App\Models\BankHoliday;
use App\Models\Department;
use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Users ─────────────────────────────────────────────────────────────

        $admin = User::firstOrCreate(['email' => 'admin@company.com'], [
            'name'         => 'Admin Manager',
            'password'     => Hash::make('admin123'),
            'role'         => 'Director',
            'role_type'    => 'admin',
            'is_manager'   => true,
            'days_allowed' => 28,
            'color'        => '#4ade80',
        ]);

        $employees = [
            ['name' => 'Sarah Chen',   'email' => 'sarah@company.com',  'role' => 'Designer',        'role_type' => 'employee', 'color' => '#e879f9', 'days_allowed' => 25],
            ['name' => 'James Okafor', 'email' => 'james@company.com',  'role' => 'Developer',       'role_type' => 'employee', 'color' => '#38bdf8', 'days_allowed' => 25],
            ['name' => 'Priya Sharma', 'email' => 'priya@company.com',  'role' => 'Product Manager', 'role_type' => 'manager',  'color' => '#fb923c', 'days_allowed' => 25],
        ];

        $createdUsers = [$admin];
        foreach ($employees as $emp) {
            $createdUsers[] = User::firstOrCreate(['email' => $emp['email']], array_merge($emp, [
                'password'   => Hash::make('password123'),
                'is_manager' => $emp['role_type'] === 'manager',
            ]));
        }

        // ── Leave Types ───────────────────────────────────────────────────────

        $leaveTypes = [
            ['name' => 'Annual Leave',         'color' => '#22c55e', 'counts_toward_allowance' => true],
            ['name' => 'Bank Holiday',          'color' => '#a855f7', 'counts_toward_allowance' => false],
            ['name' => 'Absent',                'color' => '#ef4444', 'counts_toward_allowance' => true],
            ['name' => 'Compassionate Leave',   'color' => '#ec4899', 'counts_toward_allowance' => false],
            ['name' => 'Paid Sick Leave',       'color' => '#f97316', 'counts_toward_allowance' => false],
            ['name' => 'Unpaid Sick Leave',     'color' => '#f59e0b', 'counts_toward_allowance' => false],
            ['name' => 'Maternity Leave',       'color' => '#d946ef', 'counts_toward_allowance' => false],
            ['name' => 'Paternity Leave',       'color' => '#8b5cf6', 'counts_toward_allowance' => false],
            ['name' => 'Unpaid Leave',          'color' => '#64748b', 'counts_toward_allowance' => false],
            ['name' => 'Working From Home',     'color' => '#0ea5e9', 'counts_toward_allowance' => false],
            ['name' => 'Medical Appointment',   'color' => '#06b6d4', 'counts_toward_allowance' => false],
        ];

        foreach ($leaveTypes as $lt) {
            LeaveType::firstOrCreate(['name' => $lt['name']], array_merge($lt, ['is_active' => true]));
        }

        // ── Departments ───────────────────────────────────────────────────────

        $deptData = [
            ['name' => 'Management',  'max_concurrent' => 1],
            ['name' => 'Technical',   'max_concurrent' => 1],
            ['name' => 'Operations',  'max_concurrent' => 2],
            ['name' => 'Development', 'max_concurrent' => 1],
        ];

        foreach ($deptData as $dept) {
            Department::firstOrCreate(['name' => $dept['name']], ['max_concurrent' => $dept['max_concurrent']]);
        }

        // ── Bank Holidays (UK 2025–2026) ──────────────────────────────────────

        $holidays = [
            ['date' => '2025-01-01', 'name' => "New Year's Day"],
            ['date' => '2025-04-18', 'name' => 'Good Friday'],
            ['date' => '2025-04-21', 'name' => 'Easter Monday'],
            ['date' => '2025-05-05', 'name' => 'Early May bank holiday'],
            ['date' => '2025-05-26', 'name' => 'Spring bank holiday'],
            ['date' => '2025-08-25', 'name' => 'Summer bank holiday'],
            ['date' => '2025-12-25', 'name' => 'Christmas Day'],
            ['date' => '2025-12-26', 'name' => 'Boxing Day'],
            ['date' => '2026-01-01', 'name' => "New Year's Day"],
            ['date' => '2026-04-03', 'name' => 'Good Friday'],
            ['date' => '2026-04-06', 'name' => 'Easter Monday'],
            ['date' => '2026-05-04', 'name' => 'Early May bank holiday'],
            ['date' => '2026-05-25', 'name' => 'Spring bank holiday'],
            ['date' => '2026-08-31', 'name' => 'Summer bank holiday'],
            ['date' => '2026-12-25', 'name' => 'Christmas Day'],
            ['date' => '2026-12-28', 'name' => 'Boxing Day (substitute)'],
        ];

        foreach ($holidays as $h) {
            \DB::table('bank_holidays')->insertOrIgnore([
                'date'       => $h['date'],
                'name'       => $h['name'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}

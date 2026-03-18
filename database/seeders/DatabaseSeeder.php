<?php

namespace Database\Seeders;

use App\Models\BankHoliday;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin/Manager
        User::firstOrCreate(['email' => 'admin@company.com'], [
            'name'         => 'Admin Manager',
            'password'     => Hash::make('admin123'),
            'role'         => 'Manager',
            'is_manager'   => true,
            'days_allowed' => 28,
            'color'        => '#4ade80',
        ]);

        // Sample employees
        $employees = [
            ['name' => 'Sarah Chen',    'email' => 'sarah@company.com',  'role' => 'Designer',         'color' => '#e879f9'],
            ['name' => 'James Okafor',  'email' => 'james@company.com',  'role' => 'Developer',        'color' => '#38bdf8'],
            ['name' => 'Priya Sharma',  'email' => 'priya@company.com',  'role' => 'Product Manager',  'color' => '#fb923c'],
        ];

        foreach ($employees as $emp) {
            User::firstOrCreate(['email' => $emp['email']], array_merge($emp, [
                'password'     => Hash::make('password123'),
                'is_manager'   => false,
                'days_allowed' => 25,
            ]));
        }

        // UK Bank Holidays 2025–2026
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
            BankHoliday::firstOrCreate(['date' => $h['date']], ['name' => $h['name']]);
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\LeaveType;
use Illuminate\Database\Seeder;

class LeaveTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Annual Leave', 'color' => '#3B82F6', 'days_per_year' => 20, 'description' => 'Yearly vacation leave'],
            ['name' => 'Sick Leave', 'color' => '#EF4444', 'days_per_year' => 10, 'description' => 'Medical or illness leave'],
            ['name' => 'Personal Leave', 'color' => '#8B5CF6', 'days_per_year' => 5, 'description' => 'Personal matters leave'],
            ['name' => 'Maternity Leave', 'color' => '#EC4899', 'days_per_year' => 90, 'description' => 'Maternity leave for new mothers'],
            ['name' => 'Paternity Leave', 'color' => '#06B6D4', 'days_per_year' => 10, 'description' => 'Paternity leave for new fathers'],
            ['name' => 'Unpaid Leave', 'color' => '#6B7280', 'days_per_year' => 30, 'description' => 'Leave without pay', 'requires_approval' => true],
        ];

        foreach ($types as $type) {
            LeaveType::firstOrCreate(['name' => $type['name']], $type);
        }
    }
}

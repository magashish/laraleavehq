<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // WFH is now managed through leave requests only.
        // Employees whose work_location was set to 'wfh' via the old
        // toggle buttons are office-based; reset them to 'office'.
        DB::table('users')
            ->where('work_location', 'wfh')
            ->update(['work_location' => 'office']);
    }

    public function down(): void
    {
        // No rollback — we cannot know which users were originally 'wfh'
    }
};

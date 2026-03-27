<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Promote all existing manager-role users to admin.
     * After running, demote anyone who should stay as Manager in Settings.
     */
    public function up(): void
    {
        DB::table('users')
            ->where('role_type', 'manager')
            ->update(['role_type' => 'admin']);
    }

    public function down(): void
    {
        DB::table('users')
            ->where('role_type', 'admin')
            ->update(['role_type' => 'manager']);
    }
};

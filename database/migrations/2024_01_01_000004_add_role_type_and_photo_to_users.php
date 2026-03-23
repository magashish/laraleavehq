<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // role_type replaces is_manager boolean with a 5-tier system
            $table->string('role_type')->default('employee')->after('is_manager');
            // profile photo path
            $table->string('profile_photo')->nullable()->after('color');
        });

        // Migrate existing is_manager values to role_type
        DB::table('users')->where('is_manager', true)->update(['role_type' => 'manager']);
        DB::table('users')->where('is_manager', false)->update(['role_type' => 'employee']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role_type', 'profile_photo']);
        });
    }
};

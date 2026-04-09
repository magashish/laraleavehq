<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->boolean('is_short_leave')->default(false)->after('is_half_day');
            $table->time('short_leave_from')->nullable()->after('is_short_leave');
            $table->time('short_leave_to')->nullable()->after('short_leave_from');
        });
    }

    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropColumn(['is_short_leave', 'short_leave_from', 'short_leave_to']);
        });
    }
};

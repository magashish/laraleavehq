<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->decimal('days', 4, 1)->change();
            $table->boolean('is_half_day')->default(false)->after('days');
            $table->string('half_day_part')->nullable()->after('is_half_day'); // morning or afternoon
        });
    }

    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->integer('days')->change();
            $table->dropColumn(['is_half_day', 'half_day_part']);
        });
    }
};

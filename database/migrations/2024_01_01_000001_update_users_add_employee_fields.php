<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('Employee')->after('email'); // job title
            $table->boolean('is_manager')->default(false)->after('role');
            $table->integer('days_allowed')->default(25)->after('is_manager');
            $table->string('color', 10)->default('#38bdf8')->after('days_allowed');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'is_manager', 'days_allowed', 'color']);
        });
    }
};

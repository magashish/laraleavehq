<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'manager', 'employee'])->default('employee')->after('email');
            $table->string('department')->nullable()->after('role');
            $table->string('position')->nullable()->after('department');
            $table->date('hire_date')->nullable()->after('position');
            $table->foreignId('manager_id')->nullable()->after('hire_date')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['manager_id']);
            $table->dropColumn(['role', 'department', 'position', 'hire_date', 'manager_id']);
        });
    }
};

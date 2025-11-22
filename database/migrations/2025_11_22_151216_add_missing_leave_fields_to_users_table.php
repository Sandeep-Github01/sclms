<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Only add if column doesn't exist
            if (!Schema::hasColumn('users', 'penalty_points')) {
                $table->integer('penalty_points')->default(0)->after('remember_token');
            }

            if (!Schema::hasColumn('users', 'leave_blocked_until')) {
                $table->timestamp('leave_blocked_until')->nullable()->after('penalty_points');
            }

            if (!Schema::hasColumn('users', 'dept_name')) {
                $table->string('dept_name')->nullable()->after('name');
            }

            if (!Schema::hasColumn('users', 'semester')) {
                $table->string('semester')->nullable()->after('dept_name');
            }

            if (!Schema::hasColumn('users', 'role')) {
                $table->enum('role', ['student', 'teacher'])->default('student')->after('semester');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'penalty_points',
                'leave_blocked_until',
                'dept_name',
                'semester',
                'role'
            ]);
        });
    }
};

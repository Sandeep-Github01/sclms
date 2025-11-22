<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('penalty_points')->default(0)->after('profile_status');
            $table->timestamp('leave_blocked_until')->nullable()->after('penalty_points');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['penalty_points', 'leave_blocked_until']);
        });
    }
};

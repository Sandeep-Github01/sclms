<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('image')->nullable()->after('name');
            $table->date('dob')->nullable()->after('email');
            $table->text('address')->nullable()->after('dob');
            $table->string('phone')->nullable()->after('dept_name');
            $table->string('batch')->nullable()->after('phone');
            $table->string('semester')->nullable()->after('batch');
            $table->string('gender')->nullable()->after('address');
            $table->enum('status', ['Active', 'Inactive'])->default('Active')->after('gender');
            $table->timestamp('last_login_at')->nullable()->after('password');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'image',
                'dob',
                'address',
                'phone',
                'batch',
                'semester',
                'gender',
                'status',
                'last_login_at'
            ]);
        });
    }
};

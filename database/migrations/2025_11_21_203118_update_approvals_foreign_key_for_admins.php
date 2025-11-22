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
        Schema::table('approvals', function (Blueprint $table) {
            // Drop the existing foreign key
            $table->dropForeign(['approved_by']);

            // Add a new foreign key pointing to admins table
            $table->foreign('approved_by')
                ->references('id')
                ->on('admins')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('approvals', function (Blueprint $table) {
            // Drop the foreign key pointing to admins
            $table->dropForeign(['approved_by']);

            // Restore foreign key to users table
            $table->foreign('approved_by')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }
};

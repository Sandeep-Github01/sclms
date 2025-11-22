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
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->index(['status', 'review_type', 'document_deadline']);
            $table->index(['user_id', 'status', 'start_date', 'end_date']);
            $table->index(['department_id', 'role', 'semester', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};

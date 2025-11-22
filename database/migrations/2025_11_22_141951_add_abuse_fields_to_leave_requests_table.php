<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            // abuse flags
            $table->tinyInteger('abuse')->default(0)->after('notes')->comment('0 = not abuse, 1 = abused');
            $table->text('abuse_reason')->nullable()->after('abuse');
            $table->enum('flagged_by', ['system', 'admin'])->nullable()->after('abuse_reason');
            $table->unsignedBigInteger('flagged_by_id')->nullable()->after('flagged_by'); // admin id when admin flagged

            // fraud info and document tracking
            $table->json('fraud_flags')->nullable()->after('flagged_by_id');
            $table->enum('document_status', ['pending', 'submitted', 'verified', 'missing', 'rejected'])->default('pending')->after('fraud_flags');
            $table->timestamp('document_deadline')->nullable()->after('document_status');
        });
    }

    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropColumn([
                'abuse',
                'abuse_reason',
                'flagged_by',
                'flagged_by_id',
                'fraud_flags',
                'document_status',
                'document_deadline'
            ]);
        });
    }
};

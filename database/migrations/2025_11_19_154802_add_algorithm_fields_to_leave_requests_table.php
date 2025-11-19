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
            $table->integer('fraud_score')->default(0)->after('final_score');
            $table->integer('risk_score')->default(0)->after('fraud_score');
            $table->float('probability')->default(0)->after('risk_score');
            $table->longText('notes')->nullable()->after('status_note');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropColumn(['fraud_score', 'risk_score', 'probability', 'notes']);
        });
    }
};

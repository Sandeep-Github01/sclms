<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('leave_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('leave_request_id')->unique()->constrained()->cascadeOnDelete();
            $table->json('features');
            $table->float('probability');
            $table->integer('score');
            $table->json('steps');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_evaluations');
    }
};

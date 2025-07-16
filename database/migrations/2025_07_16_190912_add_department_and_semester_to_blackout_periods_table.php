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
        Schema::table('blackout_periods', function (Blueprint $table) {
            $table->unsignedBigInteger('department_id')->nullable();
            $table->string('semester')->nullable();
        });
    }

    public function down()
    {
        Schema::table('blackout_periods', function (Blueprint $table) {
            $table->dropColumn(['department_id', 'semester']);
        });
    }
};

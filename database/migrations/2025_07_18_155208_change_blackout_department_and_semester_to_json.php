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
            $table->dropColumn(['department_id', 'semester']);
        });

        Schema::table('blackout_periods', function (Blueprint $table) {
            $table->json('department_id')->nullable()->after('id');
            $table->json('semester')->nullable()->after('department_id');
        });
    }


    public function down()
    {
        Schema::table('blackout_periods', function (Blueprint $table) {
            $table->dropColumn(['department_id', 'semester']);
        });

        Schema::table('blackout_periods', function (Blueprint $table) {
            $table->unsignedBigInteger('department_id')->nullable(); // or string/int if original
            $table->string('semester')->nullable();
        });
    }
};

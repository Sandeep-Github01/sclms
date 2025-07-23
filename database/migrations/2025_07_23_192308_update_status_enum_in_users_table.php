<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateStatusEnumInUsersTable extends Migration
{
    public function up()
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN status ENUM('active', 'inactive') DEFAULT 'active'");
    }

    public function down()
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN status ENUM('Active', 'Inactive') DEFAULT 'Active'");
    }
}

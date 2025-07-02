<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenamePasswordResetTokensToPasswordResets extends Migration
{
    public function up()
    {
        Schema::rename('password_reset_tokens', 'password_resets');
    }

    public function down()
    {
        Schema::rename('password_resets', 'password_reset_tokens');
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('student_id', 32);
            $table->string('name_family');
            $table->string('name_family_yomi');
            $table->string('name_given');
            $table->string('name_given_yomi');
            $table->string('email')->unique();
            $table->string('tel');
            $table->boolean('is_staff')->default(false);
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('univemail_verified_at')->nullable();
            $table->string('password');
            $table->text('notes')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}

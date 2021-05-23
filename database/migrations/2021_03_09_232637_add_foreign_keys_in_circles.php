<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysInCircles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('circles', function (Blueprint $table) {
            $table->bigIncrements('id')->change();
        });

        Schema::table('answers', function (Blueprint $table) {
            $table->foreign('circle_id')
                ->references('id')->on('circles')
                ->onDelete('cascade');
        });

        Schema::table('booths', function (Blueprint $table) {
            $table->unsignedBigInteger('circle_id')->change();
            $table->foreign('circle_id')
                ->references('id')->on('circles')
                ->onDelete('cascade');
        });

        Schema::table('circle_user', function (Blueprint $table) {
            $table->unsignedBigInteger('circle_id')->change();
            $table->foreign('circle_id')
                ->references('id')->on('circles')
                ->onDelete('cascade');
        });

        Schema::table('circle_tag', function (Blueprint $table) {
            $table->foreign('circle_id')
                ->references('id')->on('circles')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('answers', function (Blueprint $table) {
            $table->dropForeign(['circle_id']);
        });

        Schema::table('booths', function (Blueprint $table) {
            $table->dropForeign(['circle_id']);
            // $table->integer('circle_id')->change();
        });

        Schema::table('circle_user', function (Blueprint $table) {
            $table->dropForeign(['circle_id']);
            // $table->integer('circle_id')->change();
        });

        Schema::table('circle_tag', function (Blueprint $table) {
            $table->dropForeign(['circle_id']);
        });

        Schema::table('circles', function (Blueprint $table) {
            $table->increments('id')->change();
        });

        // L58-66 と一緒に書くとエラーになるので別にした
        Schema::table('booths', function (Blueprint $table) {
            $table->integer('circle_id')->change();
        });

        Schema::table('circle_user', function (Blueprint $table) {
            $table->integer('circle_id')->change();
        });
    }
}

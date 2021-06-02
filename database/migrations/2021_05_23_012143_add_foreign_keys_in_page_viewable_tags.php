<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysInPageViewableTags extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->bigIncrements('id')->change();
        });

        Schema::table('page_viewable_tags', function (Blueprint $table) {
            $table->foreign('page_id')
                ->references('id')->on('pages')
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
        Schema::table('page_viewable_tags', function (Blueprint $table) {
            $table->dropForeign(['page_id']);
        });

        Schema::table('pages', function (Blueprint $table) {
            $table->unsignedInteger('id')->autoIncrement()->change();
        });
    }
}

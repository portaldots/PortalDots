<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysInTags extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('circle_tag', function (Blueprint $table) {
            $table->foreign('tag_id')
                ->references('id')->on('tags')
                ->onDelete('cascade');
        });
        Schema::table('page_viewable_tags', function (Blueprint $table) {
            $table->foreign('tag_id')
                ->references('id')->on('tags')
                ->onDelete('cascade');
        });
        Schema::table('form_answerable_tags', function (Blueprint $table) {
            $table->foreign('tag_id')
                ->references('id')->on('tags')
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
        Schema::table('circle_tag', function (Blueprint $table) {
            $table->dropForeign(['tag_id']);
        });
        Schema::table('page_viewable_tags', function (Blueprint $table) {
            $table->dropForeign(['tag_id']);
        });
        Schema::table('form_answerable_tags', function (Blueprint $table) {
            $table->dropForeign(['tag_id']);
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->string('audience', 16)->default('everyone')->after('is_public');
        });

        // タグによる閲覧制限が設定済みのお知らせは、audience を selected として引き継ぐ
        DB::statement(
            "UPDATE pages SET audience = 'selected'
            WHERE EXISTS (
                SELECT 1 FROM page_viewable_tags WHERE page_viewable_tags.page_id = pages.id
            )"
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn('audience');
        });
    }
};

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
        Schema::table('forms', function (Blueprint $table) {
            $table->string('audience', 16)->default('everyone')->after('is_public');
        });

        // 回答可能なタグが設定済みのフォームは、audience を selected として引き継ぐ
        DB::statement(
            "UPDATE forms SET audience = 'selected'
            WHERE EXISTS (
                SELECT 1 FROM form_answerable_tags WHERE form_answerable_tags.form_id = forms.id
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
        Schema::table('forms', function (Blueprint $table) {
            $table->dropColumn('audience');
        });
    }
};

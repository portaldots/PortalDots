<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropCreatedByAndUpdatedByColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn('created_by');
            $table->dropColumn('updated_by');
        });

        Schema::table('forms', function (Blueprint $table) {
            $table->dropColumn('created_by');
        });

        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn('created_by');
            $table->dropColumn('updated_by');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->unsignedInteger('created_by')->after('created_at');
            $table->unsignedInteger('updated_by')->after('updated_at');
        });

        Schema::table('forms', function (Blueprint $table) {
            $table->unsignedInteger('created_by')->after('close_at');
        });

        Schema::table('pages', function (Blueprint $table) {
            $table->unsignedInteger('created_by')->after('created_at');
            $table->unsignedInteger('updated_by')->after('updated_at');
        });
    }
}

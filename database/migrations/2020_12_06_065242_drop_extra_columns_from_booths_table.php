<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropExtraColumnsFromBoothsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('booths', function (Blueprint $table) {
            $table->dropColumn('created_at');
            $table->dropColumn('created_by');
            $table->dropColumn('updated_at');
            $table->dropColumn('updated_by');
            $table->dropColumn('notes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('booths', function (Blueprint $table) {
            $table->dateTime('created_at')->after('circle_id')->default('2020-12-01 00:00:00');
            $table->unsignedInteger('created_by')->after('created_at');
            $table->dateTime('updated_at')->after('created_by')->default('2020-12-01 00:00:00');
            $table->unsignedInteger('updated_by')->after('updated_at');
            $table->text('notes')->nullable()->after('updated_by');
        });
    }
}

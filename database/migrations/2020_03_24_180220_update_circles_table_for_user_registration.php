<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateCirclesTableForUserRegistration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('circles', function (Blueprint $table) {
            $table->string('name_yomi')->after('name');
            $table->string('group_name')->after('name_yomi');
            $table->string('group_name_yomi')->after('group_name');
            $table->string('invitation_token')->nullable()->after('group_name_yomi');
            $table->dateTime('submitted_at')->nullable()->after('invitation_token');
            $table->string('status')->nullable()->after('submitted_at');
            $table->longText('status_reason')->nullable()->after('status');
            $table->dateTime('status_set_at')->nullable()->after('status_reason');
            $table->unsignedBigInteger('status_set_by')->nullable()->after('status_set_at');

            $table->dropColumn(['created_by', 'updated_by']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('circles', function (Blueprint $table) {
            $table->dropColumn([
                'name_yomi',
                'group_name',
                'group_name_yomi',
                'invitation_token',
                'submitted_at',
                'status',
                'status_reason',
                'status_set_at',
                'status_set_by',
            ]);

            $table->unsignedInteger('created_by')->after('created_at');
            $table->unsignedInteger('updated_by')->after('updated_at');
        });
    }
}

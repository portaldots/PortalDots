<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_yomi');
            $table->boolean('is_individual');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('group_user', function (Blueprint $table) {
            $table->foreignId('group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role');     // owner | member
            $table->primary(['group_id', 'user_id']);
        });

        Schema::table('circles', function (Blueprint $table) {
            $table->dropColumn('group_name');
            $table->dropColumn('group_name_yomi');
            $table->foreignId('group_id')->after('id')->constrained()->cascadeOnDelete();
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
            $table->dropForeign(['group_id']);
            $table->dropColumn('group_id');
            $table->string('group_name')->after('name_yomi');
            $table->string('group_name_yomi')->after('group_name');
        });
        Schema::dropIfExists('group_user');
        Schema::dropIfExists('groups');
    }
};

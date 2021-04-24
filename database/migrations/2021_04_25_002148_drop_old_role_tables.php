<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class DropOldRoleTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('auth_staff_page');
        Schema::dropIfExists('auth_staff_role');
        Schema::dropIfExists('roles');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $prefix = config('database.connections.mysql.prefix', '');

        DB::statement(
            "-- Create syntax for TABLE 'auth_staff_page'
            CREATE TABLE `{$prefix}auth_staff_page` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `main_page_type` varchar(255) NOT NULL DEFAULT '' COMMENT '認可設定をする対象のmain_page_type',
            `is_authorized` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0:原則不認可(ホワイトリスト使用)、1:原則認可(ブラックリスト使用)',
            PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
        );

        DB::statement(
            "-- Create syntax for TABLE 'auth_staff_role'
            CREATE TABLE `{$prefix}auth_staff_role` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `auth_staff_page_id` int(11) NOT NULL COMMENT 'auth_staff_page の ID',
            `role_id` int(11) NOT NULL COMMENT 'role_userテーブルのID',
            `is_authorized` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'このrole_idのroleに所属するユーザーは認可されているか',
            PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
        );

        DB::statement(
            "-- Create syntax for TABLE 'roles'
            CREATE TABLE `{$prefix}roles` (
            `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL DEFAULT '',
            `notes` text,
            PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
        );
    }
}

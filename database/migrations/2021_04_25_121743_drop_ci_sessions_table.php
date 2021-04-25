<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class DropCiSessionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('ci_sessions');
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
            "-- Create syntax for TABLE 'ci_sessions'
            CREATE TABLE `{$prefix}ci_sessions` (
            `id` varchar(40) NOT NULL,
            `ip_address` varchar(45) NOT NULL,
            `timestamp` int(10) unsigned NOT NULL DEFAULT '0',
            `data` blob NOT NULL,
            KEY `ci_sessions_timestamp` (`timestamp`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
        );
    }
}

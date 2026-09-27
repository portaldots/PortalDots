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
        Schema::create('thread_entry_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('thread_entry_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('name');
            $table->integer('size');
            $table->string('mime');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('thread_entry_attachments');
    }
};

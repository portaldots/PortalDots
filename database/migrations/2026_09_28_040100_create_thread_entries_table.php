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
        Schema::create('thread_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('thread_id')->constrained()->cascadeOnDelete();
            $table->string('kind', 24);
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('author_side', 16);
            $table->foreignId('contact_category_id')->nullable()->constrained()->nullOnDelete();
            $table->text('body')->nullable();
            $table->string('event_type')->nullable();
            $table->json('event_payload')->nullable();
            $table->string('client_token', 64)->nullable();
            $table->timestamps();
            $table->unique(['thread_id', 'client_token']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('thread_entries');
    }
};

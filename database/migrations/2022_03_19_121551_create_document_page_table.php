<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDocumentPageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->id()->change();
        });

        Schema::create('document_page', function (Blueprint $table) {
            $table
                ->foreignId('document_id')
                ->constrained()
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table
                ->foreignId('page_id')
                ->constrained()
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('document_page');

        Schema::table('documents', function (Blueprint $table) {
            $table->unsignedInteger('id', true)->length(11)->change();
        });
    }
}

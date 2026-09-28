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
        Schema::create('document_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('version');
            $table->string('path');
            $table->integer('size');
            $table->string('extension');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['document_id', 'version']);
        });

        // 既存の配布資料は、それぞれ現在のファイルを第1版として引き継ぐ
        DB::statement(
            'INSERT INTO document_versions (document_id, version, path, size, extension, created_at, updated_at)
            SELECT id, 1, path, size, extension, created_at, updated_at FROM documents'
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('document_versions');
    }
};

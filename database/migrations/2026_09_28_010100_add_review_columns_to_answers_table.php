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
        Schema::table('answers', function (Blueprint $table) {
            $table->string('review_status', 16)->nullable()->after('circle_id');
            $table->text('review_note')->nullable()->after('review_status');
            $table->foreignId('reviewed_by')->nullable()->after('review_note')->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            $table->timestamp('submitted_at')->nullable()->after('reviewed_at');
            $table->unsignedInteger('lock_version')->default(0)->after('submitted_at');
        });

        // 既存の回答は、それぞれの最終更新日時を提出日時として引き継ぐ
        // （review_status は、確認不要な既存フォームの回答として NULL のままとする）
        DB::statement('UPDATE answers SET submitted_at = updated_at');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('answers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reviewed_by');
            $table->dropColumn(['review_status', 'review_note', 'reviewed_at', 'submitted_at', 'lock_version']);
        });
    }
};

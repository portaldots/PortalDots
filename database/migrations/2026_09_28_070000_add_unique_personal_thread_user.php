<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $duplicateUsers = DB::table('threads')
            ->whereNotNull('user_id')
            ->select('user_id')
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('user_id');

        foreach ($duplicateUsers as $userId) {
            DB::transaction(function () use ($userId) {
                $threads = DB::table('threads')->where('user_id', $userId)
                    ->orderBy('id')->lockForUpdate()->get();
                $primary = $threads->first();
                $latest = $threads->whereNotNull('last_entry_at')->sortByDesc('last_entry_at')->first()
                    ?? $threads->sortByDesc('updated_at')->first();

                foreach ($threads->skip(1) as $thread) {
                    // 別の会話で同じ送信トークンが使われていても、過去の行を残す。
                    DB::table('thread_entries')->where('thread_id', $thread->id)->update(['client_token' => null]);
                    DB::table('thread_entries')->where('thread_id', $thread->id)->update(['thread_id' => $primary->id]);
                    DB::table('threads')->where('id', $thread->id)->delete();
                }

                DB::table('threads')->where('id', $primary->id)->update([
                    'status' => $latest->status,
                    'assignee_id' => $latest->assignee_id,
                    'last_entry_at' => $latest->last_entry_at,
                    'lock_version' => $threads->max('lock_version') + 1,
                    'updated_at' => now(),
                ]);
            });
        }

        Schema::table('threads', function (Blueprint $table) {
            $table->unique('user_id', 'threads_user_id_unique');
        });
        if (Schema::hasIndex('threads', 'threads_user_id_migration_index')) {
            Schema::table('threads', function (Blueprint $table) {
                $table->dropIndex('threads_user_id_migration_index');
            });
        }
    }

    public function down(): void
    {
        // MySQL が外部キーに一意インデックスを使っている場合は代替を先に作る。
        if (!Schema::hasIndex('threads', 'threads_user_id_migration_index')) {
            Schema::table('threads', function (Blueprint $table) {
                $table->index('user_id', 'threads_user_id_migration_index');
            });
        }
        Schema::table('threads', function (Blueprint $table) {
            $table->dropUnique('threads_user_id_unique');
        });
    }
};

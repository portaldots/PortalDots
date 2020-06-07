<?php

declare(strict_types=1);

namespace App\Services\Pages;

use DB;
use App\Eloquents\Page;
use App\Eloquents\User;
use Illuminate\Support\Collection;

class PagesService
{
    /**
     * お知らせを作成する
     *
     * @param string $title タイトル
     * @param string $body 本文
     * @param User $created_by 作成者
     * @param string $notes スタッフ用メモ
     * @param Collection|null $viewable_tags お知らせを閲覧可能な企画のタグ
     * @return Page
     */
    public function create(
        string $title,
        string $body,
        User $created_by,
        string $notes,
        ?Collection $viewable_tags = null
    ) {
        return DB::transaction(function () use ($title, $body, $created_by, $notes, $viewable_tags) {
            $page = Page::create([
                'title' => $title,
                'body' => $body,
                'created_by' => $created_by->id,
                'updated_by' => $created_by->id,
                'notes' => $notes,
            ]);

            $circle->users()->save($leader, ['is_leader' => true]);

            return $circle;
        });
    }

    public function update(Circle $circle, string $name, string $name_yomi, string $group_name, string $group_name_yomi)
    {
        return $circle->update([
            'name' => $name,
            'name_yomi' => $name_yomi,
            'group_name' => $group_name,
            'group_name_yomi' => $group_name_yomi,
        ]);
    }

    public function regenerateInvitationToken(Circle $circle)
    {
        return $circle->update([
            'invitation_token' => $this->generateInvitationToken(),
        ]);
    }

    public function addMember(Circle $circle, User $user)
    {
        return $circle->users()->save($user, ['is_leader' => false]);
    }

    public function removeMember(Circle $circle, User $user)
    {
        return $circle->users()->detach($user->id);
    }

    public function remove(Circle $circle)
    {
        return DB::transaction(function () use ($circle) {
            $circle->users()->detach();
            return $circle->delete();
        });
    }

    /**
     * 参加登録を提出する
     */
    public function submit(Circle $circle)
    {
        $circle->submitted_at = now();
        return $circle->save();
    }

    private function generateInvitationToken()
    {
        return bin2hex(random_bytes(16));
    }

    /**
     * 指定された企画についてタグを保存する
     */
    public function saveTags(Circle $circle, array $tags)
    {
        // 検索時は大文字小文字の区別をしない
        // ($tags と $exist_tags の間で大文字小文字が異なる場合、$exist_tags の表記を優先するため)
        $exist_tags = Tag::whereIn('name', $tags)->get();

        $circle->tags()->sync($exist_tags->pluck('id')->all());
    }
}

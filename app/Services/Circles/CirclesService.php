<?php

declare(strict_types=1);

namespace App\Services\Circles;

use DB;
use App\Eloquents\User;
use App\Eloquents\Circle;
use App\Eloquents\Tag;

class CirclesService
{
    /**
     * 企画を作成する
     *
     * @param User $leader 企画責任者
     * @param string $name 企画の名前
     * @param string $name_yomi 企画の名前(よみ)
     * @param string $group_name 企画団体の名前
     * @param string $group_name_yomi 企画団体の名前(よみ)
     * @return Circle
     */
    public function create(User $leader, string $name, string $name_yomi, string $group_name, string $group_name_yomi)
    {
        return DB::transaction(function () use ($leader, $name, $name_yomi, $group_name, $group_name_yomi) {
            $circle = Circle::create([
                'name' => $name,
                'name_yomi' => $name_yomi,
                'group_name' => $group_name,
                'group_name_yomi' => $group_name_yomi,
                'invitation_token' => $this->generateInvitationToken(),
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

        $diff = array_udiff($tags, $exist_tags->pluck('name')->all(), 'strcasecmp');

        foreach ($diff as $insert) {
            Tag::create(['name' => $insert]);
        }

        // $tags 配列の順番で保存するため、もう一度 DB からタグ一覧を取得する
        $tags_on_db = Tag::whereIn('name', $tags)->get();

        $circle->tags()->sync($tags_on_db->pluck('id')->all());
    }
}

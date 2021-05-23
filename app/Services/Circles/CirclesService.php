<?php

declare(strict_types=1);

namespace App\Services\Circles;

use DB;
use App\Eloquents\User;
use App\Eloquents\Circle;
use App\Eloquents\Place;
use App\Eloquents\Tag;
use App\Mail\Circles\ApprovedMailable;
use App\Mail\Circles\RejectedMailable;
use App\Mail\Circles\SubmitedMailable;
use App\Services\Circles\Exceptions\DenyCreateTagsException;
use Illuminate\Support\Facades\Mail;

class CirclesService
{
    /**
     * 企画を作成する
     *
     * @param User $leader 企画責任者
     * @param string $name 企画名
     * @param string $name_yomi 企画名(よみ)
     * @param string $group_name 企画を出店する団体の名称
     * @param string $group_name_yomi 企画を出店する団体の名称(よみ)
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
     * 指定された企画について場所を保存する
     */
    public function savePlaces(Circle $circle, array $place_ids)
    {
        $exist_places = Place::whereIn('id', $place_ids)->get();
        $circle->places()->sync($exist_places->pluck('id')->all());
    }

    /**
     * 指定された企画についてタグを保存する
     *
     * @param Circle $circle
     * @param array $tags
     * @param boolean $allow_create タグの新規作成を許可するかどうか
     * @throws DenyCreateTagsException $allow_create が false なのに企画タグの新規作成が必要になった場合に発生する例外
     * @return void
     */
    public function saveTags(Circle $circle, array $tags, bool $allow_create = true)
    {
        // 検索時は大文字小文字の区別をしない
        // ($tags と $exist_tags の間で大文字小文字が異なる場合、$exist_tags の表記を優先するため)
        $exist_tags = Tag::whereIn('name', $tags)->get();

        $diff = array_udiff($tags, $exist_tags->pluck('name')->all(), 'strcasecmp');

        foreach ($diff as $insert) {
            if (!$allow_create) {
                throw new DenyCreateTagsException('企画タグの作成は許可されていません');
            }

            Tag::create(['name' => $insert]);
        }

        // $tags 配列の順番で保存するため、もう一度 DB からタグ一覧を取得する
        $tags_on_db = Tag::whereIn('name', $tags)->get();

        $circle->tags()->sync($tags_on_db->pluck('id')->all());
    }

    public function sendSubmitedEmail(User $user, Circle $circle)
    {
        Mail::to($user)
        ->send(
            (new SubmitedMailable(
                $circle,
            ))
                ->replyTo(config('portal.contact_email'), config('portal.admin_name'))
                ->subject("【参加登録】「{$circle->name}」の参加登録を提出しました")
        );
    }

    public function sendApprovedEmail(User $user, Circle $circle)
    {
        Mail::to($user)
        ->send(
            (new ApprovedMailable(
                $circle,
            ))
                ->replyTo(config('portal.contact_email'), config('portal.admin_name'))
                ->subject("【受理】「{$circle->name}」の参加登録が受理されました")
        );
    }

    public function sendRejectedEmail(User $user, Circle $circle)
    {
        Mail::to($user)
        ->send(
            (new RejectedMailable(
                $circle,
            ))
                ->replyTo(config('portal.contact_email'), config('portal.admin_name'))
                ->subject("【不受理】「{$circle->name}」の参加登録は受理されませんでした")
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Services\Circles;

use App\Eloquents\Answer;
use App\Eloquents\User;
use App\Eloquents\Circle;
use App\Eloquents\Form;
use App\Eloquents\ParticipationType;
use App\Eloquents\Place;
use App\Eloquents\Tag;
use App\Mail\Circles\ApprovedMailable;
use App\Mail\Circles\RejectedMailable;
use App\Mail\Circles\SubmitedMailable;
use App\Services\Circles\Exceptions\DenyCreateTagsException;
use App\Services\Tags\TagsService;
use App\Services\Utils\ActivityLogService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class CirclesService
{
    public function __construct(
        private ActivityLogService $activityLogService,
        private TagsService $tagsService
    ) {
    }

    /**
     * 企画を作成する
     *
     * @param ParticipationType $participationType 参加種別
     * @param User $leader 企画責任者
     * @param string $name 企画名
     * @param string $name_yomi 企画名(よみ)
     * @param string $group_name 企画を出店する団体の名称
     * @param string $group_name_yomi 企画を出店する団体の名称(よみ)
     * @return Circle
     */
    public function create(
        ParticipationType $participationType,
        User $leader,
        string $name,
        string $name_yomi,
        string $group_name,
        string $group_name_yomi
    ) {
        return DB::transaction(function () use (
            $participationType,
            $leader,
            $name,
            $name_yomi,
            $group_name,
            $group_name_yomi
        ) {
            $circle = Circle::create([
                'participation_type_id' => $participationType->id,
                'name' => $name,
                'name_yomi' => $name_yomi,
                'group_name' => $group_name,
                'group_name_yomi' => $group_name_yomi,
                'invitation_token' => $this->generateInvitationToken(),
            ]);

            $circle->users()->save($leader, ['is_leader' => true]);

            $circle->tags()->attach($participationType->tags()->pluck('id'));

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
     *
     * @param Circle $circle
     * @param array $place_ids
     * @param User $caused_by 場所を保存したユーザー
     * @return void
     */
    public function savePlaces(Circle $circle, array $place_ids, User $caused_by)
    {
        DB::transaction(function () use ($circle, $place_ids, $caused_by) {
            $old_places = $circle->places;
            $exist_places = Place::whereIn('id', $place_ids)->get();
            $circle->places()->sync($exist_places->pluck('id')->all());

            $map_function = function ($place) {
                return [
                    'id' => $place->id,
                    'name' => $place->name,
                ];
            };

            $this->activityLogService->logOnlyAttributesChanged(
                'booth',
                $caused_by,
                $circle,
                $old_places->map($map_function)->toArray(),
                $exist_places->map($map_function)->toArray()
            );
        });
    }

    /**
     * 指定された企画についてタグを保存する
     *
     * @param Circle $circle
     * @param array $tags
     * @param boolean $allow_create タグの新規作成を許可するかどうか
     * @param User $caused_by タグを保存したユーザー
     * @throws DenyCreateTagsException $allow_create が false なのに企画タグの新規作成が必要になった場合に発生する例外
     * @return void
     */
    public function saveTags(Circle $circle, array $tags, bool $allow_create, User $caused_by)
    {
        DB::transaction(function () use ($circle, $tags, $allow_create, $caused_by) {
            $old_tags = $circle->tags;

            $tags_on_db = $this->tagsService->getOrCreateTags($tags, $allow_create);

            $circle->tags()->sync($tags_on_db->pluck('id')->all());

            // ログに残す
            $map_function = function ($tag) {
                return [
                    'id' => $tag->id,
                    'name' => $tag->name,
                ];
            };

            $this->activityLogService->logOnlyAttributesChanged(
                'circle_tag',
                $caused_by,
                $circle,
                $old_tags->map($map_function)->toArray(),
                $tags_on_db->map($map_function)->toArray()
            );
        });
    }

    public function sendSubmitedEmail(
        User $user,
        Circle $circle,
        Form $participationForm = null,
        Collection $questions = null,
        Answer $answer = null,
        array $answerDetails = null
    ) {
        Mail::to($user)
            ->send(
                (new SubmitedMailable(
                    $circle,
                    $participationForm,
                    $questions,
                    $answer,
                    $answerDetails
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

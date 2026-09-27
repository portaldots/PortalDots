<?php

namespace App\Eloquents;

use App\Contracts\AudiencePolicy;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property int $id
 * @property string $name
 * @property string $description
 * @property Carbon $open_at
 * @property Carbon $close_at
 * @property string $type
 * @property int $max_answers
 * @property bool $is_public
 * @property-read Carbon $created_at
 * @property-read Carbon $updated_at
 * @property-read Collection $questions
 */
class Form extends Model
{
    use LogsActivity;

    // requires_review はDB上デフォルト false だが、Eloquentはinsert時にDBの
    // デフォルト値を読み返さないため、作成直後のインスタンスでも false になるよう明示する
    protected $attributes = [
        'requires_review' => false,
    ];

    protected $fillable = [
        'name',
        'description',
        'confirmation_message',
        'open_at',
        'close_at',
        'type',
        'max_answers',
        'is_public',
        'requires_review',
        'audience',
    ];

    protected $casts = [
        'max_answers' => 'int',
        'is_public' => 'bool',
        'requires_review' => 'bool',
        'open_at' => 'datetime',
        'close_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('form')
            ->logOnly([
                'id',
                'name',
                'description',
                'open_at',
                'close_at',
                'max_answers',
                'is_public',
                'audience',
            ])
            ->logOnlyDirty();
    }

    /**
     * フォームの公開範囲として選択可能な値の一覧を取得する
     *
     * ゲストという概念がないため、AudiencePolicy が signed_in を許可していても、
     * フォームでは everyone・selected のみを選択可能とする
     *
     * @param AudiencePolicy $policy
     * @return string[]
     */
    public static function allowedAudiences(AudiencePolicy $policy): array
    {
        return array_values(array_intersect(
            $policy->allowedAudiences(),
            [AudiencePolicy::EVERYONE, AudiencePolicy::SELECTED]
        ));
    }

    public function answerableTags()
    {
        return $this->belongsToMany(Tag::class, 'form_answerable_tags')
            ->using(FormAnswerableTag::class);
    }

    public function assignments()
    {
        return $this->hasMany(FormAssignment::class);
    }

    /**
     * 指定した企画が回答できるフォームの一覧を取得できるクエリスコープ
     *
     * $circle を省略した場合、everyone のフォームのみが取得される
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param Circle|null $circle
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByCircle($query, ?Circle $circle = null)
    {
        $query = $query->with('answerableTags');

        return $query->where(function ($query) use ($circle) {
            $query->where('audience', AudiencePolicy::EVERYONE);

            if (!empty($circle)) {
                $tagIds = $circle->tags->pluck('id')->all();
                $query->orWhere(function ($query) use ($circle, $tagIds) {
                    $query->where('audience', AudiencePolicy::SELECTED)
                        ->where(function ($query) use ($circle, $tagIds) {
                            $query->whereHas('answerableTags', function ($query) use ($tagIds) {
                                $query->whereIn('tags.id', $tagIds);
                            })->orWhereHas('assignments', function ($query) use ($circle) {
                                $query->where('circle_id', $circle->id);
                            });
                        });
                });
            }
        });
    }

    /**
     * 企画参加登録フォームは含めない
     */
    public function scopeWithoutParticipationForms($query)
    {
        return $query->doesntHave('participationType');
    }

    /**
     * 公開中のものを取得
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * 受付終了時刻の早い順で並び替え
     */
    public function scopeCloseOrder($query, $direction = 'asc')
    {
        return $query->orderBy('close_at', $direction);
    }

    /**
     * 現時点で受付中のもの
     */
    public function scopeOpen($query)
    {
        return $query->where('open_at', '<=', now())->where('close_at', '>=', now());
    }

    /**
     * 現時点で受付終了しているもの
     */
    public function scopeClosed($query)
    {
        return $query->where('close_at', '<', now());
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }

    public function participationType()
    {
        return $this->hasOne(ParticipationType::class);
    }

    // TODO: 意味的に isAnswered という名前に変えたい
    public function answered(Circle $circle)
    {
        $answer = Answer::where('form_id', $this->id)->where('circle_id', $circle->id)->first();
        return !empty($answer);
    }

    /**
     * 企画 $circle による、このフォームへの最新の回答を取得する
     *
     * @param Circle $circle
     * @return Answer|null
     */
    public function latestAnswerFor(Circle $circle): ?Answer
    {
        return Answer::where('form_id', $this->id)->where('circle_id', $circle->id)->latest('id')->first();
    }

    /**
     * 未申請の企画(idとnameのみ)を返す関数
     *
     * このフォームの回答対象となっている、承認済みの企画のうち、
     * まだ回答していない企画を返す
     */
    public function notAnswered()
    {
        return Circle::approved()
            ->targetedByForm($this)
            ->whereDoesntHave('answers', function ($query) {
                $query->where('form_id', $this->id);
            })
            ->get(['circles.id', 'circles.name']);
    }

    /**
     * 企画 $circle に対するこのフォームの実効的な期限を取得する
     *
     * 個別の期限（form_assignments.due_at）が設定されていない場合は、
     * フォームの受付終了日時（close_at）を返す
     *
     * @param Circle $circle
     * @return Carbon
     */
    public function effectiveDueDateFor(Circle $circle): Carbon
    {
        $assignment = FormAssignment::where('form_id', $this->id)
            ->where('circle_id', $circle->id)
            ->first();

        return $assignment->due_at ?? $this->close_at;
    }

    /**
     * 企画 $circle にとって、このフォームが期限切れかどうかを判定する
     *
     * 提出後の確認が必要なフォームの場合、期限を過ぎてもまだ提出していないか、
     * 差し戻されたまま再提出していない場合に期限切れとする。
     * それ以外のフォームの場合、期限を過ぎてもまだ回答していない場合に期限切れとする。
     *
     * @param Circle $circle
     * @return bool
     */
    public function isOverdueFor(Circle $circle): bool
    {
        if (!$this->effectiveDueDateFor($circle)->isPast()) {
            return false;
        }

        if ($this->requires_review) {
            $latestAnswer = $this->latestAnswerFor($circle);
            if (empty($latestAnswer)) {
                return true;
            }
            return $latestAnswer->review_status === Answer::REVIEW_STATUS_RETURNED;
        }

        return !$this->answered($circle);
    }

    public function yetOpen()
    {
        return $this->open_at > now();
    }

    public function isOpen()
    {
        return $this->open_at->lte(now()) && $this->close_at->gte(now());
    }
}

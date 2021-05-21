<?php

namespace App\Eloquents;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
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

    protected static $logName = 'form';

    protected static $logAttributes = [
        'id',
        'name',
        'description',
        'open_at',
        'close_at',
        'max_answers',
        'is_public',
    ];

    protected static $logOnlyDirty = true;

    protected $fillable = [
        'name',
        'description',
        'open_at',
        'close_at',
        'type',
        'max_answers',
        'is_public',
    ];

    protected $dates = [
        'open_at', 'close_at',
    ];

    protected $casts = [
        'max_answers' => 'int',
        'is_public' => 'bool',
    ];

    public function answerableTags()
    {
        return $this->belongsToMany(Tag::class, 'form_answerable_tags')
            ->using(FormAnswerableTag::class);
    }

    /**
     * 指定した企画が回答できるフォームの一覧を取得できるクエリスコープ
     *
     * $circle を省略した場合、回答できる企画が限られているフォームを
     * 除くフォームの一覧が取得される
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param Circle|null $circle
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByCircle($query, ?Circle $circle = null)
    {
        $query = self::selectRaw('forms.*, min(`form_answerable_tags`.`tag_id`)')
            ->leftJoin('form_answerable_tags', 'forms.id', '=', 'form_answerable_tags.form_id')
            ->whereNull('form_answerable_tags.tag_id')
            ->with('answerableTags')
            ->groupBy('forms.id');

        if (empty($circle)) {
            return $query;
        }

        return $query
            ->orWhereIn('form_answerable_tags.tag_id', $circle->tags->pluck('id')->all());
    }

    /**
     * カスタムフォームは含めない
     */
    public function scopeWithoutCustomForms($query)
    {
        return $query->doesntHave('customForm');
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

    public function customForm()
    {
        return $this->hasOne(CustomForm::class);
    }

    // TODO: 意味的に isAnswered という名前に変えたい
    public function answered(Circle $circle)
    {
        $answer = Answer::where('form_id', $this->id)->where('circle_id', $circle->id)->first();
        return !empty($answer);
    }

    /**
     * 未申請の企画(idとnameのみ)を返す関数
     */
    public function notAnswered()
    {
        return DB::select(
            'SELECT id, name FROM circles
                WHERE
                    status = \'approved\'
                    AND NOT EXISTS
                    (SELECT circle_id FROM answers WHERE answers.form_id = :form_id AND answers.circle_id = circles.id)
            ',
            ['form_id' => $this->id]
        );
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

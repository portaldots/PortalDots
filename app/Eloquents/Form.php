<?php

namespace App\Eloquents;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $description
 * @property Carbon $open_at
 * @property Carbon $close_at
 * @property int $created_by
 * @property string $type
 * @property int $max_answers
 * @property bool $is_public
 * @property-read Carbon $created_at
 * @property-read Carbon $updated_at
 * @property-read Collection $questions
 */
class Form extends Model
{
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

    public function questions()
    {
        return $this->hasMany(Question::class);
    }
}

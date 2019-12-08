<?php

namespace App\Eloquents;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $question_id
 * @property string $value
 * @property int $priority
 * @property-read Carbon $created_at
 * @property-read Carbon $updated_at
 */
class Option extends Model
{
    protected $fillable = [
        'question_id',
        'value',
        'priority',
    ];

    protected $casts = [
        'question_id' => 'int',
        'priority' => 'int',
    ];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}

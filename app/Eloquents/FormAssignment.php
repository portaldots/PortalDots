<?php

declare(strict_types=1);

namespace App\Eloquents;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * フォームを個別の企画へ送付した記録。audience が selected のフォームにおいて、
 * タグによる指定とは別に、企画を個別に指定して回答対象にするために使う。
 *
 * @property int $id
 * @property int $form_id
 * @property int $circle_id
 * @property Carbon|null $due_at
 * @property int|null $assigned_by
 */
class FormAssignment extends Model
{
    protected $fillable = [
        'form_id',
        'circle_id',
        'due_at',
        'assigned_by',
    ];

    protected $casts = [
        'due_at' => 'datetime',
    ];

    public function form()
    {
        return $this->belongsTo(Form::class);
    }

    public function circle()
    {
        return $this->belongsTo(Circle::class);
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}

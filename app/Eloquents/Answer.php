<?php

namespace App\Eloquents;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property integer $id
 */
class Answer extends Model
{
    use LogsActivity;

    protected $fillable = [
        'form_id',
        'circle_id'
    ];

    protected static $logName = 'answer';

    protected static $logAttributes = [
        'id',
        'circle.id',
        'circle.name',
        'form.id',
        'form.name',
        // details_array に関しては、変更前の回答をログに残すことができないので注意
        'details_array'
    ];

    public function details()
    {
        return $this->hasMany(AnswerDetail::class);
    }

    public function circle()
    {
        return $this->belongsTo(Circle::class);
    }

    public function form()
    {
        return $this->belongsTo(Form::class);
    }

    /**
     * laravel-activitylog に記録するための回答詳細配列を返すアクセサ
     *
     * 現在は laravel-activitylog 以外の目的では使わない
     *
     * @return array
     */
    public function getDetailsArrayAttribute()
    {
        return $this->details->loadMissing('question')->toArray();
    }
}

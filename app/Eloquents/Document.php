<?php

namespace App\Eloquents;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Eloquents\Concerns\IsNewTrait;
use Spatie\Activitylog\Traits\LogsActivity;

class Document extends Model
{
    use IsNewTrait;
    use LogsActivity;

    protected static $logName = 'document';

    protected static $logAttributes = [
        'id',
        'name',
        'description',
        'path',
        'size',
        'extension',
        'is_public',
        'is_important',
        'schedule',
        'notes',
    ];

    protected static $logOnlyDirty = true;

    protected $casts = [
        'is_public' => 'bool',
        'is_important' => 'bool',
    ];

    protected $fillable = [
        'name',
        'description',
        'path',
        'size',
        'extension',
        'is_public',
        'is_important',
        'schedule_id',
        'notes',
    ];

    /**
     * モデルの「初期起動」メソッド
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('updated_at', function (Builder $builder) {
            $builder->orderBy('updated_at', 'desc');
        });
    }

    /**
     * 公開されている配布資料に限定するクエリスコープ
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }
}

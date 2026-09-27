<?php

namespace App\Eloquents;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Eloquents\Concerns\HasAudienceTrait;
use App\Eloquents\Concerns\IsNewTrait;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Page extends Model
{
    use HasAudienceTrait;
    use IsNewTrait;
    use LogsActivity;

    protected $casts = [
        'is_pinned' => 'bool',
        'is_public' => 'bool',
    ];

    protected $fillable = [
        'title',
        'body',
        'is_pinned',
        'is_public',
        'audience',
        'notes',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('page')
            ->logOnly([
                'id',
                'title',
                'body',
                'is_pinned',
                'is_public',
                'audience',
                'notes',
            ])
            ->logOnlyDirty();
    }

    /**
     * データベースが MySQL の FULLTEXT INDEX に対応しているかどうかを調べる
     *
     * @return boolean
     */
    public static function isMySqlFulltextIndexSupported()
    {
        static $result = null;
        if ($result === null) {
            // MySQL 5.7 以上の場合のみ対応
            $results = DB::select(DB::raw("select version()")->getValue(DB::connection()->getQueryGrammar()));
            $mysql_version =  $results[0]->{'version()'};
            if (strpos(strtolower($mysql_version), 'mariadb') !== false) {
                // MariaDB を利用している場合
                return false;
            }
            $result = version_compare($mysql_version, '5.7.6', '>=');
        }
        return $result;
    }

    /**
     * データベースが MariaDB の FULLTEXT INDEX に対応しているかどうかを調べる
     *
     * @return boolean
     */
    public static function isMariaDbFulltextIndexSupported()
    {
        // 現在は一律非対応
        return false;
        // static $result = null;
        // if ($result === null) {
        //     // MariaDB 10 以上の場合のみ対応
        //     $results = DB::select(DB::raw("select version()")->getValue(DB::connection()->getQueryGrammar()));
        //     $mariadb_version =  $results[0]->{'version()'};
        //     if (strpos(strtolower($mariadb_version), 'mariadb') === false) {
        //         // MySQL を利用している場合
        //         return false;
        //     }
        //     $result = version_compare($mariadb_version, '10.0.15', '>=');
        // }
        // return $result;
    }

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

    public function documents()
    {
        return $this->belongsToMany(Document::class);
    }

    public function viewableTags()
    {
        return $this->belongsToMany(Tag::class, 'page_viewable_tags')
            ->using(PageViewableTag::class);
    }

    public function viewableCircles()
    {
        return $this->belongsToMany(Circle::class, 'page_viewable_circles')
            ->using(PageViewableCircle::class);
    }

    /**
     * このお知らせを読んだユーザー
     */
    public function usersWhoRead()
    {
        return $this->belongsToMany(User::class, 'reads')->using(Read::class);
    }

    /**
     * 公開されているお知らせに限定するクエリスコープ
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * 固定されたお知らせに限定するクエリスコープ
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param bool $is_pinned 固定されたお知らせ以外を取得する場合は false を指定する
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePinned($query, bool $is_pinned = true)
    {
        return $query->where('is_pinned', $is_pinned);
    }

    /**
     * フルテキストインデックスを使った検索
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $keywords
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByKeywords($query, ?string $keywords = null)
    {
        if (empty($keywords)) {
            return $query;
        }
        return $query->whereRaw("match(title,body) against (? IN BOOLEAN MODE)", [$keywords]);
    }
}

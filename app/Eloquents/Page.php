<?php

namespace App\Eloquents;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Eloquents\Concerns\IsNewTrait;
use Illuminate\Support\Facades\DB;

class Page extends Model
{
    use IsNewTrait;

    protected $fillable = [
        'title',
        'body',
        'created_by',
        'updated_by',
        'notes',
    ];

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
            $results = DB::select(DB::raw("select version()"));
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
        //     $results = DB::select(DB::raw("select version()"));
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

    public function viewableTags()
    {
        return $this->belongsToMany(Tag::class, 'page_viewable_tags')
            ->using(PageViewableTag::class);
    }

    /**
     * このお知らせを読んだユーザー
     */
    public function usersWhoRead()
    {
        return $this->belongsToMany(User::class, 'reads')->using(Read::class);
    }

    /**
     * 指定した企画が閲覧できるお知らせの一覧を取得できるクエリスコープ
     *
     * $circle を省略した場合、閲覧できる企画が限られているお知らせを
     * 除くお知らせの一覧が取得される
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param Circle|null $circle
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByCircle($query, ?Circle $circle = null)
    {
        $query = self::selectRaw('`pages`.*, min(`page_viewable_tags`.`tag_id`)')
            ->leftJoin('page_viewable_tags', 'pages.id', '=', 'page_viewable_tags.page_id')
            ->whereNull('page_viewable_tags.tag_id')
            ->with('viewableTags')
            ->groupBy('pages.id');

        if (empty($circle)) {
            return $query;
        }

        return $query
            ->orWhereIn('page_viewable_tags.tag_id', $circle->tags->pluck('id')->all());
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

    public function userCreatedBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function userUpdatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}

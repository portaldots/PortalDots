<?php

namespace App\Eloquents;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

class Schedule extends Model
{
    protected $dates = [
        'start_at',
    ];

    /**
     * 取得順を開催順にするクエリスコープ
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $direction asc か desc
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeStartOrder($query, $direction = 'asc')
    {
        return $query->orderBy('start_at', $direction);
    }

    /**
     * 未開催の予定に限定するクエリスコープ
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNotStarted($query)
    {
        return $query->where('start_at', '>', now());
    }

    /**
     * 開催済の予定に限定するクエリスコープ
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeEnded($query)
    {
        // TODO: end_at 基準での判定にしたい
        return $query->where('start_at', '<', now());
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    /**
     * get した結果を月ごとにグループ化する
     *
     * 返値例)
     *
     * [
     *  '2019年 12月' => [
     *    // 2019 / 12 に行われる予定の Eloquents
     *  ],
     *  '2020年 1月' => [
     *   // 2020 / 1 に行われる予定の Eloquents
     *  ]
     * ]
     *
     * @param Collection $queryResults
     * @return array
     */
    public static function groupByMonth(Collection $queryResults)
    {
        // TODO: Laravel コレクションの `groupBy` メソッドが使えるかも

        $return = [];
        $lastKey = '';
        foreach ($queryResults as $queryResult) {
            $year = $queryResult->start_at->format('Y');
            $month = $queryResult->start_at->format('n');
            $key = sprintf('%s年 %s月', $year, $month);
            if ($lastKey !== $key) {
                $return[$key] = [];
                $lastKey = $key;
            }
            $return[$key][] = $queryResult;
        }

        return $return;
    }
}

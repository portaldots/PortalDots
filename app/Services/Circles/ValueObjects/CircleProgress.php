<?php

declare(strict_types=1);

namespace App\Services\Circles\ValueObjects;

use App\Eloquents\Circle;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * 企画1件の進捗（申請フォームへの回答・配布資料の確認依頼それぞれの状況をまとめたもの）
 *
 * CircleProgressService でのみ生成する。
 */
final class CircleProgress
{
    // 担当の表記。用語をここへ集約しておき、将来の用語変更に対応しやすくする
    public const TURN_STAFF = 'スタッフ';
    public const TURN_CIRCLE = '企画';
    public const TURN_DONE = '完了';

    /**
     * @var Circle
     */
    private $circle;

    /**
     * @var Collection<int, ProgressUnit>
     */
    private $units;

    /**
     * @param Circle $circle
     * @param Collection<int, ProgressUnit> $units
     */
    public function __construct(Circle $circle, Collection $units)
    {
        $this->circle = $circle;
        $this->units = $units;
    }

    public function getCircle(): Circle
    {
        return $this->circle;
    }

    /**
     * @return Collection<int, ProgressUnit>
     */
    public function getUnits(): Collection
    {
        return $this->units;
    }

    /**
     * 指定した状態の単位だけを取得する
     *
     * @param string[] $states
     * @return Collection<int, ProgressUnit>
     */
    public function unitsInState(array $states): Collection
    {
        return $this->units
            ->filter(fn (ProgressUnit $unit) => in_array($unit->getState(), $states, true))
            ->values();
    }

    public function getTotalCount(): int
    {
        return $this->units->count();
    }

    public function getDoneCount(): int
    {
        return $this->unitsInState([ProgressUnit::STATE_DONE])->count();
    }

    /**
     * 「{done}/{total}」形式の進捗表示
     */
    public function getProgressLabel(): string
    {
        return "{$this->getDoneCount()}/{$this->getTotalCount()}";
    }

    /**
     * 未完了の申請フォーム単位のうち、もっとも早い実効的な期限。該当が無ければ null
     */
    public function getNextDueAt(): ?Carbon
    {
        return $this->units
            ->filter(function (ProgressUnit $unit) {
                return $unit->isType(ProgressUnit::TYPE_FORM) && !$unit->isState(ProgressUnit::STATE_DONE);
            })
            ->map(fn (ProgressUnit $unit) => $unit->getDueAt())
            ->filter()
            ->sort()
            ->first();
    }

    /**
     * 期限切れの申請フォーム単位の件数
     */
    public function getOverdueCount(): int
    {
        return $this->units->filter(fn (ProgressUnit $unit) => $unit->isOverdue())->count();
    }

    /**
     * 担当。review の単位が1つでもあればスタッフ、todo・changes の単位が
     * 1つでもあれば企画、どちらも無ければ完了
     */
    public function getTurn(): string
    {
        if ($this->units->contains(fn (ProgressUnit $unit) => $unit->isState(ProgressUnit::STATE_REVIEW))) {
            return self::TURN_STAFF;
        }

        $waitingForCircle = $this->units->contains(function (ProgressUnit $unit) {
            return $unit->isState(ProgressUnit::STATE_TODO) || $unit->isState(ProgressUnit::STATE_CHANGES);
        });
        if ($waitingForCircle) {
            return self::TURN_CIRCLE;
        }

        return self::TURN_DONE;
    }

    /**
     * getTurn() の表示用ラベル。term() を経由するため config/portal.php の
     * terms を上書きすると表示も変わる
     */
    public function getTurnLabel(): string
    {
        return match ($this->getTurn()) {
            self::TURN_STAFF => term('staff_side'),
            self::TURN_CIRCLE => term('circle'),
            default => self::TURN_DONE,
        };
    }
}

<?php

declare(strict_types=1);

namespace App\Services\Circles;

use App\Contracts\AudiencePolicy;
use App\Eloquents\Answer;
use App\Eloquents\Circle;
use App\Eloquents\Document;
use App\Eloquents\DocumentApproval;
use App\Eloquents\Form;
use App\Services\Circles\ValueObjects\CircleProgress;
use App\Services\Circles\ValueObjects\ProgressUnit;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

/**
 * 企画1件、または複数の企画の「進捗」（公開中の申請フォームへの回答・配布資料の確認依頼
 * それぞれの状況）を計算するサービス
 *
 * 進捗の定義（単位の状態、進捗・次の期限・担当・期限切れの算出方法）はこのサービスにのみ持たせる。
 * forCircles は企画数に関わらず一定数のクエリで計算できるよう、関連データを一括で
 * 読み込んでから PHP 上で計算する。
 */
class CircleProgressService
{
    public function forCircle(Circle $circle): CircleProgress
    {
        return $this->forCircles(collect([$circle]))->first();
    }

    /**
     * @param Collection<int, Circle> $circles
     * @return Collection<int, CircleProgress> 企画IDをキーとする
     */
    public function forCircles(Collection $circles): Collection
    {
        $circles = EloquentCollection::make($circles->all());
        $circles->loadMissing('tags');

        $circleIds = $circles->pluck('id')->all();

        $forms = Form::public()
            ->withoutParticipationForms()
            ->with(['answerableTags', 'assignments' => function ($query) use ($circleIds) {
                $query->whereIn('circle_id', $circleIds);
            }])
            ->get();

        $latestAnswers = Answer::whereIn('circle_id', $circleIds)
            ->whereIn('form_id', $forms->pluck('id')->all())
            ->get()
            ->groupBy(fn (Answer $answer) => "{$answer->circle_id}:{$answer->form_id}")
            ->map(fn (Collection $answers) => $answers->sortByDesc('id')->first());

        $approvalsByCircle = DocumentApproval::whereIn('circle_id', $circleIds)
            ->with(['document.viewableTags', 'document.viewableCircles'])
            ->get()
            ->groupBy('circle_id');

        return $circles->mapWithKeys(function (Circle $circle) use ($forms, $latestAnswers, $approvalsByCircle) {
            $circleTagIds = $circle->tags->pluck('id')->all();
            $units = collect();

            foreach ($forms as $form) {
                if (!$this->formTargetsCircle($form, $circle, $circleTagIds)) {
                    continue;
                }

                $latestAnswer = $latestAnswers->get("{$circle->id}:{$form->id}");
                $units->push($this->buildFormUnit($form, $circle, $latestAnswer));
            }

            foreach ($approvalsByCircle->get($circle->id, []) as $approval) {
                $isVisible = !empty($approval->document)
                    && $this->documentVisibleToCircle($approval->document, $circle, $circleTagIds);
                if (!$isVisible) {
                    continue;
                }

                $units->push($this->buildDocumentUnit($approval));
            }

            return [$circle->id => new CircleProgress($circle, $units)];
        });
    }

    /**
     * Form::scopeByCircle / Circle::scopeTargetedByForm と同じ規則を、
     * 一括で読み込んだデータをもとにクエリを発行せず判定する
     */
    private function formTargetsCircle(Form $form, Circle $circle, array $circleTagIds): bool
    {
        if ($form->audience === AudiencePolicy::EVERYONE) {
            return true;
        }

        if ($form->audience !== AudiencePolicy::SELECTED) {
            return false;
        }

        $formTagIds = $form->answerableTags->pluck('id')->all();
        if (!empty(array_intersect($formTagIds, $circleTagIds))) {
            return true;
        }

        return $form->assignments->contains('circle_id', $circle->id);
    }

    /**
     * HasAudienceTrait::scopeVisibleTo と同じ規則を、一括で読み込んだデータをもとに
     * クエリを発行せず判定する
     *
     * 企画は必ずサインイン済みユーザーによって操作されるため、signed_in の配布資料も
     * 閲覧可能として扱う
     */
    private function documentVisibleToCircle(Document $document, Circle $circle, array $circleTagIds): bool
    {
        if (!$document->is_public) {
            return false;
        }

        if ($document->audience === AudiencePolicy::EVERYONE || $document->audience === AudiencePolicy::SIGNED_IN) {
            return true;
        }

        if ($document->audience !== AudiencePolicy::SELECTED) {
            return false;
        }

        $tagIds = $document->viewableTags->pluck('id')->all();
        if (!empty(array_intersect($tagIds, $circleTagIds))) {
            return true;
        }

        return $document->viewableCircles->contains('id', $circle->id);
    }

    private function buildFormUnit(Form $form, Circle $circle, ?Answer $latestAnswer): ProgressUnit
    {
        $assignment = $form->assignments->firstWhere('circle_id', $circle->id);
        $dueAt = $assignment->due_at ?? $form->close_at;

        if ($form->requires_review) {
            if (empty($latestAnswer)) {
                $state = ProgressUnit::STATE_TODO;
            } elseif ($latestAnswer->review_status === Answer::REVIEW_STATUS_RETURNED) {
                $state = ProgressUnit::STATE_CHANGES;
            } elseif ($latestAnswer->review_status === Answer::REVIEW_STATUS_ACCEPTED) {
                $state = ProgressUnit::STATE_DONE;
            } else {
                // 提出済み・確認待ち
                $state = ProgressUnit::STATE_REVIEW;
            }
        } else {
            $state = empty($latestAnswer) ? ProgressUnit::STATE_TODO : ProgressUnit::STATE_DONE;
        }

        return new ProgressUnit(
            ProgressUnit::TYPE_FORM,
            $state,
            $form->name,
            $dueAt,
            $this->isFormUnitOverdue($form, $latestAnswer, $dueAt),
            $form,
            $latestAnswer,
            null
        );
    }

    /**
     * Form::isOverdueFor と同じ規則
     */
    private function isFormUnitOverdue(Form $form, ?Answer $latestAnswer, Carbon $dueAt): bool
    {
        if (!$dueAt->isPast()) {
            return false;
        }

        if ($form->requires_review) {
            return empty($latestAnswer) || $latestAnswer->review_status === Answer::REVIEW_STATUS_RETURNED;
        }

        return empty($latestAnswer);
    }

    private function buildDocumentUnit(DocumentApproval $approval): ProgressUnit
    {
        if ($approval->status === DocumentApproval::STATUS_CHANGES_REQUESTED) {
            $state = ProgressUnit::STATE_REVIEW;
        } elseif ($approval->status === DocumentApproval::STATUS_APPROVED) {
            $state = ProgressUnit::STATE_DONE;
        } else {
            $state = ProgressUnit::STATE_TODO;
        }

        return new ProgressUnit(
            ProgressUnit::TYPE_DOCUMENT,
            $state,
            $approval->document->name,
            null,
            false,
            null,
            null,
            $approval
        );
    }
}

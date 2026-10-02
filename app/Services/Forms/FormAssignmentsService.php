<?php

declare(strict_types=1);

namespace App\Services\Forms;

use App\Eloquents\Circle;
use App\Eloquents\Form;
use App\Eloquents\FormAssignment;
use App\Eloquents\User;
use App\Events\Forms\FormDueDateChanged;
use App\Events\Forms\FormSent;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FormAssignmentsService
{
    /**
     * フォームを複数の企画へ送付する
     *
     * 既に送付済みの企画が含まれる場合、その企画の送付は重複させず、期限のみ更新する。
     * 承認済みではない企画は無視する。
     *
     * @param Form $form
     * @param int[] $circleIds 送付先の企画ID
     * @param Carbon|null $due_at 共通の期限（指定しない場合は $form->close_at が実効的な期限になる）
     * @param User $assigned_by
     * @return void
     */
    public function assignToCircles(Form $form, array $circleIds, ?Carbon $due_at, User $assigned_by): void
    {
        $circles = Circle::approved()->whereIn('id', $circleIds)->get();

        DB::transaction(function () use ($form, $circles, $due_at, $assigned_by) {
            foreach ($circles as $circle) {
                $assignment = FormAssignment::where('form_id', $form->id)
                    ->where('circle_id', $circle->id)
                    ->lockForUpdate()
                    ->first();

                if (empty($assignment)) {
                    $assignment = FormAssignment::create([
                        'form_id' => $form->id,
                        'circle_id' => $circle->id,
                        'due_at' => $due_at,
                        'assigned_by' => $assigned_by->id,
                    ]);

                    event(new FormSent(
                        $circle->id,
                        $form->id,
                        $form->name,
                        optional($due_at)->toIso8601String(),
                        $assignment->id
                    ));
                    continue;
                }

                $dueAtChanged = !$this->sameDueAt($assignment->due_at, $due_at);
                $assignment->update(['due_at' => $due_at, 'assigned_by' => $assigned_by->id]);

                if ($dueAtChanged) {
                    event(new FormDueDateChanged(
                        $circle->id,
                        $form->id,
                        $form->name,
                        optional($due_at)->toIso8601String(),
                        $assignment->id
                    ));
                }
            }
        });
    }

    /**
     * 送付済みの企画の期限を変更する
     *
     * @param Form $form
     * @param Circle $circle
     * @param Carbon|null $due_at
     * @return void
     */
    public function updateDueDate(Form $form, Circle $circle, ?Carbon $due_at): void
    {
        DB::transaction(function () use ($form, $circle, $due_at) {
            $assignment = FormAssignment::where('form_id', $form->id)
                ->where('circle_id', $circle->id)
                ->lockForUpdate()
                ->first();

            if (empty($assignment)) {
                return;
            }

            $dueAtChanged = !$this->sameDueAt($assignment->due_at, $due_at);
            $assignment->update(['due_at' => $due_at]);

            if ($dueAtChanged) {
                event(new FormDueDateChanged(
                    $circle->id,
                    $form->id,
                    $form->name,
                    optional($due_at)->toIso8601String(),
                    $assignment->id
                ));
            }
        });
    }

    /**
     * @param Carbon|null $a
     * @param Carbon|null $b
     * @return bool
     */
    private function sameDueAt(?Carbon $a, ?Carbon $b): bool
    {
        if (empty($a) && empty($b)) {
            return true;
        }
        if (empty($a) || empty($b)) {
            return false;
        }
        return $a->eq($b);
    }

    /**
     * 企画への送付を取り消す
     *
     * @param Form $form
     * @param Circle $circle
     * @return void
     */
    public function unassign(Form $form, Circle $circle): void
    {
        FormAssignment::where('form_id', $form->id)
            ->where('circle_id', $circle->id)
            ->delete();
    }
}

<?php

declare(strict_types=1);

namespace App\Services\Forms;

use App\Eloquents\Form;
use App\Eloquents\Circle;
use App\Eloquents\Answer;
use App\Eloquents\User;
use App\Events\Forms\AnswerAccepted;
use App\Events\Forms\AnswerReturned;
use App\Events\Forms\AnswerSubmitted;
use App\Exceptions\Forms\DuplicateAnswerException;
use App\Exceptions\Forms\StaleAnswerException;
use App\Services\Forms\AnswerDetailsService;
use App\Http\Requests\Forms\AnswerRequestInterface;
use App\Mail\Forms\AnswerConfirmationMailable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Throwable;

class AnswersService
{
    /**
     * @var AnswerDetailsService
     */
    private $answerDetailsService;

    public function __construct(AnswerDetailsService $answerDetailsService)
    {
        $this->answerDetailsService = $answerDetailsService;
    }

    /**
     * 企画所属者にメールを送信する
     *
     * @param Answer $answer
     * @param User $applicant
     * @param boolean $isEditedByStaff 回答がスタッフによって修正された場合はtrue
     * @return void
     */
    public function sendAll(Answer $answer, User $applicant, bool $isEditedByStaff = false)
    {
        // 企画にメールを送る
        $answer->loadMissing('form.questions');
        $answer->loadMissing('circle.users');
        $answer_details = $this->answerDetailsService->getAnswerDetailsByAnswer($answer);

        foreach ($answer->circle->users as $recipient) {
            $this->sendToUser(
                $answer->form,
                $answer->form->questions,
                $answer->circle,
                $applicant,
                $answer,
                $answer_details,
                $recipient,
                false,
                $isEditedByStaff
            );
        }

        // フォーム作成者にメールを送る
        /** @var Form */
        $form = $answer->form;
        /** @var Spatie\Activitylog\Models\Activity */
        $first_activity = $form->activities()->first();
        if (! empty($first_activity)) {
            /** @var User */
            $creator = $first_activity->causer;
            if (! empty($creator)) {
                $this->sendToUser(
                    $answer->form,
                    $answer->form->questions,
                    $answer->circle,
                    $applicant,
                    $answer,
                    $answer_details,
                    $creator,
                    true,
                    $isEditedByStaff
                );
            }
        }
    }

    /**
     * ユーザーにメールを送信する
     *
     * @param Form $form
     * @param Collection $questions
     * @param Circle $circle
     * @param User $applicant
     * @param Answer $answer
     * @param array $answer_details
     * @param User $recipient
     * @param boolean $isForStaff スタッフ用控えとして送信する場合はtrue
     * @param boolean $isEditedByStaff 回答がスタッフによって修正された場合はtrue
     * @return void
     */
    private function sendToUser(
        Form $form,
        Collection $questions,
        Circle $circle,
        User $applicant,
        Answer $answer,
        array $answer_details,
        User $recipient,
        bool $isForStaff,
        bool $isEditedByStaff
    ) {
        $subject = '申請「' . $form->name . '」を承りました';

        if ($isForStaff) {
            $subject = '【スタッフ用控え】' . $subject;
        }

        Mail::to($recipient)
            ->send(
                (new AnswerConfirmationMailable(
                    $form,
                    $questions,
                    $circle,
                    $applicant,
                    $answer,
                    $answer_details,
                    $isEditedByStaff
                ))
                    ->replyTo(config('portal.contact_email'), config('portal.admin_name'))
                    ->subject($subject)
            );
    }

    public function getAnswersByCircle(Form $form, Circle $circle)
    {
        return Answer::where('form_id', $form->id)->where('circle_id', $circle->id)->get();
    }

    /**
     * @param Form $form
     * @param Circle $circle
     * @param AnswerRequestInterface|null $request
     * @param User|null $actingUser requires_review なフォームで、リビジョンの提出者として記録するユーザー
     * @return Answer
     */
    public function createAnswer(
        Form $form,
        Circle $circle,
        ?AnswerRequestInterface $request = null,
        ?User $actingUser = null
    ) {
        try {
            $answer = DB::transaction(function () use ($form, $circle, $request, $actingUser) {
                if ($form->requires_review && $form->max_answers === 1) {
                    // フォームの行をロックし、同じ企画による2件目の回答が
                    // 同時に作成されないようにする
                    Form::whereKey($form->id)->lockForUpdate()->first();
                    if (Answer::where('form_id', $form->id)->where('circle_id', $circle->id)->exists()) {
                        throw new DuplicateAnswerException($form, $circle);
                    }
                }

                $answer_details = $this->answerDetailsService->getAnswerDetailsWithFilePathFromRequest($form, $request);

                $answer = Answer::create([
                    'form_id' => $form->id,
                    'circle_id' => $circle->id,
                ]);

                $this->answerDetailsService->updateAnswerDetails(
                    $form,
                    $answer,
                    $answer_details,
                    $form->requires_review
                );

                if ($form->requires_review) {
                    $this->recordSubmission($form, $answer, $actingUser, false);
                }

                return $answer;
            });
            $this->finishStoredFilesAfterOutermostCommit();
            return $answer;
        } catch (Throwable $e) {
            $this->answerDetailsService->discardNewlyStoredFiles();
            throw $e;
        }
    }

    /**
     * @param Form $form
     * @param Answer $answer
     * @param AnswerRequestInterface|null $request
     * @param User|null $actingUser requires_review なフォームで、リビジョンの提出者として記録するユーザー
     * @param bool $isStaffEdit スタッフによる既存回答の修正の場合はtrue。
     *  この場合、review_status は変更せずリビジョンのみ記録する
     * @param int|null $expectedLockVersion 画面表示時に読み込んだ lock_version。
     *  requires_review なフォームで、現在のDB上の値と一致しない場合は
     *  StaleAnswerException をthrowし、何も変更しない
     * @return Answer
     */
    public function updateAnswer(
        Form $form,
        Answer $answer,
        ?AnswerRequestInterface $request = null,
        ?User $actingUser = null,
        bool $isStaffEdit = false,
        ?int $expectedLockVersion = null
    ) {
        try {
            $updatedAnswer = DB::transaction(function () use (
                $form,
                $answer,
                $request,
                $actingUser,
                $isStaffEdit,
                $expectedLockVersion
            ) {
                $answer = Answer::whereKey($answer->id)->lockForUpdate()->firstOrFail();

                if ($form->requires_review) {
                    $isStale = $expectedLockVersion !== null && $answer->lock_version !== $expectedLockVersion;
                    // 企画側からの更新は、読み込んだ版の指定がない場合と、スタッフが完了にした後を拒否する
                    $isCircleUpdateRejected = !$isStaffEdit && (
                        $expectedLockVersion === null ||
                        $answer->review_status === Answer::REVIEW_STATUS_ACCEPTED
                    );
                    if ($isStale || $isCircleUpdateRejected) {
                        throw new StaleAnswerException($answer);
                    }
                }

                $answer_details = $this->answerDetailsService->getAnswerDetailsWithFilePathFromRequest($form, $request);

                $answer->update();
                $this->answerDetailsService->updateAnswerDetails(
                    $form,
                    $answer,
                    $answer_details,
                    $form->requires_review
                );

                if ($form->requires_review) {
                    $this->recordSubmission($form, $answer, $actingUser, $isStaffEdit);
                }

                return $answer;
            });
            $this->finishStoredFilesAfterOutermostCommit();
            return $updatedAnswer;
        } catch (Throwable $e) {
            $this->answerDetailsService->discardNewlyStoredFiles();
            throw $e;
        }
    }

    /**
     * スタッフが回答を完了にする
     *
     * @param Answer $answer
     * @param User $staff
     * @param int|null $expectedLockVersion 一致しない場合は StaleAnswerException をthrowする
     * @return Answer
     */
    public function acceptAnswer(Answer $answer, User $staff, ?int $expectedLockVersion = null): Answer
    {
        return DB::transaction(function () use ($answer, $staff, $expectedLockVersion) {
            $answer = Answer::whereKey($answer->id)->lockForUpdate()->firstOrFail();
            $form = $answer->form()->firstOrFail();

            if ($expectedLockVersion !== null && $answer->lock_version !== $expectedLockVersion) {
                throw new StaleAnswerException($answer);
            }

            $answer->update([
                'review_status' => Answer::REVIEW_STATUS_ACCEPTED,
                'review_note' => null,
                'reviewed_by' => $staff->id,
                'reviewed_at' => now(),
                'lock_version' => $answer->lock_version + 1,
            ]);

            if ($form->requires_review) {
                event(new AnswerAccepted(
                    $answer->circle_id,
                    $form->id,
                    $form->name,
                    $answer->id,
                    $answer->lock_version
                ));
            }

            return $answer;
        });
    }

    /**
     * スタッフが回答を差し戻す
     *
     * @param Answer $answer
     * @param User $staff
     * @param string $reason 差し戻し理由(必須)
     * @param int|null $expectedLockVersion 一致しない場合は StaleAnswerException をthrowする
     * @return Answer
     */
    public function returnAnswer(Answer $answer, User $staff, string $reason, ?int $expectedLockVersion = null): Answer
    {
        return DB::transaction(function () use ($answer, $staff, $reason, $expectedLockVersion) {
            $answer = Answer::whereKey($answer->id)->lockForUpdate()->firstOrFail();
            $form = $answer->form()->firstOrFail();

            if ($expectedLockVersion !== null && $answer->lock_version !== $expectedLockVersion) {
                throw new StaleAnswerException($answer);
            }

            $answer->update([
                'review_status' => Answer::REVIEW_STATUS_RETURNED,
                'review_note' => $reason,
                'reviewed_by' => $staff->id,
                'reviewed_at' => now(),
                'lock_version' => $answer->lock_version + 1,
            ]);

            if ($form->requires_review) {
                event(new AnswerReturned(
                    $answer->circle_id,
                    $form->id,
                    $form->name,
                    $answer->id,
                    $reason,
                    $answer->lock_version
                ));
            }

            return $answer;
        });
    }

    public function discardPendingUploads(): void
    {
        $this->answerDetailsService->discardNewlyStoredFiles();
    }

    /**
     * requires_review な回答の作成・更新時に、提出リビジョンを記録し、
     * スタッフによる修正でなければ確認待ち状態に戻す
     *
     * @param Form $form
     * @param Answer $answer 行ロック済のAnswer
     * @param User|null $actingUser
     * @param bool $isStaffEdit
     */
    private function recordSubmission(Form $form, Answer $answer, ?User $actingUser, bool $isStaffEdit): void
    {
        $nextRevision = (int)$answer->revisions()->max('revision') + 1;

        $answer->revisions()->create([
            'revision' => $nextRevision,
            'details' => $this->answerDetailsService->snapshotAnswerDetailsForRevision($answer),
            'submitted_by' => $actingUser?->id,
            'submitted_at' => now(),
        ]);

        $update = ['lock_version' => $answer->lock_version + 1];
        if (!$isStaffEdit) {
            $update['review_status'] = Answer::REVIEW_STATUS_SUBMITTED;
            $update['submitted_at'] = now();
            $update['review_note'] = null;
        }
        $answer->update($update);

        event(new AnswerSubmitted(
            $answer->circle_id,
            $form->id,
            $form->name,
            $answer->id,
            $nextRevision,
            $isStaffEdit
        ));
    }

    private function finishStoredFilesAfterOutermostCommit(): void
    {
        if (DB::transactionLevel() === 0) {
            $this->answerDetailsService->forgetNewlyStoredFiles();
            return;
        }
        DB::afterCommit(fn () => $this->answerDetailsService->forgetNewlyStoredFiles());
    }
}

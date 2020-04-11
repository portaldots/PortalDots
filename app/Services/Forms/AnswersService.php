<?php

declare(strict_types=1);

namespace App\Services\Forms;

use App\Eloquents\Form;
use App\Eloquents\Circle;
use App\Eloquents\Answer;
use App\Eloquents\AnswerDetail;
use App\Eloquents\User;
use App\Services\Forms\AnswerDetailsService;
use App\Http\Requests\Forms\AnswerRequestInterface;
use App\Mail\Forms\AnswerConfirmationMailable;
use Illuminate\Database\Eloquent\Collection;
use DB;
use Illuminate\Support\Facades\Mail;

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
        $creator = User::find($answer->form->created_by);
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

    public function createAnswer(Form $form, Circle $circle, ?AnswerRequestInterface $request = null)
    {
        return DB::transaction(function () use ($form, $circle, $request) {
            $answer_details = $this->answerDetailsService->getAnswerDetailsWithFilePathFromRequest($form, $request);

            $answer = Answer::create([
                'form_id' => $form->id,
                'circle_id' => $circle->id,
            ]);

            $this->answerDetailsService->updateAnswerDetails($form, $answer, $answer_details);

            return $answer;
        });
    }

    public function updateAnswer(Form $form, Answer $answer, ?AnswerRequestInterface $request = null)
    {
        return DB::transaction(function () use ($form, $answer, $request) {
            $answer_details = $this->answerDetailsService->getAnswerDetailsWithFilePathFromRequest($form, $request);

            $answer->update();
            $this->answerDetailsService->updateAnswerDetails($form, $answer, $answer_details);

            return $answer;
        });
    }
}

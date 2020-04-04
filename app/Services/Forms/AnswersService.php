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
    private $answerDetailsService;

    public function __construct(AnswerDetailsService $answerDetailsService)
    {
        $this->answerDetailsService = $answerDetailsService;
    }

    /**
     * 企画所属者にメールを送信する
     *
     * @return void
     */
    public function sendAll(Answer $answer, User $applicant)
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
                $recipient
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
                $creator
            );
        }
    }

    private function sendToUser(
        Form $form,
        Collection $questions,
        Circle $circle,
        User $applicant,
        Answer $answer,
        array $answer_details,
        User $recipient
    ) {
        Mail::to($recipient)
            ->send(
                (new AnswerConfirmationMailable($form, $questions, $circle, $applicant, $answer, $answer_details))
                    ->replyTo(config('portal.contact_email'), config('portal.admin_name'))
                    ->subject('申請「' . $form->name . '」を承りました')
            );
    }

    public function getAnswersByCircle(Form $form, Circle $circle)
    {
        return Answer::where('form_id', $form->id)->where('circle_id', $circle->id)->get();
    }

    public function createAnswer(Form $form, Circle $circle, AnswerRequestInterface $request)
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

    public function updateAnswer(Form $form, Answer $answer, AnswerRequestInterface $request)
    {
        return DB::transaction(function () use ($form, $answer, $request) {
            $answer_details = $this->answerDetailsService->getAnswerDetailsWithFilePathFromRequest($form, $request);

            $answer->update();
            $this->answerDetailsService->updateAnswerDetails($form, $answer, $answer_details);

            return $answer;
        });
    }
}

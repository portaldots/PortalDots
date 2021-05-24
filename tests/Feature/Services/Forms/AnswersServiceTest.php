<?php

namespace Tests\Feature\Services\Forms;

use App\Eloquents\Answer;
use App\Eloquents\Circle;
use App\Eloquents\Form;
use App\Eloquents\Question;
use App\Eloquents\User;
use App\Mail\Forms\AnswerConfirmationMailable;
use App\Services\Forms\AnswersService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class AnswersServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var AnswersSerivce
     */
    private $answersSerivce;

    public function setUp(): void
    {
        parent::setUp();
        $this->answersSerivce = App::make(AnswersService::class);
    }

    /**
     * @test
     */
    public function sendAll()
    {
        /** @var Collection */
        $staff = factory(User::class, 3)->state('staff')->create();
        $form_creator = $staff[1];

        // スタッフとしてフォームを作成する（アクティビティログにフォーム作成者を残す）
        Auth::login($form_creator);

        /** @var Form */
        $form = factory(Form::class)->create();

        // 異なるユーザーがフォームを編集する
        Auth::login($staff[0]);
        $form->name = 'フォーム情報編集 1回目';
        $form->save();
        Auth::login($staff[2]);
        $form->name = 'フォーム情報編集 2回目';
        $form->save();
        Auth::logout();

        /** @var Illuminate\Database\Eloquent\Collection */
        $circle_members = factory(User::class, 3)->create();

        Auth::login($circle_members[1]);

        /** @var Circle */
        $circle = factory(Circle::class)->create();

        /** @var Illuminate\Database\Eloquent\Collection */
        $questions = factory(Question::class, 5)->create([
            'form_id' => $form->id,
            'is_required' => false
        ]);

        $circle->users()->saveMany($circle_members);

        $form->questions()->saveMany($questions);

        /** @var Answer */
        $answer = factory(Answer::class)->create([
            'form_id' => $form->id,
            'circle_id' => $circle->id
        ]);

        Mail::fake();
        $this->answersSerivce->sendAll($answer, $circle_members[1], false);

        foreach ($circle_members as $recipient) {
            Mail::assertSent(AnswerConfirmationMailable::class, function ($mail) use ($recipient) {
                return $mail->hasTo($recipient->email);
            });
        }

        // フォーム作成者にもメールが届く
        Mail::assertSent(AnswerConfirmationMailable::class, function ($mail) use ($form_creator) {
            return $mail->hasTo($form_creator->email);
        });

        // フォームを編集したユーザーにはメールが届かない
        Mail::assertNotSent(AnswerConfirmationMailable::class, function ($mail) use ($staff) {
            return $mail->hasTo($staff[0]->email);
        });
        Mail::assertNotSent(AnswerConfirmationMailable::class, function ($mail) use ($staff) {
            return $mail->hasTo($staff[2]->email);
        });
    }
}

<?php

namespace Tests\Feature\Services\Forms;

use App\Contracts\FormAnswerUrl;
use App\Eloquents\Circle;
use App\Eloquents\Form;
use App\Eloquents\User;
use App\Events\Forms\AnswerReturned;
use App\Mail\Forms\AnswerReturnedMailable;
use App\Services\Forms\AnswersService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AnswerReturnedNotificationTest extends TestCase
{
    use RefreshDatabase;

    private AnswersService $answersService;
    private User $staff;

    public function setUp(): void
    {
        parent::setUp();
        $this->answersService = App::make(AnswersService::class);
        $this->staff = factory(User::class)->states('staff')->create();
    }

    private function memberOf(Circle $circle): User
    {
        $user = factory(User::class)->create();
        $user->circles()->attach($circle->id, ['is_leader' => true]);
        return $user;
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 差し戻しでその企画のメンバー全員に送信され他の企画には送信されない()
    {
        Mail::fake();

        $form = factory(Form::class)->create(['requires_review' => true, 'name' => '出店計画書']);
        $circle = factory(Circle::class)->create();
        $memberA = $this->memberOf($circle);
        $memberB = $this->memberOf($circle);
        $otherCircle = factory(Circle::class)->create();
        $this->memberOf($otherCircle);

        Auth::login($memberA);
        $answer = $this->answersService->createAnswer($form, $circle, null, $memberA);
        $this->answersService->returnAnswer($answer, $this->staff, '記入内容に不備があります');

        Mail::assertSent(AnswerReturnedMailable::class, function ($mail) use ($memberA) {
            return $mail->hasTo($memberA->email);
        });
        Mail::assertSent(AnswerReturnedMailable::class, function ($mail) use ($memberB) {
            return $mail->hasTo($memberB->email);
        });
        Mail::assertSent(AnswerReturnedMailable::class, 2);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 再度差し戻すと再送信され同じイベントを再発行しても2通目は送られない()
    {
        Mail::fake();

        $form = factory(Form::class)->create(['requires_review' => true, 'name' => '出店計画書']);
        $circle = factory(Circle::class)->create();
        $member = $this->memberOf($circle);

        Auth::login($member);
        $answer = $this->answersService->createAnswer($form, $circle, null, $member);
        $this->answersService->returnAnswer($answer, $this->staff, '不備1');
        $answer->refresh();
        $this->answersService->updateAnswer($form, $answer, null, $member, false, $answer->lock_version);
        $answer->refresh();
        $this->answersService->returnAnswer($answer, $this->staff, '不備2');

        // lock_versionが変わった2回目の差し戻しでも送信される
        Mail::assertSent(AnswerReturnedMailable::class, 2);

        // 同じイベントを再発行しても重複して送信されない
        $answer->refresh();
        $event = new AnswerReturned($circle->id, $form->id, $form->name, $answer->id, '不備2', $answer->lock_version);
        event($event);
        event($event);

        Mail::assertSent(AnswerReturnedMailable::class, 2);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 差し戻しの回答先URLを差し替えられる()
    {
        Mail::fake();
        App::instance(FormAnswerUrl::class, new class implements FormAnswerUrl {
            public function for(int $circleId, int $formId, ?int $answerId): string
            {
                return "https://example.test/cases/{$circleId}/forms/{$formId}";
            }
        });

        $form = factory(Form::class)->create(['requires_review' => true]);
        $circle = factory(Circle::class)->create();
        $member = $this->memberOf($circle);
        Auth::login($member);
        $answer = $this->answersService->createAnswer($form, $circle, null, $member);
        $this->answersService->returnAnswer($answer, $this->staff, '修正してください');

        Mail::assertSent(AnswerReturnedMailable::class, fn ($mail) =>
            $mail->url === "https://example.test/cases/{$circle->id}/forms/{$form->id}");
    }
}

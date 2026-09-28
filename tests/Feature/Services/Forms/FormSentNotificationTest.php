<?php

namespace Tests\Feature\Services\Forms;

use App\Eloquents\Circle;
use App\Eloquents\Form;
use App\Eloquents\User;
use App\Mail\Forms\FormSentMailable;
use App\Services\Forms\FormAssignmentsService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class FormSentNotificationTest extends TestCase
{
    use RefreshDatabase;

    private FormAssignmentsService $formAssignmentsService;
    private User $staff;

    public function setUp(): void
    {
        parent::setUp();
        $this->formAssignmentsService = App::make(FormAssignmentsService::class);
        $this->staff = factory(User::class)->states('staff')->create();
    }

    private function memberOf(Circle $circle): User
    {
        $user = factory(User::class)->create();
        $user->circles()->attach($circle->id, ['is_leader' => true]);
        return $user;
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 送付で企画のメンバーに1通ずつ送信され同じ期限での再送信は送信しない()
    {
        Mail::fake();

        $form = factory(Form::class)->create(['audience' => 'selected', 'name' => '出店計画書']);
        $circle = factory(Circle::class)->create();
        $member = $this->memberOf($circle);
        $dueAt = new Carbon('2026-10-08 23:59:59');

        $this->formAssignmentsService->assignToCircles($form, [$circle->id], $dueAt, $this->staff);

        Mail::assertSent(FormSentMailable::class, function ($mail) use ($member) {
            return $mail->hasTo($member->email);
        });
        Mail::assertSent(FormSentMailable::class, 1);

        // 同じ期限での再送信では送信されない
        $this->formAssignmentsService->assignToCircles($form, [$circle->id], $dueAt, $this->staff);

        Mail::assertSent(FormSentMailable::class, 1);
    }
}

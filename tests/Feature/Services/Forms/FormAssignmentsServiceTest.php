<?php

namespace Tests\Feature\Services\Forms;

use App\Eloquents\Circle;
use App\Eloquents\Form;
use App\Eloquents\User;
use App\Services\Forms\FormAssignmentsService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

class FormAssignmentsServiceTest extends TestCase
{
    use RefreshDatabase;

    private FormAssignmentsService $formAssignmentsService;
    private Form $form;
    private User $staff;

    public function setUp(): void
    {
        parent::setUp();
        $this->formAssignmentsService = App::make(FormAssignmentsService::class);
        $this->form = factory(Form::class)->create(['audience' => 'selected']);
        $this->staff = factory(User::class)->states('staff')->create();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function assignToCircles_複数の企画へ共通の期限で送付できる()
    {
        $circleA = factory(Circle::class)->create();
        $circleB = factory(Circle::class)->create();
        $dueAt = new Carbon('2026-10-08 23:59:59');

        $this->formAssignmentsService->assignToCircles(
            $this->form,
            [$circleA->id, $circleB->id],
            $dueAt,
            $this->staff
        );

        $this->assertDatabaseCount('form_assignments', 2);
        $this->assertDatabaseHas('form_assignments', [
            'form_id' => $this->form->id,
            'circle_id' => $circleA->id,
            'due_at' => '2026-10-08 23:59:59',
            'assigned_by' => $this->staff->id,
        ]);
        $this->assertDatabaseHas('form_assignments', [
            'form_id' => $this->form->id,
            'circle_id' => $circleB->id,
            'due_at' => '2026-10-08 23:59:59',
            'assigned_by' => $this->staff->id,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function assignToCircles_再度送付しても重複せず期限だけ更新される()
    {
        $circle = factory(Circle::class)->create();

        $this->formAssignmentsService->assignToCircles(
            $this->form,
            [$circle->id],
            new Carbon('2026-10-01 00:00:00'),
            $this->staff
        );
        $this->formAssignmentsService->assignToCircles(
            $this->form,
            [$circle->id],
            new Carbon('2026-10-08 23:59:59'),
            $this->staff
        );

        $this->assertDatabaseCount('form_assignments', 1);
        $this->assertDatabaseHas('form_assignments', [
            'form_id' => $this->form->id,
            'circle_id' => $circle->id,
            'due_at' => '2026-10-08 23:59:59',
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function assignToCircles_承認済みではない企画は送付されない()
    {
        $notApproved = factory(Circle::class)->states('notSubmitted')->create();

        $this->formAssignmentsService->assignToCircles(
            $this->form,
            [$notApproved->id],
            null,
            $this->staff
        );

        $this->assertDatabaseCount('form_assignments', 0);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function updateDueDate_送付済みの企画の期限を変更できる()
    {
        $circle = factory(Circle::class)->create();
        $this->formAssignmentsService->assignToCircles($this->form, [$circle->id], null, $this->staff);

        $this->formAssignmentsService->updateDueDate($this->form, $circle, new Carbon('2026-11-01 00:00:00'));

        $this->assertDatabaseHas('form_assignments', [
            'form_id' => $this->form->id,
            'circle_id' => $circle->id,
            'due_at' => '2026-11-01 00:00:00',
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function unassign_タグのないselectedフォームから送付を取り消すと回答対象から外れる()
    {
        $circle = factory(Circle::class)->create();
        $this->formAssignmentsService->assignToCircles($this->form, [$circle->id], null, $this->staff);

        $this->assertTrue(Form::byCircle($circle)->whereKey($this->form->id)->exists());

        $this->formAssignmentsService->unassign($this->form, $circle);

        $this->assertDatabaseCount('form_assignments', 0);
        $this->assertFalse(Form::byCircle($circle)->whereKey($this->form->id)->exists());
    }
}

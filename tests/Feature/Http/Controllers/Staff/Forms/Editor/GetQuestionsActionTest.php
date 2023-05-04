<?php

namespace Tests\Feature\Http\Controllers\Staff\Forms\Editor;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Eloquents\User;
use App\Eloquents\Form;
use App\Eloquents\ParticipationType;
use App\Eloquents\Permission;
use App\Eloquents\Question;

class GetQuestionsActionTest extends TestCase
{
    use RefreshDatabase;

    private ?Form $form;
    private ?array $questions;
    private ?User $staff;

    public function setUp(): void
    {
        parent::setUp();

        $this->form = factory(Form::class)->create();
        $this->questions = [
            factory(Question::class)->create(['priority' => 2, 'form_id' => $this->form->id]),
            factory(Question::class)->create(['priority' => 1, 'form_id' => $this->form->id]),
            factory(Question::class)->create(['priority' => 4, 'form_id' => $this->form->id]),
            factory(Question::class)->create(['priority' => 3, 'form_id' => $this->form->id]),
        ];
        $this->staff = factory(User::class)->states('staff')->create();
    }

    /**
     * @test
     */
    public function priority順の設問一覧が出力される()
    {
        Permission::create(['name' => 'staff.forms.edit']);
        $this->staff->syncPermissions(['staff.forms.edit']);

        $response = $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->get(route('staff.forms.editor.api', ['form' => $this->form]) . '/get_questions');

        $response->assertStatus(200);
        $this->assertSame(1, $response[0]['priority']);
        $this->assertSame(2, $response[1]['priority']);
        $this->assertSame(3, $response[2]['priority']);
        $this->assertSame(4, $response[3]['priority']);

        $this->assertSame($this->questions[1]->name, $response[0]['name']);
    }

    /**
     * @test
     */
    public function 参加登録フォームの場合は参加登録フォーム固有の設問も出力される()
    {
        Permission::create(['name' => 'staff.forms.edit']);
        $this->staff->syncPermissions(['staff.forms.edit']);

        ParticipationType::factory()->create([
            'form_id' => $this->form->id
        ]);

        $response = $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->get(route('staff.forms.editor.api', ['form' => $this->form]) . '/get_questions');

        $response->assertStatus(200);
        $this->assertTrue($response[0]['is_permanent']);
    }
}

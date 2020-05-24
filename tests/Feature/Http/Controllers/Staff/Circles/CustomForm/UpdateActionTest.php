<?php

namespace Tests\Feature\Http\Controllers\Staff\Circles\CustomForm;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Jackiedo\DotenvEditor\DotenvEditor;
use Tests\TestCase;
use App\Eloquents\User;
use App\Eloquents\Form;
use App\Eloquents\CustomForm;

class UpdateActionTest extends TestCase
{
    use RefreshDatabase;

    private $form;
    private $custom_form;
    private $staff;

    public function setUp(): void
    {
        parent::setUp();

        $this->form = factory(Form::class)->create([
            'name' => '企画参加登録',
        ]);
        $this->custom_form = factory(CustomForm::class)->create([
            'type' => 'circle',
            'form_id' => $this->form->id,
        ]);
        $this->staff = factory(User::class)->states('staff')->create();
    }

    /**
     * @test
     */
    public function カスタムフォームの設定を更新できる()
    {
        $this->mock(DotenvEditor::class, function ($mock) {
            $mock->shouldReceive('keyExists')->once()->with('APP_NOT_INSTALLED')->andReturn(true);
            $mock->shouldReceive('getValue')->once()->with('APP_NOT_INSTALLED')->andReturn('false');
            $mock->shouldReceive('setKey')->once()->withArgs(function ($key, $value) {
                return $key === 'PORTAL_USERS_NUMBER_TO_SUBMIT_CIRCLE' && $value === 6;
            })->andReturn(true);
            $mock->shouldReceive('save')->once();
        });

        $response = $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->patch(route('staff.circles.custom_form.update'), [
                'name' => '新しいフォーム名', // カスタムフォームではフォーム名の変更は不可
                'open_at' => '2000-12-31T12:12',
                'close_at' => '2040-12-31T11:15',
                'users_number_to_submit_circle' => '6',
                'is_public' => '1',
            ]);

        $response->assertRedirect(route('staff.circles.custom_form.index'));

        $this->assertDatabaseHas('forms', [
            'name' => '企画参加登録',    // フォーム名は変化していない
            'open_at' => '2000-12-31T12:12',
            'close_at' => '2040-12-31T11:15',
            'is_public' => 1,
        ]);
    }

    /**
     * @test
     */
    public function 受付終了日時が開始日時より後でない場合はエラー()
    {
        $response = $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->patch(route('staff.circles.custom_form.update'), [
                'open_at' => '2040-12-31T12:12',
                'close_at' => '2040-12-31T12:11',
                'users_number_to_submit_circle' => '6',
                'is_public' => '1',
            ]);

        $response->assertSessionHasErrors(['close_at']);
    }
}

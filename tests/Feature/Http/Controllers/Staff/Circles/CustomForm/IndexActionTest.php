<?php

namespace Tests\Feature\Http\Controllers\Staff\Circles\CustomForm;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Eloquents\User;
use App\Eloquents\Form;
use App\Eloquents\CustomForm;

class IndexActionTest extends TestCase
{
    use RefreshDatabase;

    private $staff;

    public function setUp(): void
    {
        parent::setUp();

        $this->staff = factory(User::class)->states('staff')->create();
    }

    /**
     * @test
     */
    public function 企画参加登録機能が無効の状態()
    {
        $this->assertDatabaseMissing('forms', [
            'name' => '企画参加登録',
        ]);

        $this->assertDatabaseMissing('custom_forms', [
            'type' => 'circle',
        ]);

        $response = $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->get(route('staff.circles.custom_form.index'));

        $response->assertStatus(200);
        $response->assertSee('無効');
    }

    /**
     * @test
     */
    public function 企画参加登録機能が有効の状態()
    {
        $form = factory(Form::class)->create();
        $customForm = factory(CustomForm::class)->create([
            'type' => 'circle',
            'form_id' => $form->id,
        ]);

        $response = $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->get(route('staff.circles.custom_form.index'));

        $response->assertStatus(200);
        $response->assertDontSee('無効');
    }
}

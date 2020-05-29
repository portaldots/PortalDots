<?php

namespace Tests\Feature\Http\Controllers\Staff\Circles\CustomForm;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Eloquents\User;
use App\Eloquents\CustomForm;

class StoreActionTest extends TestCase
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
    public function 企画参加登録機能を有効にできる()
    {
        $this->assertDatabaseMissing('forms', [
            'name' => '企画参加登録',
        ]);

        $this->assertDatabaseMissing('custom_forms', [
            'type' => 'circle',
        ]);

        $response = $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->post(route('staff.circles.custom_form.store'));

        $response->assertRedirect(route('staff.circles.custom_form.index'));

        $this->assertDatabaseHas('forms', [
            'name' => '企画参加登録',
        ]);

        $this->assertDatabaseHas('custom_forms', [
            'type' => 'circle',
        ]);
    }

    /**
     * @test
     */
    public function 企画参加登録機能が既に有効になっている場合はエラー()
    {
        $this->assertSame(0, CustomForm::count());

        $response = $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->post(route('staff.circles.custom_form.store'));

        $this->assertSame(1, CustomForm::count());

        // もう一度POSTしてもカスタムフォームは新しく作成されない
        $response = $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->post(route('staff.circles.custom_form.store'));

        $this->assertSame(1, CustomForm::count());
    }
}

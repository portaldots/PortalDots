<?php

namespace Tests\Feature\Http\Controllers\Staff\Circles\CustomForm;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Eloquents\User;
use App\Eloquents\Form;
use App\Eloquents\CustomForm;
use App\Eloquents\Permission;

class IndexActionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var User
     */
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
        Permission::create(['name' => 'staff.circles.custom_form']);
        $this->staff->syncPermissions(['staff.circles.custom_form']);

        $this->assertDatabaseMissing('forms', [
            'name' => '企画参加登録',
        ]);

        $this->assertDatabaseMissing('custom_forms', [
            'type' => 'circle',
        ]);

        $response = $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->get(route('staff.circles.custom_form.index'));

        $response->assertOk();
        $response->assertSee('無効');
    }

    /**
     * @test
     */
    public function 企画参加登録機能が有効の状態()
    {
        Permission::create(['name' => 'staff.circles.custom_form']);
        $this->staff->syncPermissions(['staff.circles.custom_form']);

        $form = factory(Form::class)->create();
        factory(CustomForm::class)->create([
            'type' => 'circle',
            'form_id' => $form->id,
        ]);

        $response = $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->get(route('staff.circles.custom_form.index'));

        $response->assertOk();
        $response->assertDontSee('無効');
    }

    /**
     * @test
     */
    public function 権限がない場合は企画参加登録設定にアクセスできない()
    {
        $form = factory(Form::class)->create();
        factory(CustomForm::class)->create([
            'type' => 'circle',
            'form_id' => $form->id,
        ]);

        $response = $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->get(route('staff.circles.custom_form.index'));

        $response->assertForbidden();
    }
}

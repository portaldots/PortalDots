<?php

namespace Tests\Feature\Http\Controllers\Staff\Forms;

use App\Eloquents\Form;
use App\Eloquents\User;
use App\Services\Forms\FormsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Mockery;

class CopyActionTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->form = factory(Form::class)->create();
        $this->form_copy = factory(Form::class)->create();
        $this->staff = factory(User::class)->states('staff')->create();
    }

    /**
     * @test
     */
    public function FormsServiceのcopyFormが呼び出される()
    {
        $this->mock(FormsService::class, function ($mock) {
            $mock->shouldReceive('copyForm')->once()->with(Mockery::on(function ($arg) {
                return $this->form->id === $arg->id && $this->form->name === $arg->name;
            }), Mockery::on(function ($arg) {
                return $this->staff->id === $arg->id && $this->staff->name === $arg->name;
            }))->andReturn($this->form_copy);
        });

        $response = $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->post(route('staff.forms.copy', ['form' => $this->form]));

        $response->assertRedirect(route('staff.forms.index'));
    }
}

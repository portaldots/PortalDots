<?php

namespace Tests\Feature\Http\Controllers\Staff\Circles;

use App\Eloquents\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CreateActionTest extends TestCase
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
    public function 企画の新規作成フォームが表示される()
    {
        $responce = $this->actingAs($this->staff)
                        ->withSession(['staff_authorized' => true])
                        ->get(
                            route('staff.circles.create')
                        );

        $responce->assertOk();
    }
}

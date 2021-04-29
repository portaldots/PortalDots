<?php

namespace Tests\Feature\Http\Controllers\Staff\Circles;

use App\Eloquents\Permission;
use App\Eloquents\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CreateActionTest extends TestCase
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
    public function 企画の新規作成フォームが表示される()
    {
        Permission::create(['name' => 'staff.circles.edit']);
        $this->staff->syncPermissions('staff.circles.edit');

        $responce = $this->actingAs($this->staff)
                        ->withSession(['staff_authorized' => true])
                        ->get(
                            route('staff.circles.create')
                        );

        $responce->assertOk();
    }

    /**
     * @test
     */
    public function 権限がない場合は企画の新規作成フォームが表示されない()
    {
        $responce = $this->actingAs($this->staff)
                        ->withSession(['staff_authorized' => true])
                        ->get(
                            route('staff.circles.create')
                        );

        $responce->assertForbidden();
    }
}

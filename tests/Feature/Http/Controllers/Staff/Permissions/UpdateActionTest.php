<?php

namespace Tests\Feature\Http\Controllers\Staff\Permissions;

use App\Eloquents\Permission;
use App\Eloquents\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateActionTest extends TestCase
{
    use RefreshDatabase;

    /** @var User */
    private $staff;

    public function setUp(): void
    {
        parent::setUp();
        $this->staff = factory(User::class)->states('staff')->create();
    }

    /**
     * @test
     */
    public function 権限設定を更新できる()
    {
        Permission::create(['name' => 'staff.permissions.edit']);
        $this->staff->syncPermissions(['staff.permissions.edit']);

        /** @var User */
        $target_user = factory(User::class)->create();

        $this->assertEquals(0, $target_user->permissions()->count());

        $data = [
            'permissions' => [
                'staff.users.read,edit',
                'staff.pages.read,export',
            ]
        ];

        $response = $this
            ->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->patch(
                route('staff.permissions.update', [
                    'user' => $target_user,
                ]),
                $data
            );

        $response->assertRedirect(route('staff.permissions.edit', ['user' => $target_user]));

        $target_user->refresh();
        $this->assertEquals(2, $target_user->permissions()->count());
        $this->assertTrue($target_user->can('staff.users.read'));
        $this->assertTrue($target_user->can('staff.users.edit'));
        $this->assertTrue($target_user->can('staff.pages.read'));
        $this->assertTrue($target_user->can('staff.pages.export'));
    }

    /**
     * @test
     */
    public function 存在しない権限を指定するとエラーになる()
    {
        Permission::create(['name' => 'staff.permissions.edit']);
        $this->staff->syncPermissions(['staff.permissions.edit']);

        /** @var User */
        $target_user = factory(User::class)->create();

        $this->assertEquals(0, $target_user->permissions()->count());

        $data = [
            'permissions' => [
                'staff.users.read,edit',
                'this.permission.is.not.defined',
            ]
        ];

        $response = $this
            ->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->patch(
                route('staff.permissions.update', [
                    'user' => $target_user,
                ]),
                $data
            );

        $response->assertSessionHasErrors();

        $target_user->refresh();
        $this->assertEquals(0, $target_user->permissions()->count());
    }

    /**
     * @test
     */
    public function 権限がない場合は権限設定を更新できない()
    {
        /** @var User */
        $target_user = factory(User::class)->create();

        $this->assertEquals(0, $target_user->permissions()->count());

        $data = [
            'permissions' => [
                'staff.users.read,edit',
                'staff.pages.read,export',
            ]
        ];

        $response = $this
            ->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->patch(
                route('staff.permissions.update', [
                    'user' => $target_user,
                ]),
                $data
            );

        $response->assertForbidden();

        $target_user->refresh();
        $this->assertEquals(0, $target_user->permissions()->count());
    }
}

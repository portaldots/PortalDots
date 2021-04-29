<?php

namespace Tests\Feature\Http\Controllers\Staff\Users;

use App\Eloquents\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateActionTest extends TestCase
{
    use RefreshDatabase;

    /** @var User */
    private $staff;

    /** @var User */
    private $admin;

    public function setUp(): void
    {
        parent::setUp();
        $this->staff = factory(User::class)->states('staff')->create();
        $this->admin = factory(User::class)->states('admin')->create();
    }

    /**
     * @test
     */
    public function ユーザー情報を更新できる()
    {
        /** @var User */
        $target_user = factory(User::class)->create();

        $this->assertFalse($target_user->is_staff);
        $this->assertFalse($target_user->is_admin);

        $data = [
            'student_id' => '123updated',
            'name' => '更新　され太',
            'name_yomi' => 'コウシン された',
            'email' => 'updated@example.com',
            'tel' => '000-updated-000',
            'notes' => '新しいメモ',
            'user_type' => 'staff',
        ];

        $response = $this
            ->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->patch(
                route('staff.users.update', [
                    'user' => $target_user,
                ]),
                $data
            );

        $response->assertRedirect(route('staff.users.edit', ['user' => $target_user]));

        $target_user->refresh();
        $this->assertEquals('123UPDATED', $target_user->student_id);
        $this->assertEquals('更新 され太', $target_user->name);
        $this->assertEquals('こうしん された', $target_user->name_yomi);
        $this->assertEquals($data['email'], $target_user->email);
        $this->assertEquals($data['tel'], $target_user->tel);
        $this->assertEquals($data['notes'], $target_user->notes);
        $this->assertTrue($target_user->is_staff);
        $this->assertFalse($target_user->is_admin);
    }

    /**
     * @test
     */
    public function スタッフ自身のユーザー種別は変更できない()
    {
        $data = [
            'student_id' => '123updated',
            'name' => '更新　され太',
            'name_yomi' => 'コウシン された',
            'email' => 'updated@example.com',
            'tel' => '000-updated-000',
            'notes' => '新しいメモ',
            'user_type' => 'normal',
        ];

        $response = $this
            ->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->patch(
                route('staff.users.update', [
                    'user' => $this->staff,
                ]),
                $data
            );

        $response->assertSessionHasErrors(['user_type']);

        $this->staff->refresh();
        $this->assertTrue($this->staff->is_staff);
    }

    /**
     * @test
     */
    public function 管理者であっても自身のユーザー種別は変更できない()
    {
        $data = [
            'student_id' => '123updated',
            'name' => '更新　され太',
            'name_yomi' => 'コウシン された',
            'email' => 'updated@example.com',
            'tel' => '000-updated-000',
            'notes' => '新しいメモ',
            'user_type' => 'staff',
        ];

        $response = $this
            ->actingAs($this->admin)
            ->withSession(['staff_authorized' => true])
            ->patch(
                route('staff.users.update', [
                    'user' => $this->admin,
                ]),
                $data
            );

        $response->assertSessionHasErrors(['user_type']);

        $this->admin->refresh();
        $this->assertTrue($this->admin->is_admin);
    }

    /**
     * @test
     */
    public function 管理者であれば他のユーザーを管理者にできる()
    {
        /** @var User */
        $target_user = factory(User::class)->create();

        $this->assertFalse($target_user->is_admin);

        $data = [
            'student_id' => '123updated',
            'name' => '更新　され太',
            'name_yomi' => 'コウシン された',
            'email' => 'updated@example.com',
            'tel' => '000-updated-000',
            'notes' => '新しいメモ',
            'user_type' => 'admin',
        ];

        $response = $this
            ->actingAs($this->admin)
            ->withSession(['staff_authorized' => true])
            ->patch(
                route('staff.users.update', [
                    'user' => $target_user,
                ]),
                $data
            );

        $response->assertRedirect(route('staff.users.edit', ['user' => $target_user]));

        $target_user->refresh();
        $this->assertTrue($target_user->is_admin);
    }

    /**
     * @test
     */
    public function 管理者ではない場合は他のユーザーを管理者にできない()
    {
        /** @var User */
        $target_user = factory(User::class)->create();

        $this->assertFalse($target_user->is_admin);

        $data = [
            'student_id' => '123updated',
            'name' => '更新　され太',
            'name_yomi' => 'コウシン された',
            'email' => 'updated@example.com',
            'tel' => '000-updated-000',
            'notes' => '新しいメモ',
            'user_type' => 'admin',
        ];

        $response = $this
            ->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->patch(
                route('staff.users.update', [
                    'user' => $target_user,
                ]),
                $data
            );

        $response->assertSessionHasErrors(['user_type']);

        $target_user->refresh();
        $this->assertFalse($target_user->is_admin);
    }

    /**
     * @test
     */
    public function 管理者ではない場合は他の管理者のユーザー種別を変更できない()
    {
        $this->assertTrue($this->admin->is_admin);

        $data = [
            'student_id' => '123updated',
            'name' => '更新　され太',
            'name_yomi' => 'コウシン された',
            'email' => 'updated@example.com',
            'tel' => '000-updated-000',
            'notes' => '新しいメモ',
            'user_type' => 'staff',
        ];

        $response = $this
            ->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->patch(
                route('staff.users.update', [
                    'user' => $this->admin,
                ]),
                $data
            );

        $response->assertSessionHasErrors(['user_type']);

        $this->admin->refresh();
        $this->assertTrue($this->admin->is_admin);
    }

    /**
     * @test
     */
    public function 一般ユーザーはユーザー情報の更新はできない()
    {
        /** @var User */
        $user = factory(User::class)->create();

        /** @var User */
        $target_user = factory(User::class)->create();

        $data = [
            'student_id' => '123updated',
            'name' => '更新　され太',
            'name_yomi' => 'コウシン された',
            'email' => 'updated@example.com',
            'tel' => '000-updated-000',
            'notes' => '新しいメモ',
        ];

        $this
            ->actingAs($user)
            ->patch(
                route('staff.users.update', [
                    'user' => $target_user,
                ]),
                $data
            )
            ->assertForbidden();
    }
}

<?php

namespace Tests\Feature\Http\Controllers\Staff\Users;

use App\Eloquents\Permission;
use App\Eloquents\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EditActionTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function 学籍番号がないユーザーの編集画面が表示される()
    {
        config([
            'portal.auth.student_id' => false,
            'portal.auth.univemail' => false,
        ]);

        /** @var User */
        $staff = factory(User::class)->states('staff')->create();
        Permission::create(['name' => 'staff.users.edit']);
        $staff->syncPermissions(['staff.users.edit']);

        /** @var User */
        $targetUser = factory(User::class)->create([
            'student_id' => null,
            'univemail_local_part' => '',
            'univemail_domain_part' => '',
        ]);

        $response = $this
            ->actingAs($staff)
            ->withSession(['staff_authorized' => true])
            ->get(route('staff.users.edit', ['user' => $targetUser]));

        $response->assertOk();
    }
}

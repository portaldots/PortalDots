<?php

namespace Tests\Feature\Http\Controllers\Staff\Users;

use App\Eloquents\Permission;
use App\Eloquents\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndexActionTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function 学籍番号がないユーザーがいても一覧が表示される()
    {
        config([
            'portal.auth.student_id' => false,
            'portal.auth.univemail' => false,
        ]);

        /** @var User */
        $staff = factory(User::class)->states('staff')->create();
        Permission::create(['name' => 'staff.users.read']);
        $staff->syncPermissions(['staff.users.read']);

        $noStudentIdUser = factory(User::class)->create([
            'student_id' => null,
            'univemail_local_part' => '',
            'univemail_domain_part' => '',
        ]);

        $response = $this
            ->actingAs($staff)
            ->withSession(['staff_authorized' => true])
            ->get(route('staff.users.index'));

        $response->assertOk();

        $apiResponse = $this
            ->actingAs($staff)
            ->withSession(['staff_authorized' => true])
            ->getJson(route('staff.users.api'));

        $apiResponse->assertOk();
        $row = collect($apiResponse->json('paginator.data'))->firstWhere('id', $noStudentIdUser->id);
        $this->assertNotNull($row);
        $this->assertNull($row['student_id']);
    }
}

<?php

namespace Tests\Feature\Http\Controllers\Users;

use App\Eloquents\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EditInfoActionTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function デフォルトでは学籍番号と大学提供メールアドレスの入力欄が表示される()
    {
        /** @var User */
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->get(route('user.edit'));

        $response->assertOk();
        $response->assertSee('list-view-student-id-and-univemail-input', false);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function student_idとunivemailが無効な場合入力欄が表示されない()
    {
        config([
            'portal.auth.student_id' => false,
            'portal.auth.univemail' => false,
        ]);

        /** @var User */
        $user = factory(User::class)->create([
            'student_id' => null,
            'univemail_local_part' => '',
            'univemail_domain_part' => '',
        ]);

        $response = $this->actingAs($user)->get(route('user.edit'));

        $response->assertOk();
        $response->assertDontSee('list-view-student-id-and-univemail-input', false);
        $response->assertDontSee('name="student_id"', false);
        $response->assertDontSee('name="univemail_local_part"', false);
        $response->assertDontSee('name="univemail_domain_part"', false);
    }
}

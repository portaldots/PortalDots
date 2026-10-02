<?php

namespace Tests\Feature\Http\Controllers\Users;

use App\Eloquents\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateInfoActionTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function student_idとunivemailが無効な場合それらを入力しなくても保存できる()
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

        $response = $this->actingAs($user)->patch(route('user.update'), [
            'name' => '変更 太郎',
            'name_yomi' => 'ヘンコウ タロウ',
            'email' => $user->email,
            'tel' => '000-0000-0000',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('user.edit'));
        $response->assertSessionDoesntHaveErrors();

        $user->refresh();
        $this->assertNull($user->student_id);
        $this->assertEquals('変更 太郎', $user->name);
        $this->assertEquals('000-0000-0000', $user->tel);
    }
}

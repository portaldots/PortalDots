<?php

namespace Tests\Feature\Eloquents;

use App\Eloquents\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function デフォルトでは連絡先メールアドレスと大学提供メールアドレスの両方の認証が必要()
    {
        $user = factory(User::class)->create([
            'email_verified_at' => now(),
            'univemail_verified_at' => null,
        ]);

        $this->assertFalse($user->areBothEmailsVerified());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function univemailが無効な場合は連絡先メールアドレスの認証のみで完了とみなす()
    {
        config(['portal.auth.univemail' => false]);

        $user = factory(User::class)->create([
            'email_verified_at' => now(),
            'univemail_verified_at' => null,
        ]);

        $this->assertTrue($user->areBothEmailsVerified());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function univemailが無効な場合scopeVerifiedは連絡先メールアドレスの認証のみで絞り込む()
    {
        config(['portal.auth.univemail' => false]);

        $verified = factory(User::class)->create([
            'email_verified_at' => now(),
            'univemail_verified_at' => null,
        ]);
        $notVerified = factory(User::class)->create([
            'email_verified_at' => null,
            'univemail_verified_at' => null,
        ]);

        $ids = User::verified()->pluck('id')->all();

        $this->assertContains($verified->id, $ids);
        $this->assertNotContains($notVerified->id, $ids);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function student_idがnullでも保存できる()
    {
        $user = factory(User::class)->create(['student_id' => null]);

        $this->assertNull($user->fresh()->student_id);
    }
}

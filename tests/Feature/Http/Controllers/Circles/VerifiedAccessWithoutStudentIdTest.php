<?php

namespace Tests\Feature\Http\Controllers\Circles;

use App\Eloquents\User;
use Carbon\Carbon;
use Carbon\CarbonImmutable;

/**
 * student_id・univemail を使わない運用（招待制で連絡先メールアドレスのみ認証する運用）を想定したテスト
 */
class VerifiedAccessWithoutStudentIdTest extends BaseTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        // 受付期間内
        Carbon::setTestNowAndTimezone(new CarbonImmutable('2020-02-16 02:25:15'));
        CarbonImmutable::setTestNowAndTimezone(new CarbonImmutable('2020-02-16 02:25:15'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function student_idとunivemailが無効な場合連絡先メールアドレス認証済のユーザーはログインしてverified必須のページを利用できる()
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
            'email_verified_at' => now(),
            'univemail_verified_at' => null,
        ]);

        $this->post(route('login'), [
            'login_id' => $user->email,
            'password' => 'password',
        ]);
        $this->assertAuthenticatedAs($user);

        $response = $this->get(
            route('circles.create', ['participation_type' => $this->participationType])
        );

        $response->assertOk();
    }
}

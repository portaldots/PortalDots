<?php

namespace Tests\Feature\Services\Emails;

use App\Eloquents\Email;
use App\Services\Emails\SendEmailService;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\Emails\SendEmailServiceMailable;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class SendEmailsServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_failed_delivery_releases_lock_and_can_be_retried(): void
    {
        $email = factory(Email::class)->create();
        Log::spy();
        $mailManager = Mail::getFacadeRoot();
        $pendingMail = Mockery::mock();
        $pendingMail->shouldReceive('send')->once()->andThrow(new RuntimeException('SMTP failure'));
        Mail::shouldReceive('to')->once()->andReturn($pendingMail);

        SendEmailService::runJob();

        $email->refresh();
        $this->assertNull($email->locked_at);
        $this->assertNull($email->sent_at);
        $this->assertSame(1, $email->count_failed);
        Log::shouldHaveReceived('warning')->once()->with('メールの送信に失敗しました。', [
            'email_id' => $email->id,
            'attempt' => 1,
            'exception_class' => RuntimeException::class,
        ]);

        Mail::swap($mailManager);
        Mail::fake();
        SendEmailService::runJob();

        $email->refresh();
        $this->assertNull($email->locked_at);
        $this->assertNotNull($email->sent_at);
        Mail::assertSent(SendEmailServiceMailable::class, 1);
    }

    public function test_delivery_skips_sent_locked_and_exhausted_emails(): void
    {
        factory(Email::class)->create(['sent_at' => now()]);
        factory(Email::class)->create(['locked_at' => now()]);
        factory(Email::class)->create(['count_failed' => 3]);
        Mail::fake();

        SendEmailService::runJob();

        Mail::assertNothingSent();
    }

    public function test_another_job_cannot_send_an_email_while_it_is_locked(): void
    {
        $email = factory(Email::class)->create();
        $pendingMail = Mockery::mock();
        $pendingMail->shouldReceive('send')->once()->andReturnUsing(function () {
            SendEmailService::runJob();
        });
        Mail::shouldReceive('to')->once()->andReturn($pendingMail);

        SendEmailService::runJob();

        $email->refresh();
        $this->assertNull($email->locked_at);
        $this->assertNotNull($email->sent_at);
    }

    public function test_health_check_ignores_failed_deliveries_and_recent_emails(): void
    {
        factory(Email::class)->create(['count_failed' => 1, 'created_at' => now()->subHours(25)]);
        factory(Email::class)->create(['created_at' => now()->subHours(23)]);

        $this->assertTrue(SendEmailService::isServiceOperational());
    }

    public function test_delivery_rechecks_emails_claimed_by_another_job_after_fetching(): void
    {
        factory(Email::class)->create();
        $otherEmail = factory(Email::class)->create();
        $pendingMail = Mockery::mock();
        $pendingMail->shouldReceive('send')->once()->andReturnUsing(function () use ($otherEmail) {
            $otherEmail->sent_at = now();
            $otherEmail->save();
        });
        Mail::shouldReceive('to')->once()->andReturn($pendingMail);

        SendEmailService::runJob();

        $this->assertSame(2, Email::whereNotNull('sent_at')->count());
    }

    public function test_delivery_stops_after_ten_failures_in_one_job(): void
    {
        factory(Email::class, 11)->create();
        Log::spy();
        $pendingMail = Mockery::mock();
        $pendingMail->shouldReceive('send')->times(10)->andThrow(new RuntimeException('SMTP failure'));
        Mail::shouldReceive('to')->times(10)->andReturn($pendingMail);

        SendEmailService::runJob();

        $this->assertSame(10, Email::where('count_failed', 1)->count());
        $this->assertSame(1, Email::where('count_failed', 0)->count());
        $this->assertSame(0, Email::whereNotNull('locked_at')->count());
        Log::shouldHaveReceived('error')->once()->with(
            'メール送信の失敗回数が上限に達したため、今回の処理を中断しました。',
            ['failed_count' => 10]
        );
    }

    /**
     * @var SendEmailService
     */
    private $sendEmailService;

    public function setUp(): void
    {
        parent::setUp();

        $this->sendEmailService = App::make(SendEmailService::class);
        Carbon::setTestNowAndTimezone(new CarbonImmutable('2020-02-02 20:20:20'));
        CarbonImmutable::setTestNowAndTimezone(new CarbonImmutable('2020-02-02 20:20:20'));
    }

    public function test_isServiceOperational_配信予約がない場合はtrueを返す()
    {
        $this->assertTrue($this->sendEmailService->isServiceOperational());
    }

    public function test_isServiceOperational_配信予約されたメールが配信されているときはtrueを返す()
    {
        // 送信済みメール
        factory(Email::class)->create([
            'subject' => '送信済のメール',
            'sent_at' => now(),
            'created_at' => now()->subHours(25),
        ]);

        $this->assertTrue($this->sendEmailService->isServiceOperational());
    }

    public function test_isServiceOperational_配信予約から24時間以上経過してもメールが送信されていないときにfalseを返す()
    {
        // 送信済みメール
        factory(Email::class)->create([
            'subject' => '送信済のメール',
            'sent_at' => now(),
            'created_at' => now()->subHours(25),
        ]);

        // CRON未設定などで未送信のメール
        factory(Email::class)->create([
            'subject' => 'CRONの不具合で未送信のメール',
            'created_at' => now()->subHours(25),
        ]);

        $this->assertFalse($this->sendEmailService->isServiceOperational());
    }
}

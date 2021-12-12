<?php

namespace Tests\Feature\Services\Emails;

use App\Eloquents\Email;
use App\Services\Emails\SendEmailService;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

class SendEmailsServiceTest extends TestCase
{
    use RefreshDatabase;

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

    /**
     * @test
     */
    public function isServiceOperational_配信予約がない場合はtrueを返す()
    {
        $this->assertTrue($this->sendEmailService->isServiceOperational());
    }

    /**
     * @test
     */
    public function isServiceOperational_配信予約されたメールが配信されているときはtrueを返す()
    {
        // 送信済みメール
        factory(Email::class)->create([
            'subject' => '送信済のメール',
            'sent_at' => now(),
            'created_at' => now()->subHours(25),
        ]);

        $this->assertTrue($this->sendEmailService->isServiceOperational());
    }

    /**
     * @test
     */
    public function isServiceOperational_配信予約から24時間以上経過してもメールが送信されていないときにfalseを返す()
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

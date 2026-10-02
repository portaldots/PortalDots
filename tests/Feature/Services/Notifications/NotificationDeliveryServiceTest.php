<?php

namespace Tests\Feature\Services\Notifications;

use App\Eloquents\User;
use App\Services\Notifications\NotificationDeliveryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use RuntimeException;
use Tests\TestCase;

class NotificationDeliveryServiceTest extends TestCase
{
    use RefreshDatabase;

    private NotificationDeliveryService $notificationDeliveryService;
    private int $userId;

    public function setUp(): void
    {
        parent::setUp();
        $this->notificationDeliveryService = App::make(NotificationDeliveryService::class);
        $this->userId = factory(User::class)->create()->id;
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 同じキーでの2回目の呼び出しは送信されない()
    {
        $sent = 0;
        $send = function () use (&$sent) {
            $sent++;
        };

        $this->notificationDeliveryService->sendOnce('answer-returned:1:1:1', $this->userId, $send);
        $this->notificationDeliveryService->sendOnce('answer-returned:1:1:1', $this->userId, $send);

        $this->assertSame(1, $sent);
        $this->assertDatabaseCount('notification_deliveries', 1);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 送信が例外を投げると予約が削除され次回は送信される()
    {
        $attempts = 0;
        $failingSend = function () use (&$attempts) {
            $attempts++;
            throw new RuntimeException('メール送信に失敗しました');
        };

        try {
            $this->notificationDeliveryService->sendOnce('answer-returned:1:1:1', $this->userId, $failingSend);
            $this->fail('例外が伝播するはず');
        } catch (RuntimeException $e) {
            $this->assertSame('メール送信に失敗しました', $e->getMessage());
        }

        $this->assertSame(1, $attempts);
        $this->assertDatabaseCount('notification_deliveries', 0);

        $succeeded = false;
        $succeed = function () use (&$succeeded) {
            $succeeded = true;
        };
        $this->notificationDeliveryService->sendOnce('answer-returned:1:1:1', $this->userId, $succeed);

        $this->assertTrue($succeeded);
        $this->assertDatabaseCount('notification_deliveries', 1);
    }
}

<?php

namespace Tests\Feature\Services\Contacts;

use App\Mail\Contacts\ContactMailable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Services\Contacts\ContactsService;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;
use App;
use App\Eloquents\Circle;
use App\Eloquents\ContactCategory;
use App\Eloquents\User;
use App\Mail\Contacts\EmailCategoryMailable;

class ContactsServeceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var ContactsService
     */
    private $contactsService;

        /**
     * @var Circle
     */
    private $circle;

    /**
     * @var User
     */
    private $leader;

    /**
     * @var User
     */
    private $member;

    /**
     * @var ContactCategory
     */
    private $ContactCategory;

    public function setUp(): void
    {
        parent::setUp();
        $this->contactsService = App::make(ContactsService::class);
        $this->circle = factory(Circle::class)->create();
        $this->leader = factory(User::class)->create();
        $this->member = factory(User::class)->create();

        $this->circle->users()->attach([
            $this->leader->id => ['is_leader' => true],
            $this->member->id,
        ]);

        $this->ContactCategory = factory(ContactCategory::class)->create();
    }

    /**
     * @test
     */
    public function create_お問い合わせを作成できる()
    {
        Mail::fake();

        $this->contactsService->create($this->circle, $this->leader, "こんにちは。\nこれはてすとです。", $this->ContactCategory);

        $this->send_お問い合わせが企画のメンバーに送信できる();
        $this->sendToStaff_スタッフ用控えが送信できる();
    }

    private function send_お問い合わせが企画のメンバーに送信できる()
    {
        Mail::assertSent(ContactMailable::class, function ($mail) {
            return $mail->hasTo($this->leader->email);
        });

        Mail::assertSent(ContactMailable::class, function ($mail) {
            return $mail->hasTo($this->member->email);
        });
    }

    private function sendToStaff_スタッフ用控えが送信できる()
    {
        Mail::assertSent(ContactMailable::class, function ($mail) {
            return $mail->hasTo($this->ContactCategory->email);
        });
    }

    /**
     * @test
     */
    public function sendContactCategory_ContactCategory作成時にメールが送信される()
    {
        Mail::fake();

        $this->contactsService->sendContactCategory($this->ContactCategory);

        Mail::assertSent(EmailCategoryMailable::class, function ($mail) {
            return $mail->hasTo($this->ContactCategory->email);
        });
    }
}

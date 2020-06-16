<?php

namespace Tests\Feature\Services\Contacts;

use App;
use App\Eloquents\ContactCategory;
use App\Mail\Contacts\EmailCategoryMailable;
use App\Services\Contacts\ContactCategoriesService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ContactCategoriesServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var ContactCategoriesService
     */
    private $categoriesService;

    /**
     * @var ContactCategory
     */
    private $contactCategory;

    public function setUp(): void
    {
        parent::setUp();
        $this->categoriesService = App::make(ContactCategoriesService::class);

        $this->contactCategory = factory(ContactCategory::class)->create();
    }

    /**
     * @test
     */
    public function send()
    {
        Mail::fake();

        $this->categoriesService->send($this->contactCategory);

        Mail::assertSent(EmailCategoryMailable::class, function ($mail) {
            return $mail->hasTo($this->contactCategory->email);
        });
    }
}

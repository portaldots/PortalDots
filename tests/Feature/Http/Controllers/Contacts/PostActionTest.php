<?php

namespace Tests\Feature\Http\Controllers\Contacts;

use App\Eloquents\Circle;
use App\Eloquents\ContactEmail;
use App\Eloquents\User;
use App\Services\Contacts\ContactsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

class PostActionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var Circle
     */
    private $circle;

    /**
     * @var User
     */
    private $user;

    /**
     * @var ContactEmail
     */
    private $contactEmail;

    public function setUp(): void
    {
        parent::setUp();

        $this->circle = factory(Circle::class)->create();
        $this->user = factory(User::class)->create();

        $this->circle->users()->attach($this->user->id, ['is_leader' => true]);

        $this->contactEmail = factory(ContactEmail::class)->create();
    }

    /**
     * @test
     */
    public function ContactsServiceのcreateが呼び出される()
    {
        $this->mock(ContactsService::class, function ($mock) {
            $mock->shouldReceive('create')->once()->andReturn(true);
        });

        $responce = $this
            ->actingAs($this->user)
            ->post(route('contacts.post'), [
                'circle_id' => $this->circle->id,
                'contact_body' => 'テストです！',
                'category' => $this->contactEmail->id,
            ]);

        $responce->assertSessionHas('topAlert.title', 'お問い合わせを受け付けました。');
    }
}

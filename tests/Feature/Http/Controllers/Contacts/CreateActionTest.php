<?php

namespace Tests\Feature\Http\Controllers\Contacts;

use App\Eloquents\ContactCategory;
use App\Eloquents\Thread;
use App\Eloquents\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateActionTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function 企画に所属していないユーザーはお問い合わせを送信して自分の会話だけを見られる()
    {
        $category = factory(ContactCategory::class)->create();
        $user = factory(User::class)->create();

        $this->actingAs($user)
            ->post(route('contacts.post'), [
                'contact_body' => 'よろしくお願いします',
                'category' => $category->id,
                'client_token' => 'token-circle-less-1',
            ]);

        $thread = Thread::whereNull('circle_id')->where('user_id', $user->id)->firstOrFail();
        $this->assertSame(1, $thread->entries()->count());

        $response = $this->actingAs($user)->get(route('contacts'));

        $response->assertOk();
        $response->assertSee('よろしくお願いします');
    }
}

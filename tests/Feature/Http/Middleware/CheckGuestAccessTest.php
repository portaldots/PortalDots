<?php

namespace Tests\Feature\Http\Middleware;

use App\Contracts\GuestAccessPolicy;
use App\Eloquents\Document;
use App\Eloquents\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CheckGuestAccessTest extends TestCase
{
    use RefreshDatabase;

    private function denyGuests()
    {
        $this->app->bind(GuestAccessPolicy::class, function () {
            return new class implements GuestAccessPolicy {
                public function allowsGuests(): bool
                {
                    return false;
                }
            };
        });
    }

    /**
     * @return array ルート名の一覧
     */
    public static function ルート一覧_provider()
    {
        return [
            'ホーム' => ['home'],
            'お知らせ一覧' => ['pages.index'],
            '配布資料一覧' => ['documents.index'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\Test]
    #[\PHPUnit\Framework\Attributes\DataProvider("ルート一覧_provider")]
    public function GuestAccessPolicyがfalseの場合ゲストはログイン画面にリダイレクトされる(string $routeName)
    {
        $this->denyGuests();

        $response = $this->get(route($routeName));

        $response->assertRedirect(route('login'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function GuestAccessPolicyがfalseの場合ゲストは配布資料のダウンロード画面でもログイン画面にリダイレクトされる()
    {
        $this->denyGuests();

        Storage::fake('local');
        $document = factory(Document::class)->create([
            'is_public' => true,
            'path' => 'documents/foobar.pdf',
        ]);
        Storage::disk('local')->put($document->path, 'dummy');

        $response = $this->get(route('documents.show', ['document' => $document]));

        $response->assertRedirect(route('login'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    #[\PHPUnit\Framework\Attributes\DataProvider("ルート一覧_provider")]
    public function GuestAccessPolicyがfalseでもログイン済みユーザーは影響を受けない(string $routeName)
    {
        $this->denyGuests();

        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->get(route($routeName));

        $response->assertOk();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function GuestAccessPolicyがfalseでもログイン済みユーザーは配布資料をダウンロードできる()
    {
        $this->denyGuests();

        Storage::fake('local');
        $document = factory(Document::class)->create([
            'is_public' => true,
            'path' => 'documents/foobar.pdf',
        ]);
        Storage::disk('local')->put($document->path, 'dummy');

        $user = factory(User::class)->create();

        $response = $this->actingAs($user)
            ->get(route('documents.show', ['document' => $document]));

        $response->assertOk();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    #[\PHPUnit\Framework\Attributes\DataProvider("ルート一覧_provider")]
    public function GuestAccessPolicyがtrueの場合ゲストもアクセスできる(string $routeName)
    {
        $response = $this->get(route($routeName));

        $response->assertOk();
    }
}

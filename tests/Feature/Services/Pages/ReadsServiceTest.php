<?php

namespace Tests\Feature\Services\Pages;

use App\Eloquents\Page;
use App\Eloquents\Read;
use App\Eloquents\User;
use App\Services\Pages\ReadsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

class ReadsServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var ReadsService
     */
    private $readsService;

    public function setUp(): void
    {
        parent::setUp();
        $this->readsService = App::make(ReadsService::class);
    }

    /**
     * @test
     */
    public function markAsRead()
    {
        /** @var Page */
        $page = factory(Page::class)->create();

        /** @var User */
        $user = factory(User::class)->create();

        $this->assertDatabaseMissing('reads', [
            'page_id' => $page->id,
            'user_id' => $user->id,
        ]);

        $this->readsService->markAsRead($page, $user);

        $this->assertDatabaseHas('reads', [
            'page_id' => $page->id,
            'user_id' => $user->id,
        ]);
    }

    /**
     * @test
     */
    public function deleteAllReadsByPage()
    {
        /** @var Illuminate\Database\Eloquent\Collection */
        $pages = factory(Page::class, 5)->create();

        /** @var Illuminate\Database\Eloquent\Collection */
        $users = factory(User::class, 3)->create();

        $this->assertSame(0, Read::count());

        // 3 人のユーザーは 5 つのお知らせを全て読む
        foreach ($pages as $page) {
            foreach ($users as $user) {
                $this->readsService->markAsRead($page, $user);
            }
        }

        $this->assertSame($pages->count() * $users->count(), Read::count());

        // 3 つ目のお知らせの既読情報を削除
        $this->readsService->deleteAllReadsByPage($pages[2]);

        $this->assertSame(($pages->count() - 1) * $users->count(), Read::count());

        foreach ($pages as $page) {
            if ($page->id === $pages[2]->id) {
                $this->assertDatabaseMissing('reads', ['page_id' => $page->id]);
            } else {
                $this->assertDatabaseHas('reads', ['page_id' => $page->id]);
            }
        }
    }
}

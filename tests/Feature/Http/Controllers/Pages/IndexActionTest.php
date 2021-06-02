<?php

namespace Tests\Feature\Http\Controllers\Pages;

use App\Eloquents\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndexActionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function 非公開と固定表示のお知らせは一覧に表示されない()
    {
        // 固定されたお知らせ
        $pinnedPrivatePageTitle = 'this is a pinned private page';
        $pinnedPublicPageTitle = 'this is a pinned public page';

        // 通常のお知らせ
        $privatePageTitle = 'this is a private page';
        $publicPageTitle = 'this is a public form';

        factory(Page::class)->create([
            'title' => $pinnedPrivatePageTitle,
            'is_pinned' => true,
            'is_public' => false,
        ]);
        factory(Page::class)->create([
            'title' => $pinnedPublicPageTitle,
            'is_pinned' => true,
            'is_public' => true,
        ]);
        factory(Page::class)->create([
            'title' => $privatePageTitle,
            'is_public' => false,
        ]);
        factory(Page::class)->create([
            'title' => $publicPageTitle,
            'is_public' => true,
        ]);

        $response = $this->get(route('pages.index'));

        $response->assertDontSee($pinnedPrivatePageTitle);
        $response->assertDontSee($pinnedPublicPageTitle);
        $response->assertDontSee($privatePageTitle);
        $response->assertSee($publicPageTitle);
    }
}

<?php

namespace Tests\Feature\Http\Controllers\Pages;

use App\Eloquents\Document;
use App\Eloquents\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShowActionTest extends TestCase
{
    use RefreshDatabase;

    public function 非公開と固定表示のお知らせは表示できない_provider()
    {
        return [
            '公開・非固定' => [true, false, true],
            '非公開・非固定' => [false, false, false],
            '非公開・固定' => [false, true, false],
            '公開・固定' => [true, true, false],
        ];
    }

    /**
     * @test
     * @dataProvider 非公開と固定表示のお知らせは表示できない_provider
     */
    public function 非公開と固定表示のお知らせは表示できない(bool $is_public, bool $is_pinned, bool $can_see)
    {
        $page_title = 'これはお知らせのタイトルです';

        $page = factory(Page::class)->create([
            'title' => $page_title,
            'is_pinned' => $is_pinned,
            'is_public' => $is_public,
        ]);

        $response = $this->get(route('pages.show', ['page' => $page]));

        if ($can_see) {
            $response->assertOk();
            $response->assertSee($page_title);
        } else {
            $response->assertForbidden();
            $response->assertDontSee($page_title);
        }
    }

    /**
     * @test
     */
    public function お知らせに添付されている非公開の配布資料が一覧に表示されない()
    {
        /** @var Page */
        $page = factory(Page::class)->create([
            'is_public' => true,
        ]);

        /** @var Document */
        $public_document = factory(Document::class)->create([
            'name' => '公開されている配布資料',
            'is_public' => true,
        ]);

        /** @var Document */
        $private_document = factory(Document::class)->create([
            'name' => '非公開の配布資料',
            'is_public' => false,
        ]);

        $page->documents()->save($public_document);
        $page->documents()->save($private_document);

        $response = $this->get(route('pages.show', ['page' => $page]));

        $response->assertOk();
        $response->assertSee('公開されている配布資料');
        $response->assertDontSee('非公開の配布資料');
    }
}

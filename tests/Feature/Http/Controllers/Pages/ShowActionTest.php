<?php

namespace Tests\Feature\Http\Controllers\Pages;

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
}

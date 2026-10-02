<?php

namespace Tests\Feature\Http\Controllers\Pages;

use App\Eloquents\Circle;
use App\Eloquents\Document;
use App\Eloquents\Page;
use App\Eloquents\Tag;
use App\Eloquents\User;
use App\Services\Circles\SelectorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

class ShowActionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var SelectorService
     */
    private $selectorService;

    private $tag;
    private $circleWithTag;
    private $circleWithTagUser;
    private $circleSelected;
    private $circleSelectedUser;
    private $circleUnrelated;
    private $circleUnrelatedUser;
    private $userWithoutCircle;

    public function setUp(): void
    {
        parent::setUp();

        $this->selectorService = App::make(SelectorService::class);

        $this->tag = factory(Tag::class)->create();

        $this->circleWithTag = factory(Circle::class)->create();
        $this->circleWithTag->tags()->attach($this->tag->id);
        $this->circleWithTagUser = factory(User::class)->create();
        $this->circleWithTagUser->circles()->attach($this->circleWithTag->id, ['is_leader' => true]);

        $this->circleSelected = factory(Circle::class)->create();
        $this->circleSelectedUser = factory(User::class)->create();
        $this->circleSelectedUser->circles()->attach($this->circleSelected->id, ['is_leader' => true]);

        $this->circleUnrelated = factory(Circle::class)->create();
        $this->circleUnrelatedUser = factory(User::class)->create();
        $this->circleUnrelatedUser->circles()->attach($this->circleUnrelated->id, ['is_leader' => true]);

        $this->userWithoutCircle = factory(User::class)->create();
    }

    /**
     * @param string $viewerType
     * @return $this
     */
    private function actingAsViewer(string $viewerType)
    {
        switch ($viewerType) {
            case 'guest':
                return $this;
            case 'no_circle':
                return $this->actingAs($this->userWithoutCircle);
            case 'matching_tag':
                $this->selectorService->setCircle($this->circleWithTag);
                return $this->actingAs($this->circleWithTagUser);
            case 'selected_circle':
                $this->selectorService->setCircle($this->circleSelected);
                return $this->actingAs($this->circleSelectedUser);
            case 'unrelated_circle':
                $this->selectorService->setCircle($this->circleUnrelated);
                return $this->actingAs($this->circleUnrelatedUser);
        }
    }

    public static function 非公開と固定表示のお知らせは表示できない_provider()
    {
        return [
            '公開・非固定' => [true, false, true],
            '非公開・非固定' => [false, false, false],
            '非公開・固定' => [false, true, false],
            '公開・固定' => [true, true, false],
        ];
    }

    #[\PHPUnit\Framework\Attributes\Test]
    #[\PHPUnit\Framework\Attributes\DataProvider("非公開と固定表示のお知らせは表示できない_provider")]
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
     * @return array 公開範囲・閲覧者の組み合わせと、表示できるかどうか
     */
    public static function 公開範囲による表示切り替え_provider()
    {
        return [
            'everyone・ゲスト' => ['everyone', 'guest', true],
            'everyone・企画未選択のユーザー' => ['everyone', 'no_circle', true],
            'everyone・該当タグの企画' => ['everyone', 'matching_tag', true],
            'everyone・選択された企画' => ['everyone', 'selected_circle', true],
            'everyone・関係ない企画' => ['everyone', 'unrelated_circle', true],
            'signed_in・ゲスト' => ['signed_in', 'guest', false],
            'signed_in・企画未選択のユーザー' => ['signed_in', 'no_circle', true],
            'signed_in・該当タグの企画' => ['signed_in', 'matching_tag', true],
            'signed_in・選択された企画' => ['signed_in', 'selected_circle', true],
            'signed_in・関係ない企画' => ['signed_in', 'unrelated_circle', true],
            'selected・ゲスト' => ['selected', 'guest', false],
            'selected・企画未選択のユーザー' => ['selected', 'no_circle', false],
            'selected・該当タグの企画' => ['selected', 'matching_tag', true],
            'selected・選択された企画' => ['selected', 'selected_circle', true],
            'selected・関係ない企画' => ['selected', 'unrelated_circle', false],
        ];
    }

    #[\PHPUnit\Framework\Attributes\Test]
    #[\PHPUnit\Framework\Attributes\DataProvider("公開範囲による表示切り替え_provider")]
    public function 公開範囲による表示切り替え(string $audience, string $viewerType, bool $canSee)
    {
        $page = factory(Page::class)->create([
            'title' => '公開範囲のテスト対象お知らせ',
            'is_public' => true,
            'audience' => $audience,
        ]);

        if ($audience === 'selected') {
            $page->viewableTags()->attach($this->tag->id);
            $page->viewableCircles()->attach($this->circleSelected->id);
        }

        $response = $this->actingAsViewer($viewerType)->get(route('pages.show', ['page' => $page]));

        if ($canSee) {
            $response->assertOk();
        } else {
            $response->assertForbidden();
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 非公開かつタグ指定のないお知らせは企画にログインしていても表示できない()
    {
        // audience が everyone でタグが空でも、is_public が false ならどの閲覧者にも表示されない
        $page = factory(Page::class)->create([
            'is_public' => false,
            'audience' => 'everyone',
        ]);

        $response = $this->actingAsViewer('matching_tag')->get(route('pages.show', ['page' => $page]));

        $response->assertForbidden();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 非公開に変更するとすぐに閲覧できなくなる()
    {
        $page = factory(Page::class)->create([
            'is_public' => true,
            'audience' => 'everyone',
        ]);

        $this->get(route('pages.show', ['page' => $page]))->assertOk();

        $page->is_public = false;
        $page->save();

        $this->get(route('pages.show', ['page' => $page]))->assertForbidden();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 公開範囲を変更するとアクセスできなくなったユーザーはすぐに閲覧できなくなる()
    {
        $page = factory(Page::class)->create([
            'is_public' => true,
            'audience' => 'everyone',
        ]);

        $this->actingAsViewer('unrelated_circle')->get(route('pages.show', ['page' => $page]))->assertOk();

        $page->audience = 'selected';
        $page->save();
        $page->viewableCircles()->attach($this->circleSelected->id);

        $this->actingAsViewer('unrelated_circle')->get(route('pages.show', ['page' => $page]))->assertForbidden();
    }

    #[\PHPUnit\Framework\Attributes\Test]
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

    #[\PHPUnit\Framework\Attributes\Test]
    public function お知らせに添付されていても閲覧者から見えない公開範囲の配布資料は表示されない()
    {
        /** @var Page */
        $page = factory(Page::class)->create([
            'is_public' => true,
        ]);

        /** @var Document */
        $visible_document = factory(Document::class)->create([
            'name' => '閲覧可能な配布資料',
            'is_public' => true,
            'audience' => 'everyone',
        ]);

        /** @var Document */
        $hidden_document = factory(Document::class)->create([
            'name' => '閲覧不可能な配布資料',
            'is_public' => true,
            'audience' => 'selected',
        ]);
        $hidden_document->viewableCircles()->attach($this->circleSelected->id);

        $page->documents()->save($visible_document);
        $page->documents()->save($hidden_document);

        $response = $this->actingAsViewer('unrelated_circle')->get(route('pages.show', ['page' => $page]));

        $response->assertOk();
        $response->assertSee('閲覧可能な配布資料');
        $response->assertDontSee('閲覧不可能な配布資料');
    }
}

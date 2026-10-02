<?php

namespace Tests\Feature\Http\Controllers\Pages;

use App\Eloquents\Circle;
use App\Eloquents\Page;
use App\Eloquents\Tag;
use App\Eloquents\User;
use App\Services\Circles\SelectorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

class IndexActionTest extends TestCase
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

    #[\PHPUnit\Framework\Attributes\Test]
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

    /**
     * @return array 公開範囲・閲覧者の組み合わせと、一覧に表示できるかどうか
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
        $pageTitle = '公開範囲のテスト対象お知らせ';

        $page = factory(Page::class)->create([
            'title' => $pageTitle,
            'is_public' => true,
            'audience' => $audience,
        ]);

        if ($audience === 'selected') {
            $page->viewableTags()->attach($this->tag->id);
            $page->viewableCircles()->attach($this->circleSelected->id);
        }

        $response = $this->actingAsViewer($viewerType)->get(route('pages.index'));

        if ($canSee) {
            $response->assertSee($pageTitle);
        } else {
            $response->assertDontSee($pageTitle);
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 非公開かつタグ指定のないお知らせは企画にログインしていても一覧に表示されない()
    {
        $pageTitle = '非公開の全体公開お知らせ';

        factory(Page::class)->create([
            'title' => $pageTitle,
            'is_public' => false,
            'audience' => 'everyone',
        ]);

        $response = $this->actingAsViewer('matching_tag')->get(route('pages.index'));

        $response->assertDontSee($pageTitle);
    }
}

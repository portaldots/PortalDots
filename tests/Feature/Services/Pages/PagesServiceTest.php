<?php

namespace Tests\Feature\Services\Pages;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Services\Pages\PagesService;
use App\Eloquents\Tag;
use App\Eloquents\Circle;
use App\Eloquents\User;
use App\Eloquents\Page;
use App\Eloquents\Email;
use App;
use DB;

class PagesServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var PagesService
     */
    private $pagesService;

    private $staff;

    private $content = [
    'title' => 'お知らせ作成テスト123',
    'body' => <<< EOL
これはお知らせです。

# 見出しです。
- リストです
- リストです
    - リストです
EOL
    ];

    public function setUp(): void
    {
        parent::setUp();

        $this->pagesService = App::make(PagesService::class);

        $this->staff = factory(User::class)->states('staff')->create();
    }

    /**
     * @test
     */
    public function createPage_お知らせを保存する()
    {
        $this->assertSame(0, Page::count());

        $this->pagesService->createPage(
            $this->content['title'],
            $this->content['body'],
            $this->staff,
            '',
            []
        );

        $content_on_db = $this->content;
        $content_on_db['created_by'] = $this->staff->id;

        $this->assertDatabaseHas('pages', $content_on_db);
    }

    /**
     * @test
     */
    public function sendEmailsByPage_全ユーザーに対し一斉送信予約する()
    {
        $this->assertSame(0, Page::count());

        // 送信先用にたくさんユーザーを作成する
        factory(User::class, 40)->create();

        $page = $this->pagesService->createPage(
            $this->content['title'],
            $this->content['body'],
            $this->staff,
            '',
            []
        );

        $this->pagesService->sendEmailsByPage($page);

        $this->assertDatabaseHas('pages', $this->content);

        $this->assertSame(User::count(), Email::count());
    }

    /**
     * @test
     */
    public function createPage_お知らせを保存する際にアクセス可能な企画タグを指定する()
    {
        $tags_count = 4;
        $tags = factory(Tag::class, $tags_count)->create();

        // 送信先用にたくさんユーザーを作成する
        $users = factory(User::class, 40)->create();
        $circles = factory(Circle::class, 40)->create();
        $tags = factory(Tag::class, 10)->create();

        for ($i = 0; $i < count($circles); ++$i) {
            $circles[$i]->tags()->attach($tags[$i % 10]);
            $circles[$i]->users()->attach($users[$i & 40]);
        }

        $this->assertSame(0, DB::table('page_viewable_tags')->count());

        $page = $this->pagesService->createPage(
            $this->content['title'],
            $this->content['body'],
            $this->staff,
            '',
            // $tags[2] と $tags[5] の2つを、閲覧可能なタグとして指定する
            // （該当する企画数は 8）
            $post_content['viewable_tags'] = [$tags[2]->name, $tags[5]->name]
        );

        $this->pagesService->sendEmailsByPage($page);

        $this->assertSame(2, DB::table('page_viewable_tags')->count());
        $this->assertSame(
            User::byTags(
                Tag::whereIn('id', [$tags[2]->id, $tags[5]->id])->get()
            )->count(),
            Email::count()
        );
    }

    /**
     * @test
     */
    public function createPage_未作成のタグを指定した場合は無視される()
    {
        $tag = factory(Tag::class)->create();

        $this->assertSame(0, DB::table('page_viewable_tags')->count());

        $page = $this->pagesService->createPage(
            $this->content['title'],
            $this->content['body'],
            $this->staff,
            '',
            // 未作成のタグを意図的に指定する。作成済みのタグも混ぜる
            ['未作成のタグA', '未作成のタグB', $tag->name]
        );

        $this->assertSame(1, Page::count());
        // 未作成のタグが混じっていた場合、作成済みタグのみ保存される
        $this->assertSame(1, DB::table('page_viewable_tags')->count());
    }
}

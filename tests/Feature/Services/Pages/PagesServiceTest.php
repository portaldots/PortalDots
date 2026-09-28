<?php

namespace Tests\Feature\Services\Pages;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Services\Pages\PagesService;
use App\Eloquents\Tag;
use App\Eloquents\Circle;
use App\Eloquents\Document;
use App\Eloquents\User;
use App\Eloquents\Page;
use App\Eloquents\Email;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class PagesServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var PagesService
     */
    private $pagesService;

    /**
     * @var User
     */
    private $staff;

    private $content = [
        'title' => 'お知らせ作成テスト123',
        'is_pinned' => true,
        'is_public' => false,
        'body' => "これはお知らせです。\n\n# 見出しです。\n- リストです\n- リストです\n    - リストです"
    ];

    public function setUp(): void
    {
        parent::setUp();

        $this->pagesService = App::make(PagesService::class);

        $this->staff = factory(User::class)->states('staff')->create();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function createPage_お知らせを保存する()
    {
        $this->assertSame(0, Page::count());

        $this->pagesService->createPage(
            $this->content['title'],
            $this->content['body'],
            $this->staff,
            '',
            [],
            [],
            $this->content['is_public'],
            $this->content['is_pinned'],
        );

        $content_on_db = $this->content;

        $this->assertDatabaseHas('pages', $content_on_db);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function setPinStatusForPage_お知らせを固定表示できる()
    {
        $page = $this->pagesService->createPage(
            $this->content['title'],
            $this->content['body'],
            $this->staff,
            '',
            [],
            [],
            $this->content['is_public'],
            0,
        );

        $this->pagesService->setPinStatusForPage($page, true);

        $content_on_db = $this->content;
        $content_on_db['body'] = $this->content['body'];
        $content_on_db['is_pinned'] = true;

        $this->assertSame(1, Page::count());
        $this->assertDatabaseHas('pages', $content_on_db);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function setPinStatusForPage_お知らせを固定解除できる()
    {
        $page = $this->pagesService->createPage(
            $this->content['title'],
            $this->content['body'],
            $this->staff,
            '',
            [],
            [],
            $this->content['is_public'],
            1,
        );

        $this->pagesService->setPinStatusForPage($page, false);

        $content_on_db = $this->content;
        $content_on_db['body'] = $this->content['body'];
        $content_on_db['is_pinned'] = false;

        $this->assertSame(1, Page::count());
        $this->assertDatabaseHas('pages', $content_on_db);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function updatePage()
    {
        $this->assertSame(0, Page::count());

        $page = $this->pagesService->createPage(
            $this->content['title'],
            $this->content['body'],
            $this->staff,
            '',
            [],
            [],
            $this->content['is_public'],
            $this->content['is_pinned'],
        );

        $this->pagesService->updatePage(
            $page,
            $this->content['title'],
            "更新した本文",
            $this->staff,
            '',
            [],
            [],
            true,
            true,
        );

        $content_on_db = $this->content;
        $content_on_db['body'] = "更新した本文";
        $content_on_db['is_public'] = true;
        $content_on_db['is_pinned'] = true;

        $this->assertSame(1, Page::count());
        $this->assertDatabaseHas('pages', $content_on_db);
    }

    #[\PHPUnit\Framework\Attributes\Test]
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
            [],
            [],
            $this->content['is_public'],
            $this->content['is_pinned'],
        );

        $this->pagesService->sendEmailsByPage($page);

        $this->assertDatabaseHas('pages', $this->content);

        $this->assertSame(User::count(), Email::count());
    }

    #[\PHPUnit\Framework\Attributes\Test]
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
            $post_content['viewable_tags'] = [$tags[2]->name, $tags[5]->name],
            [],
            $this->content['is_public'],
            $this->content['is_pinned'],
            'selected',
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

    #[\PHPUnit\Framework\Attributes\Test]
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
            ['未作成のタグA', '未作成のタグB', $tag->name],
            [],
            $this->content['is_public'],
            $this->content['is_pinned'],
        );

        $this->assertSame(1, Page::count());
        // 未作成のタグが混じっていた場合、作成済みタグのみ保存される
        $this->assertSame(1, DB::table('page_viewable_tags')->count());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function sendEmailsByPage_公開範囲がeveryoneの場合は全ての認証済みユーザーに送信される()
    {
        factory(User::class, 5)->create();

        $page = $this->pagesService->createPage(
            $this->content['title'],
            $this->content['body'],
            $this->staff,
            '',
            [],
            [],
            $this->content['is_public'],
            $this->content['is_pinned'],
            'everyone',
        );

        $this->pagesService->sendEmailsByPage($page);

        $this->assertSame(User::verified()->count(), Email::count());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function sendEmailsByPage_公開範囲がselectedで企画を直接指定した場合はその企画のメンバーにのみ送信される()
    {
        $targetCircle = factory(Circle::class)->create();
        $targetCircleUser = factory(User::class)->create();
        $targetCircle->users()->attach($targetCircleUser->id, ['is_leader' => true]);

        $otherCircle = factory(Circle::class)->create();
        $otherCircleUser = factory(User::class)->create();
        $otherCircle->users()->attach($otherCircleUser->id, ['is_leader' => true]);

        $page = $this->pagesService->createPage(
            $this->content['title'],
            $this->content['body'],
            $this->staff,
            '',
            [],
            [],
            $this->content['is_public'],
            $this->content['is_pinned'],
            'selected',
            [$targetCircle->id],
        );

        $this->pagesService->sendEmailsByPage($page);

        $this->assertSame(1, Email::count());
        $this->assertDatabaseHas('emails', ['email_to' => $targetCircleUser->email]);
        $this->assertDatabaseMissing('emails', ['email_to' => $otherCircleUser->email]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function sendEmailsByPage_公開範囲がselectedでタグを指定した場合は該当タグの企画のメンバーにのみ送信される()
    {
        $tag = factory(Tag::class)->create();

        $taggedCircle = factory(Circle::class)->create();
        $taggedCircle->tags()->attach($tag->id);
        $taggedCircleUser = factory(User::class)->create();
        $taggedCircle->users()->attach($taggedCircleUser->id, ['is_leader' => true]);

        $untaggedCircle = factory(Circle::class)->create();
        $untaggedCircleUser = factory(User::class)->create();
        $untaggedCircle->users()->attach($untaggedCircleUser->id, ['is_leader' => true]);

        $page = $this->pagesService->createPage(
            $this->content['title'],
            $this->content['body'],
            $this->staff,
            '',
            [$tag->name],
            [],
            $this->content['is_public'],
            $this->content['is_pinned'],
            'selected',
        );

        $this->pagesService->sendEmailsByPage($page);

        $this->assertSame(1, Email::count());
        $this->assertDatabaseHas('emails', ['email_to' => $taggedCircleUser->email]);
        $this->assertDatabaseMissing('emails', ['email_to' => $untaggedCircleUser->email]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function sendEmailsByPage_受信者全員が閲覧できない配布資料は本文に含まれない()
    {
        $visibleDocument = factory(Document::class)->create([
            'name' => '全員が閲覧できる配布資料',
            'is_public' => true,
            'audience' => 'everyone',
        ]);

        $circleForRecipient = factory(Circle::class)->create();
        $hiddenDocument = factory(Document::class)->create([
            'name' => '一部の受信者しか閲覧できない配布資料',
            'is_public' => true,
            'audience' => 'selected',
        ]);
        $hiddenDocument->viewableCircles()->attach($circleForRecipient->id);

        // このユーザーは $hiddenDocument を閲覧できるが、他のユーザーは閲覧できない
        $recipientWithAccess = factory(User::class)->create();
        $circleForRecipient->users()->attach($recipientWithAccess->id, ['is_leader' => true]);

        // audience が everyone のお知らせなので、上記以外の認証済みユーザーにも送信される
        factory(User::class)->create();

        $page = $this->pagesService->createPage(
            $this->content['title'],
            $this->content['body'],
            $this->staff,
            '',
            [],
            [$visibleDocument->id, $hiddenDocument->id],
            $this->content['is_public'],
            $this->content['is_pinned'],
            'everyone',
        );

        $this->pagesService->sendEmailsByPage($page);

        $email = Email::first();
        $this->assertStringContainsString('全員が閲覧できる配布資料', $email->body);
        $this->assertStringNotContainsString('一部の受信者しか閲覧できない配布資料', $email->body);
    }
}

<?php

namespace Tests\Feature\Http\Controllers\Staff\Pages;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Eloquents\Tag;
use App\Eloquents\User;
use App\Eloquents\Page;
use App\Eloquents\Email;
use DB;

class StoreActionTest extends TestCase
{
    use RefreshDatabase;

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

        $this->staff = factory(User::class)->states('staff')->create();
    }

    /**
     * @test
     */
    public function お知らせを保存する()
    {
        $this->assertSame(0, Page::count());

        $response = $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->post(route('staff.pages.store'), $this->content);

        $response->assertRedirect(route('staff.pages.create'));

        $this->assertDatabaseHas('pages', $this->content);
    }

    /**
     * @test
     */
    public function 全ユーザーに対し一斉送信予約する()
    {
        $this->assertSame(0, Page::count());

        // 送信先用にたくさんユーザーを作成する
        factory(User::class, 442);

        $post_content = $this->content;
        $post_content['send_emails'] = 'true';

        $response = $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->post(route('staff.pages.store'), $post_content);


        $this->assertDatabaseHas('pages', $this->content);

        $this->assertSame(User::count(), Email::count());
    }

    /**
     * @test
     */
    public function お知らせを保存する際にアクセス可能な企画タグを指定する()
    {
        $tags_count = 4;
        $tags = factory(Tag::class, $tags_count)->create();

        $this->assertSame(0, DB::table('page_viewable_tags')->count());

        $post_content = $this->content;
        $post_content['viewable_tags'] = $tags->pluck('name')->toArray();

        $response = $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->post(route('staff.pages.store'), $post_content);

        $this->assertSame($tags_count, DB::table('page_viewable_tags')->count());
    }

    /**
     * @test
     */
    public function 特定タグの企画に対し一斉送信予約する()
    {
        $this->markTestIncomplete('プルリクマージ前にこのテストも書く');
    }

    /**
     * @test
     */
    public function 未作成のタグを指定した場合はエラーになる()
    {
        $tag = factory(Tag::class)->create();

        $this->assertSame(0, DB::table('page_viewable_tags')->count());

        $post_content = $this->content;
        // 未作成のタグを意図的に指定する。作成済みのタグも混ぜる
        $post_content['viewable_tags'] = ['未作成のタグA', '未作成のタグB', $tag->name];

        $response = $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->post(route('staff.pages.store'), $post_content);

        // 未作成のタグが1つでも混じっていた場合、一切保存しない
        $this->assertSame(0, Page::count());
        $this->assertSame(0, DB::table('page_viewable_tags')->count());
    }
}

<?php

namespace Tests\Feature\Http\Controllers\Staff\Pages;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Eloquents\Tag;
use App\Eloquents\Circle;
use App\Eloquents\User;
use App\Eloquents\Page;
use App\Eloquents\Email;
use App\Services\Pages\PagesService;
use DB;
use Mockery;

class StoreActionTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->staff = factory(User::class)->states('staff')->create();
    }

    /**
     * @test
     */
    public function お知らせを作成できる()
    {
        $this->mock(PagesService::class, function ($mock) {
            $mock->shouldReceive('createPage')->once()->with(
                'お知らせのタイトル',
                "本文です\n\n# 見出し\n- リストです\n- リストです",
                Mockery::on(function ($arg) {
                    return $this->staff->id === $arg->id && $this->staff->name === $arg->name;
                }),
                'スタッフ用メモです！123',
                ['Cブース', '屋外模擬店'],
            )->andReturn(new Page());
        });

        $responce = $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->post(route('staff.pages.store'), [
                'title' => 'お知らせのタイトル',
                'body' => "本文です\n\n# 見出し\n- リストです\n- リストです",
                'viewable_tags' => ['Cブース', '屋外模擬店'],
                'send_emails' => '0',
                'notes' => 'スタッフ用メモです！123',
            ]);

        $response->assertRedirect(route('staff.pages.create'));
    }

    /**
     * @test
     */
    public function タイトルと本文が未入力だとエラーが発生する()
    {
        $responce = $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->post(route('staff.pages.store'), [
                // 何もフォームの値を送らない
            ]);

        $response->assertSessionHasErrors(['title', 'body']);
    }

    /**
     * @test
     */
    public function 一斉配信予約ができる()
    {
        $page = new Page([
                'title' => '一斉配信するお知らせのタイトル',
            ]);

        $this->mock(PagesService::class, function ($mock) use ($page) {
            $mock->shouldReceive('createPage')->once()->with(
                $page->title,
                "本文です\n\n# 見出し\n- リストです\n- リストです",
                Mockery::on(function ($arg) {
                    return $this->staff->id === $arg->id && $this->staff->name === $arg->name;
                }),
                'スタッフ用メモです！123',
                ['Cブース', '屋外模擬店'],
            )->andReturn($page);

            $mock->shouldReceive('sendEmailsByPage')->once()->with(
                Mockery::on(function ($arg) use ($page) {
                    return $page->title === $arg->title;
                }),
            );
        });

        $responce = $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->post(route('staff.pages.store'), [
                'title' => $page->title,
                'body' => "本文です\n\n# 見出し\n- リストです\n- リストです",
                'viewable_tags' => ['Cブース', '屋外模擬店'],
                'send_emails' => '1',
                'notes' => 'スタッフ用メモです！123',
            ]);
    }
}

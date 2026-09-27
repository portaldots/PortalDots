<?php

namespace Tests\Feature\Http\Controllers\Staff\Pages;

use App\Contracts\AudiencePolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Eloquents\User;
use App\Eloquents\Page;
use App\Eloquents\Document;
use App\Eloquents\Permission;
use App\Services\Pages\PagesService;
use Mockery;

class StoreActionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var User
     */
    private $staff;

    /**
     * @var Document
     */
    private $document;

    public function setUp(): void
    {
        parent::setUp();
        $this->staff = factory(User::class)->states('staff')->create();
        $this->document = factory(Document::class)->create();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function お知らせを作成できる()
    {
        Permission::create(['name' => 'staff.pages.edit']);
        $this->staff->syncPermissions(['staff.pages.edit']);

        $this->mock(PagesService::class, function ($mock) {
            $mock->shouldReceive('createPage')->once()->with(
                'お知らせのタイトル',
                "本文です\n\n# 見出し\n- リストです\n- リストです",
                Mockery::on(function ($arg) {
                    return $this->staff->id === $arg->id && $this->staff->name === $arg->name;
                }),
                'スタッフ用メモです！123',
                ['Cブース', '屋外模擬店'],
                [$this->document->id],
                false,
                false,
                'selected',
                []
            )->andReturn(new Page());
        });

        $response = $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->post(route('staff.pages.store'), [
                'title' => 'お知らせのタイトル',
                'body' => "本文です\n\n# 見出し\n- リストです\n- リストです",
                'audience' => 'selected',
                'viewable_tags' => ['Cブース', '屋外模擬店'],
                'documents' => [(string)$this->document->id],
                'is_public' => '0',
                'is_pinned' => null,
                'send_emails' => '0',
                'notes' => 'スタッフ用メモです！123',
            ]);

        $response->assertRedirect(route('staff.pages.create'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 権限がない場合はお知らせを作成できない()
    {
        $response = $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->post(route('staff.pages.store'), [
                'title' => 'お知らせのタイトル',
                'body' => "本文です\n\n# 見出し\n- リストです\n- リストです",
                'viewable_tags' => ['Cブース', '屋外模擬店'],
                'documents' => [(string)$this->document->id],
                'is_public' => '0',
                'is_pinned' => null,
                'send_emails' => '0',
                'notes' => 'スタッフ用メモです！123',
            ]);

        $response->assertForbidden();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function タイトルと本文が未入力だとエラーが発生する()
    {
        Permission::create(['name' => 'staff.pages.edit']);
        $this->staff->syncPermissions(['staff.pages.edit']);

        $response = $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->post(route('staff.pages.store'), [
                // 何もフォームの値を送らない
            ]);

        $response->assertSessionHasErrors(['title', 'body']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 一斉配信予約ができる()
    {
        Permission::create(['name' => 'staff.pages.edit']);
        Permission::create(['name' => 'staff.pages.send_emails']);
        $this->staff->syncPermissions(['staff.pages.edit', 'staff.pages.send_emails']);

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
                [],
                // お知らせは非公開でもメール配信は可能
                false,
                false,
                'selected',
                [],
            )->andReturn($page);

            $mock->shouldReceive('sendEmailsByPage')->once()->with(
                Mockery::on(function ($arg) use ($page) {
                    return $page->title === $arg->title;
                }),
            );
        });

        $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->post(route('staff.pages.store'), [
                'title' => $page->title,
                'body' => "本文です\n\n# 見出し\n- リストです\n- リストです",
                'audience' => 'selected',
                'viewable_tags' => ['Cブース', '屋外模擬店'],
                'documents' => [],
                'is_public' => '0',
                'is_pinned' => null,
                'send_emails' => '1',
                'notes' => 'スタッフ用メモです！123',
            ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function AudiencePolicyでselectedのみ許可されている場合everyoneとsigned_inは拒否される()
    {
        Permission::create(['name' => 'staff.pages.edit']);
        $this->staff->syncPermissions(['staff.pages.edit']);

        $this->app->bind(AudiencePolicy::class, function () {
            return new class implements AudiencePolicy {
                public function allowedAudiences(): array
                {
                    return [self::SELECTED];
                }
            };
        });

        $everyoneResponse = $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->post(route('staff.pages.store'), [
                'title' => 'お知らせのタイトル',
                'body' => '本文',
                'audience' => 'everyone',
                'is_public' => '1',
                'is_pinned' => null,
                'send_emails' => '0',
            ]);
        $everyoneResponse->assertSessionHasErrors(['audience']);

        $signedInResponse = $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->post(route('staff.pages.store'), [
                'title' => 'お知らせのタイトル',
                'body' => '本文',
                'audience' => 'signed_in',
                'is_public' => '1',
                'is_pinned' => null,
                'send_emails' => '0',
            ]);
        $signedInResponse->assertSessionHasErrors(['audience']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 存在しない企画を閲覧可能な企画に指定するとエラーが発生する()
    {
        Permission::create(['name' => 'staff.pages.edit']);
        $this->staff->syncPermissions(['staff.pages.edit']);

        $response = $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->post(route('staff.pages.store'), [
                'title' => 'お知らせのタイトル',
                'body' => '本文',
                'audience' => 'selected',
                'viewable_circles' => [999999],
                'is_public' => '1',
                'is_pinned' => null,
                'send_emails' => '0',
            ]);

        $response->assertSessionHasErrors(['viewable_circles.0']);
        $this->assertSame(0, Page::count());
    }
}

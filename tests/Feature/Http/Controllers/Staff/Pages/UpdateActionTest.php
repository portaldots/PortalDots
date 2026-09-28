<?php

namespace Tests\Feature\Http\Controllers\Staff\Pages;

use App\Contracts\AudiencePolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Eloquents\User;
use App\Eloquents\Page;
use App\Eloquents\Circle;
use App\Eloquents\Document;
use App\Eloquents\Permission;
use App\Services\Pages\PagesService;
use Mockery;

class UpdateActionTest extends TestCase
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
    public function お知らせを更新できる()
    {
        Permission::create(['name' => 'staff.pages.edit']);
        $this->staff->syncPermissions(['staff.pages.edit']);

        $page = factory(Page::class)->create();

        $this->mock(PagesService::class, function ($mock) use ($page) {
            $mock->shouldReceive('updatePage')->once()->with(
                Mockery::on(function ($arg) use ($page) {
                    return $page->id === $arg->id;
                }),
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
            )->andReturn(true);
        });

        $response = $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->patch(route('staff.pages.update', ['page' => $page]), [
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

        $response->assertRedirect(route('staff.pages.edit', ['page' => $page]));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 権限がない場合はお知らせを更新できない()
    {
        $page = factory(Page::class)->create();

        $response = $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->patch(route('staff.pages.update', ['page' => $page]), [
                'title' => 'お知らせのタイトル',
                'body' => "本文です\n\n# 見出し\n- リストです\n- リストです",
                'audience' => 'everyone',
                'is_public' => '0',
                'is_pinned' => null,
                'send_emails' => '0',
            ]);

        $response->assertForbidden();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function AudiencePolicyでselectedのみ許可されている場合everyoneとsigned_inは拒否される()
    {
        Permission::create(['name' => 'staff.pages.edit']);
        $this->staff->syncPermissions(['staff.pages.edit']);

        $page = factory(Page::class)->create();

        $this->app->bind(AudiencePolicy::class, function () {
            return new class implements AudiencePolicy {
                public function allowedAudiences(): array
                {
                    return [self::SELECTED];
                }

                public function allowsTagTargets(): bool
                {
                    return true;
                }
            };
        });

        $response = $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->patch(route('staff.pages.update', ['page' => $page]), [
                'title' => 'お知らせのタイトル',
                'body' => '本文',
                'audience' => 'everyone',
                'is_public' => '1',
                'is_pinned' => null,
                'send_emails' => '0',
            ]);

        $response->assertSessionHasErrors(['audience']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function タグの指定を許可しないAudiencePolicyの場合タグを指定するとエラーになる()
    {
        Permission::create(['name' => 'staff.pages.edit']);
        $this->staff->syncPermissions(['staff.pages.edit']);

        $page = factory(Page::class)->create();

        $this->app->bind(AudiencePolicy::class, function () {
            return new class implements AudiencePolicy {
                public function allowedAudiences(): array
                {
                    return [self::SELECTED];
                }

                public function allowsTagTargets(): bool
                {
                    return false;
                }
            };
        });

        $response = $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->patch(route('staff.pages.update', ['page' => $page]), [
                'title' => 'お知らせのタイトル',
                'body' => '本文',
                'audience' => 'selected',
                'viewable_tags' => ['Cブース'],
                'is_public' => '1',
                'is_pinned' => null,
                'send_emails' => '0',
            ]);

        $response->assertSessionHasErrors(['viewable_tags']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function タグの指定を許可しないAudiencePolicyの場合企画のみでselectedに更新できる()
    {
        Permission::create(['name' => 'staff.pages.edit']);
        $this->staff->syncPermissions(['staff.pages.edit']);

        $page = factory(Page::class)->create();
        $circle = factory(Circle::class)->create();

        $this->app->bind(AudiencePolicy::class, function () {
            return new class implements AudiencePolicy {
                public function allowedAudiences(): array
                {
                    return [self::SELECTED];
                }

                public function allowsTagTargets(): bool
                {
                    return false;
                }
            };
        });

        $response = $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->patch(route('staff.pages.update', ['page' => $page]), [
                'title' => 'お知らせのタイトル',
                'body' => '本文',
                'audience' => 'selected',
                'viewable_circles' => [$circle->id],
                'is_public' => '1',
                'is_pinned' => null,
                'send_emails' => '0',
            ]);

        $response->assertSessionDoesntHaveErrors(['audience', 'viewable_tags', 'viewable_circles']);
        $this->assertDatabaseHas('pages', [
            'id' => $page->id,
            'audience' => 'selected',
        ]);
    }
}

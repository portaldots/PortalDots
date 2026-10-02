<?php

namespace Tests\Feature\Http\Controllers\Staff\Pages;

use App\Contracts\AudiencePolicy;
use App\Eloquents\Page;
use App\Eloquents\Permission;
use App\Eloquents\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EditActionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var User
     */
    private $staff;

    public function setUp(): void
    {
        parent::setUp();
        $this->staff = factory(User::class)->states('staff')->create();
        Permission::create(['name' => 'staff.pages.edit']);
        $this->staff->syncPermissions(['staff.pages.edit']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function タグの指定を許可しないAudiencePolicyの場合タグ入力が表示されない()
    {
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
            ->get(route('staff.pages.edit', ['page' => $page]));

        $response->assertOk();
        $response->assertDontSee('閲覧可能なタグ');
        $response->assertDontSee('viewable_tags', false);
        $response->assertSee('閲覧可能な企画');
    }
}

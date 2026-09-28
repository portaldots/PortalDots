<?php

namespace Tests\Feature\Http\Controllers\Staff\Forms;

use App\Contracts\AudiencePolicy;
use App\Eloquents\Permission;
use App\Eloquents\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateActionTest extends TestCase
{
    use RefreshDatabase;

    private User $staff;

    public function setUp(): void
    {
        parent::setUp();
        $this->staff = factory(User::class)->states('staff')->create();
        Permission::create(['name' => 'staff.forms.edit']);
        $this->staff->syncPermissions(['staff.forms.edit']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function タグの指定を許可するAudiencePolicyの場合タグ入力が表示される()
    {
        $response = $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->get(route('staff.forms.create'));

        $response->assertOk();
        $response->assertSee('フォームへ回答可能なユーザー');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function タグの指定を許可しないAudiencePolicyの場合タグ入力が表示されない()
    {
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
            ->get(route('staff.forms.create'));

        $response->assertOk();
        $response->assertDontSee('フォームへ回答可能なユーザー');
        $response->assertDontSee('answerable_tags', false);
    }
}

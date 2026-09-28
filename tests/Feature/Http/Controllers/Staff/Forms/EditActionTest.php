<?php

namespace Tests\Feature\Http\Controllers\Staff\Forms;

use App\Contracts\AudiencePolicy;
use App\Eloquents\Form;
use App\Eloquents\Permission;
use App\Eloquents\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EditActionTest extends TestCase
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
    public function タグの指定を許可しないAudiencePolicyの場合タグ入力が表示されない()
    {
        $form = factory(Form::class)->create();

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
            ->get(route('staff.forms.edit', ['form' => $form]));

        $response->assertOk();
        $response->assertDontSee('フォームへ回答可能なユーザー');
        $response->assertDontSee('answerable_tags', false);
    }
}

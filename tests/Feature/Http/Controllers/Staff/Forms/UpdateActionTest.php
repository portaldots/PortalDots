<?php

namespace Tests\Feature\Http\Controllers\Staff\Forms;

use App\Contracts\AudiencePolicy;
use App\Eloquents\Circle;
use App\Eloquents\Form;
use App\Eloquents\FormAssignment;
use App\Eloquents\Permission;
use App\Eloquents\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateActionTest extends TestCase
{
    use RefreshDatabase;

    private User $staff;
    private Form $form;

    public function setUp(): void
    {
        parent::setUp();
        $this->staff = factory(User::class)->states('staff')->create();
        Permission::create(['name' => 'staff.forms.edit']);
        $this->staff->syncPermissions(['staff.forms.edit']);

        $this->form = factory(Form::class)->create([
            'name' => '更新前のフォーム',
            'audience' => 'everyone',
        ]);
    }

    private function baseParams(array $overrides = []): array
    {
        return array_merge([
            'name' => '更新後のフォーム',
            'open_at' => '2026-01-01 00:00',
            'close_at' => '2026-12-31 23:59',
            'max_answers' => 1,
            'is_public' => '1',
            'audience' => 'everyone',
        ], $overrides);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 送付先の企画があればタグがなくてもselectedにできる()
    {
        $circle = factory(Circle::class)->create();
        FormAssignment::create([
            'form_id' => $this->form->id,
            'circle_id' => $circle->id,
        ]);

        $response = $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->patch(route('staff.forms.update', ['form' => $this->form]), $this->baseParams([
                'audience' => 'selected',
            ]));

        $response->assertSessionDoesntHaveErrors(['audience']);
        $this->assertDatabaseHas('forms', [
            'id' => $this->form->id,
            'audience' => 'selected',
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 送付先の企画もタグもなければselectedにできない()
    {
        $response = $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->patch(route('staff.forms.update', ['form' => $this->form]), $this->baseParams([
                'audience' => 'selected',
            ]));

        $response->assertSessionHasErrors(['audience']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function タグの指定を許可しないAudiencePolicyの場合タグを指定するとエラーになる()
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
            ->patch(route('staff.forms.update', ['form' => $this->form]), $this->baseParams([
                'audience' => 'selected',
                'answerable_tags' => ['Aタグ'],
            ]));

        $response->assertSessionHasErrors(['answerable_tags']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function タグの指定を許可しないAudiencePolicyの場合送付先の企画があればタグなしでselectedに更新できる()
    {
        $circle = factory(Circle::class)->create();
        FormAssignment::create([
            'form_id' => $this->form->id,
            'circle_id' => $circle->id,
        ]);

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
            ->patch(route('staff.forms.update', ['form' => $this->form]), $this->baseParams([
                'audience' => 'selected',
            ]));

        $response->assertSessionDoesntHaveErrors(['audience', 'answerable_tags']);
        $this->assertDatabaseHas('forms', [
            'id' => $this->form->id,
            'audience' => 'selected',
        ]);
    }
}

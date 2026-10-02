<?php

namespace Tests\Feature\Http\Controllers\Staff\Forms;

use App\Contracts\AudiencePolicy;
use App\Eloquents\Permission;
use App\Eloquents\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreActionTest extends TestCase
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

    private function baseParams(array $overrides = []): array
    {
        return array_merge([
            'name' => 'テストフォーム',
            'open_at' => '2026-01-01 00:00',
            'close_at' => '2026-12-31 23:59',
            'max_answers' => 1,
            'is_public' => '1',
            'audience' => 'everyone',
        ], $overrides);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function everyoneのフォームを作成できる()
    {
        $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->post(route('staff.forms.store'), $this->baseParams());

        $this->assertDatabaseHas('forms', [
            'name' => 'テストフォーム',
            'audience' => 'everyone',
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function タグを指定してselectedのフォームを作成できる()
    {
        $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->post(route('staff.forms.store'), $this->baseParams([
                'audience' => 'selected',
                'answerable_tags' => ['Aタグ'],
            ]));

        $this->assertDatabaseHas('forms', [
            'name' => 'テストフォーム',
            'audience' => 'selected',
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function selectedでタグも送付先の企画も指定しない場合エラーになる()
    {
        $response = $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->post(route('staff.forms.store'), $this->baseParams([
                'audience' => 'selected',
            ]));

        $response->assertSessionHasErrors(['audience']);
        $this->assertDatabaseMissing('forms', ['name' => 'テストフォーム']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function AudiencePolicyでselectedのみ許可されている場合everyoneは拒否される()
    {
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
            ->post(route('staff.forms.store'), $this->baseParams([
                'audience' => 'everyone',
            ]));

        $response->assertSessionHasErrors(['audience']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function signed_inはフォームの公開範囲として選択できない()
    {
        $response = $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->post(route('staff.forms.store'), $this->baseParams([
                'audience' => 'signed_in',
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
            ->post(route('staff.forms.store'), $this->baseParams([
                'audience' => 'selected',
                'answerable_tags' => ['Aタグ'],
            ]));

        $response->assertSessionHasErrors(['answerable_tags']);
        $this->assertDatabaseMissing('forms', ['name' => 'テストフォーム']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function タグの指定を許可しないAudiencePolicyの場合新規作成時はまだ企画を送付できないためselectedにできない()
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
            ->post(route('staff.forms.store'), $this->baseParams([
                'audience' => 'selected',
            ]));

        $response->assertSessionHasErrors(['audience']);
        $this->assertDatabaseMissing('forms', ['name' => 'テストフォーム']);
    }
}

<?php

namespace Tests\Feature\Http\Controllers\Staff\Documents;

use App\Contracts\AudiencePolicy;
use App\Eloquents\Circle;
use App\Eloquents\Document;
use App\Eloquents\Permission;
use App\Eloquents\User;
use App\Services\Documents\DocumentsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Mockery;

class UpdateActionTest extends TestCase
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
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function DocumentsServiceのupdateDocumentが呼び出される()
    {
        Permission::create(['name' => 'staff.documents.edit']);
        $this->staff->syncPermissions(['staff.documents.edit']);

        $document = factory(Document::class)->create();

        $this->mock(DocumentsService::class, function ($mock) use ($document) {
            $mock->shouldReceive('updateDocument')->once()->with(
                Mockery::on(function ($arg) use ($document) {
                    return $document->id === $arg->id;
                }),
                'document name',
                'document description',
                null,
                false,
                true,
                'notes',
                'everyone',
                [],
                [],
                Mockery::on(function ($arg) {
                    return $arg->id === $this->staff->id;
                })
            )->andReturn(true);
        });

        $response = $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->from(route('staff.documents.edit', ['document' => $document]))
            ->patch(route('staff.documents.update', ['document' => $document]), [
                'name' => 'document name',
                'description' => 'document description',
                'is_public' => '0',
                'is_important' => '1',
                'audience' => 'everyone',
                'notes' => 'notes',
            ]);

        $response->assertSessionHasNoErrors();

        $response->assertRedirect(route('staff.documents.edit', ['document' => $document]));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 権限がない場合は配布資料を更新できない()
    {
        $document = factory(Document::class)->create();

        $response = $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->patch(route('staff.documents.update', ['document' => $document]), [
                'name' => 'document name',
                'description' => 'document description',
                'is_public' => '0',
                'is_important' => '1',
                'notes' => 'notes',
            ]);

        $response->assertForbidden();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function AudiencePolicyでselectedのみ許可されている場合everyoneとsigned_inは拒否される()
    {
        Permission::create(['name' => 'staff.documents.edit']);
        $this->staff->syncPermissions(['staff.documents.edit']);

        $document = factory(Document::class)->create();

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
            ->patch(route('staff.documents.update', ['document' => $document]), [
                'name' => 'document name',
                'description' => 'document description',
                'is_public' => '1',
                'is_important' => '0',
                'audience' => 'signed_in',
                'notes' => 'notes',
            ]);

        $response->assertSessionHasErrors(['audience']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function タグの指定を許可しないAudiencePolicyの場合タグを指定するとエラーになる()
    {
        Permission::create(['name' => 'staff.documents.edit']);
        $this->staff->syncPermissions(['staff.documents.edit']);

        $document = factory(Document::class)->create();

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
            ->patch(route('staff.documents.update', ['document' => $document]), [
                'name' => 'document name',
                'description' => 'document description',
                'is_public' => '1',
                'is_important' => '0',
                'audience' => 'selected',
                'viewable_tags' => ['Cブース'],
                'notes' => 'notes',
            ]);

        $response->assertSessionHasErrors(['viewable_tags']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function タグの指定を許可しないAudiencePolicyの場合企画のみでselectedに更新できる()
    {
        Permission::create(['name' => 'staff.documents.edit']);
        $this->staff->syncPermissions(['staff.documents.edit']);

        $document = factory(Document::class)->create();
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
            ->patch(route('staff.documents.update', ['document' => $document]), [
                'name' => 'document name',
                'description' => 'document description',
                'is_public' => '1',
                'is_important' => '0',
                'audience' => 'selected',
                'viewable_circles' => [$circle->id],
                'notes' => 'notes',
            ]);

        $response->assertSessionDoesntHaveErrors(['audience', 'viewable_tags', 'viewable_circles']);
        $this->assertDatabaseHas('documents', [
            'id' => $document->id,
            'audience' => 'selected',
        ]);
    }
}

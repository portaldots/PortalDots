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
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class StoreActionTest extends TestCase
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
    public function DocumentsServiceのcreateDocumentが呼び出される()
    {
        Permission::create(['name' => 'staff.documents.edit']);
        $this->staff->syncPermissions(['staff.documents.edit']);

        Storage::fake('local');

        $filesize = 1;  // 単位 : KiB
        $file = UploadedFile::fake()->create('配布資料.pdf', $filesize, 'application/pdf');

        $document = factory(Document::class)->create([
            'path' => "documents/{$file->hashName()}.pdf",
            'size' => $filesize * 1024, // 単位 : バイト
            'extension' => 'pdf',
        ]);

        $this->mock(DocumentsService::class, function ($mock) use ($document) {
            $mock->shouldReceive('createDocument')->once()->with(
                'document name',
                'document description',
                Mockery::any(),
                false,
                true,
                'notes',
                'everyone',
                [],
                [],
                Mockery::on(function ($arg) {
                    return $arg->id === $this->staff->id;
                })
            )->andReturn($document);
        });

        $response = $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->post(route('staff.documents.store'), [
                'name' => 'document name',
                'description' => 'document description',
                'file' => $file,
                'is_public' => '0',
                'is_important' => '1',
                'audience' => 'everyone',
                'notes' => 'notes',
            ]);

        $response->assertSessionHasNoErrors();

        $response->assertRedirect(route('staff.documents.create'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 権限がない場合は配布資料を保存できない()
    {
        $filesize = 1;  // 単位 : KiB
        $file = UploadedFile::fake()->create('配布資料.pdf', $filesize, 'application/pdf');

        $response = $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->post(route('staff.documents.store'), [
                'name' => 'document name',
                'description' => 'document description',
                'file' => $file,
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

        Storage::fake('local');
        $file = UploadedFile::fake()->create('配布資料.pdf', 1, 'application/pdf');

        $response = $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->post(route('staff.documents.store'), [
                'name' => 'document name',
                'description' => 'document description',
                'file' => $file,
                'is_public' => '1',
                'is_important' => '0',
                'audience' => 'everyone',
                'notes' => 'notes',
            ]);

        $response->assertSessionHasErrors(['audience']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function タグの指定を許可しないAudiencePolicyの場合タグを指定するとエラーになる()
    {
        Permission::create(['name' => 'staff.documents.edit']);
        $this->staff->syncPermissions(['staff.documents.edit']);

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

        Storage::fake('local');
        $file = UploadedFile::fake()->create('配布資料.pdf', 1, 'application/pdf');

        $response = $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->post(route('staff.documents.store'), [
                'name' => 'document name',
                'description' => 'document description',
                'file' => $file,
                'is_public' => '1',
                'is_important' => '0',
                'audience' => 'selected',
                'viewable_tags' => ['Cブース'],
                'notes' => 'notes',
            ]);

        $response->assertSessionHasErrors(['viewable_tags']);
        $this->assertSame(0, Document::count());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function タグの指定を許可しないAudiencePolicyの場合企画のみでselectedの配布資料を作成できる()
    {
        Permission::create(['name' => 'staff.documents.edit']);
        $this->staff->syncPermissions(['staff.documents.edit']);

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

        Storage::fake('local');
        $file = UploadedFile::fake()->create('配布資料.pdf', 1, 'application/pdf');

        $response = $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->post(route('staff.documents.store'), [
                'name' => 'document name',
                'description' => 'document description',
                'file' => $file,
                'is_public' => '1',
                'is_important' => '0',
                'audience' => 'selected',
                'viewable_circles' => [$circle->id],
                'notes' => 'notes',
            ]);

        $response->assertSessionDoesntHaveErrors(['audience', 'viewable_tags', 'viewable_circles']);
        $this->assertDatabaseHas('documents', [
            'name' => 'document name',
            'audience' => 'selected',
        ]);
    }
}

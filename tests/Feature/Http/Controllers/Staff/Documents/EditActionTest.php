<?php

namespace Tests\Feature\Http\Controllers\Staff\Documents;

use App\Contracts\AudiencePolicy;
use App\Eloquents\Circle;
use App\Eloquents\Permission;
use App\Eloquents\User;
use App\Services\Documents\DocumentApprovalsService;
use App\Services\Documents\DocumentsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EditActionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var DocumentsService
     */
    private $documentsService;

    /**
     * @var User
     */
    private $staff;

    public function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        $this->documentsService = App::make(DocumentsService::class);
        $this->staff = factory(User::class)->states('staff')->create();

        Permission::create(['name' => 'staff.documents.edit']);
        $this->staff->syncPermissions(['staff.documents.edit']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 版が1つでも版の履歴が表示される()
    {
        $document = $this->documentsService->createDocument(
            '配布資料',
            null,
            UploadedFile::fake()->create('第1版.pdf', 1, 'application/pdf'),
            true,
            false,
            null,
            'everyone',
            [],
            [],
            $this->staff
        );

        $response = $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->get(route('staff.documents.edit', ['document' => $document]));

        $response->assertOk();
        $response->assertSee('版の履歴');
        $response->assertSee('第1版');
        $response->assertSee('新しい版のファイル');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 版が複数の場合は版の履歴とアップロードしたスタッフが表示される()
    {
        $document = $this->documentsService->createDocument(
            '配布資料',
            null,
            UploadedFile::fake()->create('第1版.pdf', 1, 'application/pdf'),
            true,
            false,
            null,
            'everyone',
            [],
            [],
            $this->staff
        );
        $this->documentsService->updateDocument(
            $document,
            '配布資料',
            null,
            UploadedFile::fake()->create('第2版.pdf', 1, 'application/pdf'),
            true,
            false,
            null,
            'everyone',
            [],
            [],
            $this->staff
        );

        $response = $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->get(route('staff.documents.edit', ['document' => $document]));

        $response->assertOk();
        $response->assertSee('版の履歴');
        $response->assertSee('第1版');
        $response->assertSee('第2版');
        $response->assertSee($this->staff->name);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 確認依頼済みの企画とステータスが表示される()
    {
        $document = $this->documentsService->createDocument(
            '配布資料',
            null,
            UploadedFile::fake()->create('第1版.pdf', 1, 'application/pdf'),
            true,
            false,
            null,
            'everyone',
            [],
            [],
            $this->staff
        );
        $circle = factory(Circle::class)->create();
        App::make(DocumentApprovalsService::class)->requestForCircles($document, [$circle->id], $this->staff);

        $response = $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->get(route('staff.documents.edit', ['document' => $document]));

        $response->assertOk();
        $response->assertSee('確認依頼');
        $response->assertSee($circle->name);
        $response->assertSee('確認待ち');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function タグの指定を許可しないAudiencePolicyの場合タグ入力が表示されない()
    {
        $document = $this->documentsService->createDocument(
            '配布資料',
            null,
            UploadedFile::fake()->create('第1版.pdf', 1, 'application/pdf'),
            true,
            false,
            null,
            'everyone',
            [],
            [],
            $this->staff
        );

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
            ->get(route('staff.documents.edit', ['document' => $document]));

        $response->assertOk();
        $response->assertDontSee('閲覧可能なタグ');
        $response->assertDontSee('viewable_tags', false);
        $response->assertSee('閲覧可能な企画');
    }
}

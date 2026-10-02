<?php

namespace Tests\Feature\Http\Controllers\Documents\Approvals;

use App\Eloquents\Circle;
use App\Eloquents\Document;
use App\Eloquents\DocumentApproval;
use App\Eloquents\User;
use App\Policies\DocumentApprovalPolicy;
use App\Services\Circles\SelectorService;
use App\Services\Documents\DocumentApprovalsService;
use App\Services\Documents\DocumentsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ApproveActionTest extends TestCase
{
    use RefreshDatabase;

    private DocumentsService $documentsService;
    private DocumentApprovalsService $documentApprovalsService;
    private SelectorService $selectorService;
    private User $staff;
    private Document $document;
    private Circle $circle;
    private User $member;
    private DocumentApproval $approval;

    public function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');

        $this->documentsService = App::make(DocumentsService::class);
        $this->documentApprovalsService = App::make(DocumentApprovalsService::class);
        $this->selectorService = App::make(SelectorService::class);

        $this->staff = factory(User::class)->states('staff')->create();
        $this->document = $this->documentsService->createDocument(
            '配布資料',
            null,
            UploadedFile::fake()->create('第１版.pdf', 1, 'application/pdf'),
            true,
            false,
            null,
            'everyone',
            [],
            [],
            $this->staff
        );

        $this->circle = factory(Circle::class)->create();
        $this->member = factory(User::class)->create();
        $this->member->circles()->attach($this->circle->id);

        $this->documentApprovalsService->requestForCircles($this->document, [$this->circle->id], $this->staff);
        $this->approval = $this->document->approvals()->where('circle_id', $this->circle->id)->firstOrFail();
        $this->selectorService->setCircle($this->circle);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 確認済みにできる()
    {
        $response = $this->actingAs($this->member)
            ->post(route('documents.approval.approve', ['document' => $this->document]), [
                'document_version_id' => $this->approval->document_version_id,
                'lock_version' => $this->approval->lock_version,
            ]);

        $response->assertRedirect(route('documents.approval.show', ['document' => $this->document]));
        $this->approval->refresh();
        $this->assertSame(DocumentApproval::STATUS_APPROVED, $this->approval->status);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 古いlock_versionでは変更されずセッションへ戻る()
    {
        $response = $this->actingAs($this->member)
            ->post(route('documents.approval.approve', ['document' => $this->document]), [
                'document_version_id' => $this->approval->document_version_id,
                'lock_version' => $this->approval->lock_version + 1,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('topAlert.type', 'danger');
        $this->approval->refresh();
        $this->assertSame(DocumentApproval::STATUS_PENDING, $this->approval->status);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 古い版のdocument_version_idでは変更されずJSONは409を返す()
    {
        $this->documentsService->updateDocument(
            $this->document,
            $this->document->name,
            $this->document->description,
            UploadedFile::fake()->create('第２版.pdf', 1, 'application/pdf'),
            true,
            false,
            null,
            'everyone',
            [],
            [],
            $this->staff
        );
        $this->approval->refresh();
        $currentLockVersion = $this->approval->lock_version;
        $staleVersionId = $this->document->versions()->reorder('version', 'asc')->first()->id;

        $response = $this->actingAs($this->member)
            ->postJson(route('documents.approval.approve', ['document' => $this->document]), [
                'document_version_id' => $staleVersionId,
                'lock_version' => $currentLockVersion,
            ]);

        $response->assertStatus(409);
        $this->approval->refresh();
        $this->assertSame(DocumentApproval::STATUS_PENDING, $this->approval->status);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 同じ内容の二重送信は決定を1つしか記録しない()
    {
        $params = [
            'document_version_id' => $this->approval->document_version_id,
            'lock_version' => $this->approval->lock_version,
        ];

        $this->actingAs($this->member)
            ->post(route('documents.approval.approve', ['document' => $this->document]), $params);
        $this->actingAs($this->member)
            ->post(route('documents.approval.approve', ['document' => $this->document]), $params);

        $this->assertDatabaseCount('document_approval_decisions', 2); // 依頼 + 承認の1回分
        $this->approval->refresh();
        $this->assertSame(DocumentApproval::STATUS_APPROVED, $this->approval->status);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 企画に所属しないユーザーは決定できない()
    {
        $nonMember = factory(User::class)->create();

        $canDecide = (new DocumentApprovalPolicy())->decide($nonMember, $this->approval);

        $this->assertFalse($canDecide);
    }
}

<?php

namespace Tests\Feature\Services\Documents;

use App\Eloquents\Circle;
use App\Eloquents\Document;
use App\Eloquents\DocumentApproval;
use App\Eloquents\User;
use App\Exceptions\Documents\StaleDocumentApprovalException;
use App\Services\Documents\DocumentApprovalsService;
use App\Services\Documents\DocumentsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentApprovalsServiceTest extends TestCase
{
    use RefreshDatabase;

    private DocumentApprovalsService $documentApprovalsService;
    private DocumentsService $documentsService;
    private User $staff;
    private Document $document;

    public function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');

        $this->documentApprovalsService = App::make(DocumentApprovalsService::class);
        $this->documentsService = App::make(DocumentsService::class);
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
    }

    private function memberOf(Circle $circle): User
    {
        $user = factory(User::class)->create();
        $user->circles()->attach($circle->id);
        return $user;
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function requestForCircles_複数の企画へ現在の版の確認待ちとして依頼できる()
    {
        $circleA = factory(Circle::class)->create();
        $circleB = factory(Circle::class)->create();
        $version = $this->document->versions()->first();

        $this->documentApprovalsService->requestForCircles(
            $this->document,
            [$circleA->id, $circleB->id],
            $this->staff
        );

        $this->assertDatabaseCount('document_approvals', 2);
        $this->assertDatabaseHas('document_approvals', [
            'document_id' => $this->document->id,
            'circle_id' => $circleA->id,
            'document_version_id' => $version->id,
            'status' => DocumentApproval::STATUS_PENDING,
            'requested_by' => $this->staff->id,
        ]);
        $this->assertDatabaseHas('document_approvals', [
            'document_id' => $this->document->id,
            'circle_id' => $circleB->id,
            'document_version_id' => $version->id,
            'status' => DocumentApproval::STATUS_PENDING,
        ]);
        $this->assertDatabaseCount('document_approval_decisions', 2);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function requestForCircles_現在の版で既に確認待ちの場合は何もしない()
    {
        $circle = factory(Circle::class)->create();
        $this->documentApprovalsService->requestForCircles($this->document, [$circle->id], $this->staff);

        $this->documentApprovalsService->requestForCircles($this->document, [$circle->id], $this->staff);

        $this->assertDatabaseCount('document_approvals', 1);
        $this->assertDatabaseCount('document_approval_decisions', 1);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function requestForCircles_修正対応中の企画へ再依頼すると確認待ちへリセットされる()
    {
        $circle = factory(Circle::class)->create();
        $member = $this->memberOf($circle);
        $this->documentApprovalsService->requestForCircles($this->document, [$circle->id], $this->staff);
        $approval = DocumentApproval::where('circle_id', $circle->id)->firstOrFail();

        $this->documentApprovalsService->decide(
            $approval,
            $member,
            DocumentApproval::STATUS_CHANGES_REQUESTED,
            '誤字があります',
            $approval->document_version_id,
            $approval->lock_version
        );

        $this->documentApprovalsService->requestForCircles($this->document, [$circle->id], $this->staff);

        $approval->refresh();
        $this->assertSame(DocumentApproval::STATUS_PENDING, $approval->status);
        $this->assertDatabaseCount('document_approval_decisions', 3);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function cancelRequest_依頼と履歴が削除される()
    {
        $circle = factory(Circle::class)->create();
        $this->documentApprovalsService->requestForCircles($this->document, [$circle->id], $this->staff);
        $approval = DocumentApproval::where('circle_id', $circle->id)->firstOrFail();

        $this->documentApprovalsService->cancelRequest($this->document, $circle);

        $this->assertDatabaseMissing('document_approvals', ['id' => $approval->id]);
        $this->assertDatabaseMissing('document_approval_decisions', ['document_approval_id' => $approval->id]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function ラウンドトリップ_依頼から新版での再依頼と承認まで()
    {
        $circle = factory(Circle::class)->create();
        $member = $this->memberOf($circle);

        // スタッフが企画へ依頼する
        $this->documentApprovalsService->requestForCircles($this->document, [$circle->id], $this->staff);
        $approval = DocumentApproval::where('circle_id', $circle->id)->firstOrFail();
        $v1 = $this->document->versions()->first();
        $this->assertSame($v1->id, $approval->document_version_id);

        // 企画が修正を依頼する
        $this->documentApprovalsService->decide(
            $approval,
            $member,
            DocumentApproval::STATUS_CHANGES_REQUESTED,
            '表紙のタイトルを直してください',
            $v1->id,
            $approval->lock_version
        );
        $approval->refresh();
        $this->assertSame(DocumentApproval::STATUS_CHANGES_REQUESTED, $approval->status);

        // スタッフが新しい版をアップロードする
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
        $this->document->refresh();
        $v2 = $this->document->versions()->orderByDesc('version')->first();
        $this->assertSame(2, $v2->version);

        $approval->refresh();
        $this->assertSame(DocumentApproval::STATUS_PENDING, $approval->status);
        $this->assertSame($v2->id, $approval->document_version_id);

        // 第1版に対する修正依頼の履歴が残っている
        $v1Decision = $approval->decisions()
            ->where('document_version_id', $v1->id)
            ->where('status', DocumentApproval::STATUS_CHANGES_REQUESTED)
            ->first();
        $this->assertNotNull($v1Decision);
        $this->assertSame('表紙のタイトルを直してください', $v1Decision->comment);

        // 企画が第2版を承認する
        $this->documentApprovalsService->decide(
            $approval,
            $member,
            DocumentApproval::STATUS_APPROVED,
            null,
            $v2->id,
            $approval->lock_version
        );
        $approval->refresh();
        $this->assertSame(DocumentApproval::STATUS_APPROVED, $approval->status);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function decide_lock_versionが一致しない場合は何も変更せずStaleDocumentApprovalExceptionをthrowする()
    {
        $circle = factory(Circle::class)->create();
        $member = $this->memberOf($circle);
        $this->documentApprovalsService->requestForCircles($this->document, [$circle->id], $this->staff);
        $approval = DocumentApproval::where('circle_id', $circle->id)->firstOrFail();
        $originalLockVersion = $approval->lock_version;

        $this->expectException(StaleDocumentApprovalException::class);
        try {
            $this->documentApprovalsService->decide(
                $approval,
                $member,
                DocumentApproval::STATUS_APPROVED,
                null,
                $approval->document_version_id,
                $originalLockVersion + 1
            );
        } finally {
            $approval->refresh();
            $this->assertSame(DocumentApproval::STATUS_PENDING, $approval->status);
            $this->assertSame($originalLockVersion, $approval->lock_version);
            $this->assertDatabaseCount('document_approval_decisions', 1);
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function decide_古い版のdocument_version_idでは何も変更せずStaleDocumentApprovalExceptionをthrowする()
    {
        $circle = factory(Circle::class)->create();
        $member = $this->memberOf($circle);
        $this->documentApprovalsService->requestForCircles($this->document, [$circle->id], $this->staff);
        $approval = DocumentApproval::where('circle_id', $circle->id)->firstOrFail();
        $oldVersionId = $approval->document_version_id;

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
        $approval->refresh();
        $currentLockVersion = $approval->lock_version;

        $this->expectException(StaleDocumentApprovalException::class);
        try {
            $this->documentApprovalsService->decide(
                $approval,
                $member,
                DocumentApproval::STATUS_APPROVED,
                null,
                $oldVersionId,
                $currentLockVersion
            );
        } finally {
            $approval->refresh();
            $this->assertSame(DocumentApproval::STATUS_PENDING, $approval->status);
            $this->assertSame($currentLockVersion, $approval->lock_version);
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function decide_同じlock_versionでの二重送信は決定を1つしか記録しない()
    {
        $circle = factory(Circle::class)->create();
        $member = $this->memberOf($circle);
        $this->documentApprovalsService->requestForCircles($this->document, [$circle->id], $this->staff);
        $approval = DocumentApproval::where('circle_id', $circle->id)->firstOrFail();
        $lockVersion = $approval->lock_version;
        $versionId = $approval->document_version_id;

        $this->documentApprovalsService->decide(
            $approval,
            $member,
            DocumentApproval::STATUS_APPROVED,
            null,
            $versionId,
            $lockVersion
        );

        $this->expectException(StaleDocumentApprovalException::class);
        try {
            $this->documentApprovalsService->decide(
                $approval,
                $member,
                DocumentApproval::STATUS_APPROVED,
                null,
                $versionId,
                $lockVersion
            );
        } finally {
            $this->assertDatabaseCount('document_approval_decisions', 2);
            $approval->refresh();
            $this->assertSame(DocumentApproval::STATUS_APPROVED, $approval->status);
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 同じ資料への企画Aと企画Bの依頼は互いに独立している()
    {
        $circleA = factory(Circle::class)->create();
        $circleB = factory(Circle::class)->create();
        $memberA = $this->memberOf($circleA);

        $this->documentApprovalsService->requestForCircles(
            $this->document,
            [$circleA->id, $circleB->id],
            $this->staff
        );
        $approvalA = DocumentApproval::where('circle_id', $circleA->id)->firstOrFail();
        $approvalB = DocumentApproval::where('circle_id', $circleB->id)->firstOrFail();

        $this->documentApprovalsService->decide(
            $approvalA,
            $memberA,
            DocumentApproval::STATUS_APPROVED,
            null,
            $approvalA->document_version_id,
            $approvalA->lock_version
        );

        $approvalA->refresh();
        $approvalB->refresh();
        $this->assertSame(DocumentApproval::STATUS_APPROVED, $approvalA->status);
        $this->assertSame(DocumentApproval::STATUS_PENDING, $approvalB->status);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 配布資料を削除すると確認依頼と履歴もエラーなく削除される()
    {
        $circle = factory(Circle::class)->create();
        $this->documentApprovalsService->requestForCircles($this->document, [$circle->id], $this->staff);
        $approval = DocumentApproval::where('circle_id', $circle->id)->firstOrFail();

        $this->documentsService->deleteDocument($this->document);

        $this->assertDatabaseMissing('document_approvals', ['id' => $approval->id]);
        $this->assertDatabaseMissing('document_approval_decisions', ['document_approval_id' => $approval->id]);
    }
}

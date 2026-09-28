<?php

namespace Tests\Feature\Http\Controllers\Documents\Approvals;

use App\Eloquents\Circle;
use App\Eloquents\Document;
use App\Eloquents\DocumentApproval;
use App\Eloquents\User;
use App\Services\Circles\SelectorService;
use App\Services\Documents\DocumentApprovalsService;
use App\Services\Documents\DocumentsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RequestChangesActionTest extends TestCase
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
    public function コメント付きで修正を依頼できる()
    {
        $response = $this->actingAs($this->member)
            ->post(route('documents.approval.request-changes', ['document' => $this->document]), [
                'comment' => '表紙の誤字を直してください',
                'document_version_id' => $this->approval->document_version_id,
                'lock_version' => $this->approval->lock_version,
            ]);

        $response->assertRedirect(route('documents.approval.show', ['document' => $this->document]));
        $this->approval->refresh();
        $this->assertSame(DocumentApproval::STATUS_CHANGES_REQUESTED, $this->approval->status);
        $this->assertDatabaseHas('document_approval_decisions', [
            'document_approval_id' => $this->approval->id,
            'status' => DocumentApproval::STATUS_CHANGES_REQUESTED,
            'comment' => '表紙の誤字を直してください',
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function コメントなしでは修正を依頼できない()
    {
        $response = $this->actingAs($this->member)
            ->post(route('documents.approval.request-changes', ['document' => $this->document]), [
                'document_version_id' => $this->approval->document_version_id,
                'lock_version' => $this->approval->lock_version,
            ]);

        $response->assertSessionHasErrors(['comment']);
        $this->approval->refresh();
        $this->assertSame(DocumentApproval::STATUS_PENDING, $this->approval->status);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 確認済みの後は修正を依頼できない()
    {
        $this->documentApprovalsService->decide(
            $this->approval,
            $this->member,
            DocumentApproval::STATUS_APPROVED,
            null,
            $this->approval->document_version_id,
            $this->approval->lock_version
        );
        $this->approval->refresh();

        $response = $this->actingAs($this->member)
            ->post(route('documents.approval.request-changes', ['document' => $this->document]), [
                'comment' => 'やっぱり修正してください',
                'document_version_id' => $this->approval->document_version_id,
                'lock_version' => $this->approval->lock_version,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('topAlert.type', 'danger');
        $this->approval->refresh();
        $this->assertSame(DocumentApproval::STATUS_APPROVED, $this->approval->status);
        $this->assertSame(1, $this->approval->decisions()->where('status', DocumentApproval::STATUS_APPROVED)->count());
    }
}

<?php

namespace Tests\Feature\Http\Controllers\Documents\Approvals;

use App\Eloquents\Circle;
use App\Eloquents\Document;
use App\Eloquents\User;
use App\Services\Circles\SelectorService;
use App\Services\Documents\DocumentApprovalsService;
use App\Services\Documents\DocumentsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ShowActionTest extends TestCase
{
    use RefreshDatabase;

    private DocumentsService $documentsService;
    private DocumentApprovalsService $documentApprovalsService;
    private SelectorService $selectorService;
    private User $staff;
    private Document $document;
    private Circle $circleA;
    private User $memberA;

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

        $this->circleA = factory(Circle::class)->create();
        $this->memberA = factory(User::class)->create();
        $this->memberA->circles()->attach($this->circleA->id);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 依頼のある企画は確認画面を見られる()
    {
        $this->documentApprovalsService->requestForCircles($this->document, [$this->circleA->id], $this->staff);
        $this->selectorService->setCircle($this->circleA);

        $response = $this->actingAs($this->memberA)
            ->get(route('documents.approval.show', ['document' => $this->document]));

        $response->assertOk();
        $response->assertSee('確認してください');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 依頼のない企画は404になる()
    {
        $this->selectorService->setCircle($this->circleA);

        $response = $this->actingAs($this->memberA)
            ->get(route('documents.approval.show', ['document' => $this->document]));

        $response->assertNotFound();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 資料を非公開にすると企画からは404になる()
    {
        $this->documentApprovalsService->requestForCircles($this->document, [$this->circleA->id], $this->staff);
        $this->selectorService->setCircle($this->circleA);

        $this->documentsService->updateDocument(
            $this->document,
            $this->document->name,
            $this->document->description,
            null,
            false,
            false,
            null
        );

        $response = $this->actingAs($this->memberA)
            ->get(route('documents.approval.show', ['document' => $this->document]));

        $response->assertNotFound();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 公開範囲を狭めて対象外になった企画からは404になる()
    {
        $this->documentApprovalsService->requestForCircles($this->document, [$this->circleA->id], $this->staff);
        $this->selectorService->setCircle($this->circleA);

        $otherCircle = factory(Circle::class)->create();
        $this->documentsService->updateDocument(
            $this->document,
            $this->document->name,
            $this->document->description,
            null,
            true,
            false,
            null,
            'selected',
            [],
            [$otherCircle->id]
        );

        $response = $this->actingAs($this->memberA)
            ->get(route('documents.approval.show', ['document' => $this->document]));

        $response->assertNotFound();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 別の企画の依頼は見えない()
    {
        $circleB = factory(Circle::class)->create();
        $memberB = factory(User::class)->create();
        $memberB->circles()->attach($circleB->id);

        $this->documentApprovalsService->requestForCircles($this->document, [$this->circleA->id], $this->staff);
        $this->selectorService->setCircle($circleB);

        $response = $this->actingAs($memberB)
            ->get(route('documents.approval.show', ['document' => $this->document]));

        $response->assertNotFound();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 新しい版になっても過去の版の決定が履歴に残る()
    {
        $this->documentApprovalsService->requestForCircles($this->document, [$this->circleA->id], $this->staff);
        $approval = $this->document->approvals()->where('circle_id', $this->circleA->id)->firstOrFail();
        $this->documentApprovalsService->decide(
            $approval,
            $this->memberA,
            \App\Eloquents\DocumentApproval::STATUS_CHANGES_REQUESTED,
            '修正してください',
            $approval->document_version_id,
            $approval->lock_version
        );
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
        $this->selectorService->setCircle($this->circleA);

        $response = $this->actingAs($this->memberA)
            ->get(route('documents.approval.show', ['document' => $this->document]));

        $response->assertOk();
        $response->assertSee('修正してください');
    }
}

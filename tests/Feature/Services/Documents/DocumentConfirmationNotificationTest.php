<?php

namespace Tests\Feature\Services\Documents;

use App\Eloquents\Circle;
use App\Eloquents\DocumentApproval;
use App\Eloquents\User;
use App\Mail\Documents\DocumentConfirmationRequestedMailable;
use App\Mail\Documents\DocumentConfirmationResetMailable;
use App\Services\Documents\DocumentApprovalsService;
use App\Services\Documents\DocumentsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentConfirmationNotificationTest extends TestCase
{
    use RefreshDatabase;

    private DocumentsService $documentsService;
    private DocumentApprovalsService $documentApprovalsService;
    private User $staff;

    public function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->documentsService = App::make(DocumentsService::class);
        $this->documentApprovalsService = App::make(DocumentApprovalsService::class);
        $this->staff = factory(User::class)->states('staff')->create();
    }

    private function memberOf(Circle $circle): User
    {
        $user = factory(User::class)->create();
        $user->circles()->attach($circle->id, ['is_leader' => true]);
        return $user;
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 二つの企画への確認依頼はそれぞれのメンバーに1通ずつ送信され新版の追加で1通ずつ再送信され決定では送信しない()
    {
        Mail::fake();

        $document = $this->documentsService->createDocument(
            '出店の手引き',
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

        $circleA = factory(Circle::class)->create();
        $circleB = factory(Circle::class)->create();
        $memberA = $this->memberOf($circleA);
        $memberB = $this->memberOf($circleB);

        $this->documentApprovalsService->requestForCircles($document, [$circleA->id, $circleB->id], $this->staff);

        Mail::assertSent(DocumentConfirmationRequestedMailable::class, function ($mail) use ($memberA) {
            return $mail->hasTo($memberA->email);
        });
        Mail::assertSent(DocumentConfirmationRequestedMailable::class, function ($mail) use ($memberB) {
            return $mail->hasTo($memberB->email);
        });
        Mail::assertSent(DocumentConfirmationRequestedMailable::class, 2);

        // 新版の追加で確認待ちへリセットされ、各企画のメンバーに1通ずつ再送信される
        $this->documentsService->updateDocument(
            $document,
            $document->name,
            $document->description,
            UploadedFile::fake()->create('第2版.pdf', 1, 'application/pdf'),
            true,
            false,
            null,
            'everyone',
            [],
            [],
            $this->staff
        );

        Mail::assertSent(DocumentConfirmationResetMailable::class, 2);

        // 確認済み・修正依頼の決定では通知メールは送信しない
        $approvalA = DocumentApproval::where('circle_id', $circleA->id)->firstOrFail();
        $this->documentApprovalsService->decide(
            $approvalA,
            $memberA,
            DocumentApproval::STATUS_APPROVED,
            null,
            $approvalA->document_version_id,
            $approvalA->lock_version
        );

        $approvalB = DocumentApproval::where('circle_id', $circleB->id)->firstOrFail();
        $this->documentApprovalsService->decide(
            $approvalB,
            $memberB,
            DocumentApproval::STATUS_CHANGES_REQUESTED,
            '表紙を直してください',
            $approvalB->document_version_id,
            $approvalB->lock_version
        );

        Mail::assertSent(DocumentConfirmationRequestedMailable::class, 2);
        Mail::assertSent(DocumentConfirmationResetMailable::class, 2);
    }
}

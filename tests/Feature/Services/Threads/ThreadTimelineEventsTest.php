<?php

namespace Tests\Feature\Services\Threads;

use App\Eloquents\Circle;
use App\Eloquents\DocumentApproval;
use App\Eloquents\Form;
use App\Eloquents\Permission;
use App\Eloquents\Thread;
use App\Eloquents\ThreadEntry;
use App\Eloquents\User;
use App\Events\Forms\AnswerSubmitted;
use App\Services\Documents\DocumentApprovalsService;
use App\Services\Documents\DocumentsService;
use App\Services\Forms\AnswersService;
use App\Services\Forms\FormAssignmentsService;
use App\Services\Threads\ThreadsService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ThreadTimelineEventsTest extends TestCase
{
    use RefreshDatabase;

    private AnswersService $answersService;
    private DocumentsService $documentsService;
    private DocumentApprovalsService $documentApprovalsService;
    private FormAssignmentsService $formAssignmentsService;
    private ThreadsService $threadsService;
    private User $staff;

    public function setUp(): void
    {
        parent::setUp();
        $this->answersService = App::make(AnswersService::class);
        $this->documentsService = App::make(DocumentsService::class);
        $this->documentApprovalsService = App::make(DocumentApprovalsService::class);
        $this->formAssignmentsService = App::make(FormAssignmentsService::class);
        $this->threadsService = App::make(ThreadsService::class);
        $this->staff = factory(User::class)->states('staff')->create();
        Permission::create(['name' => 'staff.threads.read']);
        $this->staff->syncPermissions(['staff.threads.read']);
    }

    private function memberOf(Circle $circle): User
    {
        $user = factory(User::class)->create();
        $user->circles()->attach($circle->id, ['is_leader' => true]);
        return $user;
    }

    private function eventTypesFor(Circle $circle): array
    {
        $thread = Thread::where('circle_id', $circle->id)->firstOrFail();
        return $thread->entries()
            ->where('kind', ThreadEntry::KIND_EVENT)
            ->orderBy('id')
            ->pluck('event_type')
            ->all();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 確認ありの回答のラウンドトリップで会話に4件のイベントが記録され両側で表示される()
    {
        $form = factory(Form::class)->create(['requires_review' => true, 'name' => '出店計画書']);
        $circle = factory(Circle::class)->create();
        $member = $this->memberOf($circle);
        Auth::login($member);

        $answer = $this->answersService->createAnswer($form, $circle, null, $member);
        $this->answersService->returnAnswer($answer, $this->staff, '記入内容に不備があります');
        $answer->refresh();
        $this->answersService->updateAnswer($form, $answer, null, $member, false, $answer->lock_version);
        $answer->refresh();
        $this->answersService->acceptAnswer($answer, $this->staff, $answer->lock_version);

        $this->assertSame(
            [
                ThreadEntry::EVENT_TYPE_ANSWER_SUBMITTED,
                ThreadEntry::EVENT_TYPE_ANSWER_RETURNED,
                ThreadEntry::EVENT_TYPE_ANSWER_SUBMITTED,
                ThreadEntry::EVENT_TYPE_ANSWER_ACCEPTED,
            ],
            $this->eventTypesFor($circle)
        );

        $circleResponse = $this->actingAs($member)->selectCircle($circle)->get(route('contacts'));
        $circleResponse->assertOk();
        $circleResponse->assertSee('出店計画書を提出しました（第1版）', false);
        $circleResponse->assertSee('出店計画書を差し戻しました : 記入内容に不備があります', false);
        $circleResponse->assertSee('出店計画書を提出しました（第2版）', false);
        $circleResponse->assertSee('出店計画書を完了にしました', false);

        $thread = Thread::where('circle_id', $circle->id)->firstOrFail();
        $staffResponse = $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->get(route('staff.threads.show', ['thread' => $thread]));
        $staffResponse->assertOk();
        $staffResponse->assertSee('出店計画書を提出しました（第1版）', false);
        $staffResponse->assertSee('出店計画書を差し戻しました : 記入内容に不備があります', false);
        $staffResponse->assertSee('出店計画書を提出しました（第2版）', false);
        $staffResponse->assertSee('出店計画書を完了にしました', false);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 確認なしのフォームではイベントが記録されない()
    {
        $form = factory(Form::class)->create(['requires_review' => false]);
        $circle = factory(Circle::class)->create();
        $member = $this->memberOf($circle);
        Auth::login($member);

        $answer = $this->answersService->createAnswer($form, $circle);
        $this->answersService->updateAnswer($form, $answer);

        $this->assertNull(Thread::where('circle_id', $circle->id)->first());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 配布資料の依頼から新版での確認までのイベントが記録され企画ごとに独立している()
    {
        Storage::fake('local');
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

        $this->documentApprovalsService->requestForCircles($document, [$circleA->id, $circleB->id], $this->staff);

        $approvalA = DocumentApproval::where('circle_id', $circleA->id)->firstOrFail();
        $this->documentApprovalsService->decide(
            $approvalA,
            $memberA,
            DocumentApproval::STATUS_CHANGES_REQUESTED,
            '表紙を直してください',
            $approvalA->document_version_id,
            $approvalA->lock_version
        );

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

        $approvalA->refresh();
        $this->documentApprovalsService->decide(
            $approvalA,
            $memberA,
            DocumentApproval::STATUS_APPROVED,
            null,
            $approvalA->document_version_id,
            $approvalA->lock_version
        );

        // 企画Aには依頼・修正依頼・リセット・確認済みの4件
        $this->assertSame(
            [
                ThreadEntry::EVENT_TYPE_DOCUMENT_CONFIRMATION_REQUESTED,
                ThreadEntry::EVENT_TYPE_DOCUMENT_CONFIRMATION_DECIDED,
                ThreadEntry::EVENT_TYPE_DOCUMENT_CONFIRMATION_RESET,
                ThreadEntry::EVENT_TYPE_DOCUMENT_CONFIRMATION_DECIDED,
            ],
            $this->eventTypesFor($circleA)
        );

        // 企画Bには依頼とリセットの2件のみ（企画Aの決定は含まれない）
        $this->assertSame(
            [
                ThreadEntry::EVENT_TYPE_DOCUMENT_CONFIRMATION_REQUESTED,
                ThreadEntry::EVENT_TYPE_DOCUMENT_CONFIRMATION_RESET,
            ],
            $this->eventTypesFor($circleB)
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 閲覧できなくなった配布資料へのイベントはリンクなしで文言のみ表示される()
    {
        Storage::fake('local');
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
        $circle = factory(Circle::class)->create();
        $member = $this->memberOf($circle);
        $this->documentApprovalsService->requestForCircles($document, [$circle->id], $this->staff);

        // 資料を非公開にし、企画からは閲覧できなくする
        $this->documentsService->updateDocument(
            $document,
            $document->name,
            $document->description,
            null,
            false,
            false,
            null,
            'everyone',
            [],
            [],
            $this->staff
        );

        $response = $this->actingAs($member)->selectCircle($circle)->get(route('contacts'));
        $response->assertOk();
        $response->assertSee('出店の手引き 第1版の確認を依頼しました', false);
        $response->assertDontSee(route('documents.approval.show', ['document' => $document]), false);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function フォームの送付で企画ごとに1件記録され同じ期限での再送信は増えず期限変更で1件増える()
    {
        $form = factory(Form::class)->create(['audience' => 'selected', 'name' => '出店計画書']);
        $circleA = factory(Circle::class)->create();
        $circleB = factory(Circle::class)->create();
        $dueAt = new Carbon('2026-10-08 23:59:59');

        $this->formAssignmentsService->assignToCircles($form, [$circleA->id, $circleB->id], $dueAt, $this->staff);

        $this->assertSame([ThreadEntry::EVENT_TYPE_FORM_SENT], $this->eventTypesFor($circleA));
        $this->assertSame([ThreadEntry::EVENT_TYPE_FORM_SENT], $this->eventTypesFor($circleB));

        // 同じ期限での再送信では増えない
        $this->formAssignmentsService->assignToCircles($form, [$circleA->id, $circleB->id], $dueAt, $this->staff);
        $this->assertSame([ThreadEntry::EVENT_TYPE_FORM_SENT], $this->eventTypesFor($circleA));
        $this->assertSame([ThreadEntry::EVENT_TYPE_FORM_SENT], $this->eventTypesFor($circleB));

        // 期限を変更した企画だけ1件増える
        $this->formAssignmentsService->updateDueDate($form, $circleA, new Carbon('2026-10-15 23:59:59'));
        $this->assertSame(
            [ThreadEntry::EVENT_TYPE_FORM_SENT, ThreadEntry::EVENT_TYPE_FORM_DUE_DATE_CHANGED],
            $this->eventTypesFor($circleA)
        );
        $this->assertSame([ThreadEntry::EVENT_TYPE_FORM_SENT], $this->eventTypesFor($circleB));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 同じイベントを2回発行してもリスナーの重複実行で会話には1件しか作られない()
    {
        $form = factory(Form::class)->create(['requires_review' => true, 'name' => '出店計画書']);
        $circle = factory(Circle::class)->create();

        $event = new AnswerSubmitted($circle->id, $form->id, $form->name, 1, 1, false);
        event($event);
        event($event);

        $this->assertDatabaseCount('thread_entries', 1);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function トランザクションがロールバックした場合は会話に記録されない()
    {
        $form = factory(Form::class)->create(['requires_review' => true, 'name' => '出店計画書']);
        $circle = factory(Circle::class)->create();

        try {
            DB::transaction(function () use ($circle, $form) {
                event(new AnswerSubmitted($circle->id, $form->id, $form->name, 1, 1, false));
                throw new \RuntimeException('ロールバックさせるための例外');
            });
        } catch (\RuntimeException $e) {
            // ロールバックの確認が目的なので例外は握りつぶす
        }

        $this->assertDatabaseCount('thread_entries', 0);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function イベントの記録は会話の状態を変更せずメールも送信しない()
    {
        $form = factory(Form::class)->create(['requires_review' => true, 'name' => '出店計画書']);
        $circle = factory(Circle::class)->create();
        $member = $this->memberOf($circle);
        Auth::login($member);

        Mail::fake();
        $answer = $this->answersService->createAnswer($form, $circle, null, $member);
        $this->answersService->acceptAnswer($answer, $this->staff, $answer->lock_version);

        Mail::assertNothingSent();

        $thread = Thread::where('circle_id', $circle->id)->firstOrFail();
        $this->assertSame(Thread::STATUS_RESOLVED, $thread->status);
    }
}

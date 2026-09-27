<?php

namespace Tests\Feature\Services\Circles;

use App\Eloquents\Answer;
use App\Eloquents\Circle;
use App\Eloquents\Document;
use App\Eloquents\DocumentApproval;
use App\Eloquents\DocumentVersion;
use App\Eloquents\Form;
use App\Eloquents\FormAssignment;
use App\Eloquents\ParticipationType;
use App\Eloquents\Tag;
use App\Services\Circles\CircleProgressService;
use App\Services\Circles\ValueObjects\CircleProgress;
use App\Services\Circles\ValueObjects\ProgressUnit;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

class CircleProgressServiceTest extends TestCase
{
    use RefreshDatabase;

    private CircleProgressService $circleProgressService;

    public function setUp(): void
    {
        parent::setUp();
        $this->circleProgressService = App::make(CircleProgressService::class);
    }

    private function createApproval(Circle $circle, string $status): DocumentApproval
    {
        $document = factory(Document::class)->create();
        $version = factory(DocumentVersion::class)->create(['document_id' => $document->id]);

        return DocumentApproval::create([
            'document_id' => $document->id,
            'circle_id' => $circle->id,
            'document_version_id' => $version->id,
            'status' => $status,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function forCircle_進捗_誰の番_次の期限_遅れを単位から計算する()
    {
        Carbon::setTestNow(new Carbon('2026-10-15 00:00:00'));

        $circle = factory(Circle::class)->create();

        // (a) 未回答の要確認フォーム。期限はまだ先
        $formA = factory(Form::class)->create([
            'requires_review' => true,
            'close_at' => new Carbon('2026-11-01 00:00:00'),
        ]);

        // (b) 差し戻された回答。期限はすでに過ぎている
        $formB = factory(Form::class)->create([
            'requires_review' => true,
            'close_at' => new Carbon('2026-10-01 00:00:00'),
        ]);
        factory(Answer::class)->create([
            'form_id' => $formB->id,
            'circle_id' => $circle->id,
            'review_status' => Answer::REVIEW_STATUS_RETURNED,
        ]);

        // (c) 確認済みの回答
        $formC = factory(Form::class)->create(['requires_review' => true]);
        factory(Answer::class)->create([
            'form_id' => $formC->id,
            'circle_id' => $circle->id,
            'review_status' => Answer::REVIEW_STATUS_ACCEPTED,
        ]);

        // (d) 確認待ちの配布資料確認依頼
        $approvalD = $this->createApproval($circle, DocumentApproval::STATUS_PENDING);

        // (e) 修正依頼された配布資料確認依頼
        $approvalE = $this->createApproval($circle, DocumentApproval::STATUS_CHANGES_REQUESTED);

        $progress = $this->circleProgressService->forCircle($circle);

        $this->assertSame(5, $progress->getTotalCount());
        $this->assertSame(1, $progress->getDoneCount());
        $this->assertSame('1/5', $progress->getProgressLabel());
        $this->assertSame(CircleProgress::TURN_STAFF, $progress->getTurn(), '(e) がreviewのためスタッフの番');
        $this->assertTrue($progress->getNextDueAt()->equalTo($formB->close_at), '未完了フォームのうち最も早い期限');
        $this->assertSame(1, $progress->getOverdueCount(), '期限を過ぎているのは(b)のみ');

        // (e) を承認して完了にする
        $approvalE->update(['status' => DocumentApproval::STATUS_APPROVED]);

        $progress = $this->circleProgressService->forCircle($circle);
        $this->assertSame(CircleProgress::TURN_CIRCLE, $progress->getTurn(), 'review単位が無くなり企画の番になる');

        Carbon::setTestNow();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function forCircle_確認不要フォームは回答すればdoneになる()
    {
        $circle = factory(Circle::class)->create();
        $form = factory(Form::class)->create(['requires_review' => false]);

        $progress = $this->circleProgressService->forCircle($circle);
        $this->assertSame(ProgressUnit::STATE_TODO, $progress->getUnits()->first()->getState());

        factory(Answer::class)->create(['form_id' => $form->id, 'circle_id' => $circle->id]);

        $progress = $this->circleProgressService->forCircle($circle);
        $this->assertSame(ProgressUnit::STATE_DONE, $progress->getUnits()->first()->getState());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function forCircle_非公開フォーム_参加登録フォーム_対象外のフォームは単位に含まれない()
    {
        $circle = factory(Circle::class)->create();

        // 非公開フォーム
        factory(Form::class)->create(['is_public' => false]);

        // 参加登録フォーム
        $participationForm = factory(Form::class)->create();
        ParticipationType::factory()->create(['form_id' => $participationForm->id]);

        // 対象外（selectedだがタグも送付も無い）
        factory(Form::class)->create(['audience' => 'selected']);

        // 比較用に、含まれるべきフォームを1つ用意する
        $includedForm = factory(Form::class)->create();

        $progress = $this->circleProgressService->forCircle($circle);

        $this->assertSame(1, $progress->getTotalCount());
        $this->assertSame($includedForm->name, $progress->getUnits()->first()->getLabel());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function forCircle_selectedフォームはタグ一致または送付先指定で対象になる()
    {
        $tag = factory(Tag::class)->create();

        $taggedCircle = factory(Circle::class)->create();
        $taggedCircle->tags()->attach($tag->id);
        $taggedForm = factory(Form::class)->create(['audience' => 'selected']);
        $taggedForm->answerableTags()->attach($tag->id);

        $assignedCircle = factory(Circle::class)->create();
        $assignedForm = factory(Form::class)->create(['audience' => 'selected']);
        FormAssignment::create(['form_id' => $assignedForm->id, 'circle_id' => $assignedCircle->id]);

        $taggedProgress = $this->circleProgressService->forCircle($taggedCircle);
        $this->assertSame(1, $taggedProgress->getTotalCount());

        $assignedProgress = $this->circleProgressService->forCircle($assignedCircle);
        $this->assertSame(1, $assignedProgress->getTotalCount());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function forCircle_企画がもう閲覧できない配布資料への確認依頼は単位に含まれない()
    {
        $circle = factory(Circle::class)->create();

        // 非公開になった配布資料
        $privateApproval = $this->createApproval($circle, DocumentApproval::STATUS_PENDING);
        $privateApproval->document->update(['is_public' => false]);

        // selectedだが対象外になった配布資料
        $unrelatedApproval = $this->createApproval($circle, DocumentApproval::STATUS_PENDING);
        $unrelatedApproval->document->update(['audience' => 'selected']);

        $progress = $this->circleProgressService->forCircle($circle);

        $this->assertSame(0, $progress->getTotalCount());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function forCircles_複数企画をまとめて計算しても互いに影響しない()
    {
        $circleA = factory(Circle::class)->create();
        $circleB = factory(Circle::class)->create();
        $form = factory(Form::class)->create(['requires_review' => false]);
        factory(Answer::class)->create(['form_id' => $form->id, 'circle_id' => $circleA->id]);

        $progresses = $this->circleProgressService->forCircles(collect([$circleA, $circleB]));

        $this->assertSame(ProgressUnit::STATE_DONE, $progresses->get($circleA->id)->getUnits()->first()->getState());
        $this->assertSame(ProgressUnit::STATE_TODO, $progresses->get($circleB->id)->getUnits()->first()->getState());
    }
}

<?php

namespace Tests\Feature\Http\Controllers\Staff\Progress;

use App\Eloquents\Answer;
use App\Eloquents\Circle;
use App\Eloquents\Document;
use App\Eloquents\DocumentApproval;
use App\Eloquents\DocumentVersion;
use App\Eloquents\Form;
use App\Eloquents\Permission;
use App\Eloquents\Thread;
use App\Eloquents\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class IndexActionTest extends TestCase
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

    private function actingAsStaff()
    {
        return $this->actingAs($this->staff)->withSession(['staff_authorized' => true]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 権限がなければアクセスできない()
    {
        $response = $this->actingAsStaff()->get(route('staff.progress.index'));

        $response->assertStatus(403);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 権限があれば承認済み企画ごとの進捗が表示される()
    {
        Permission::create(['name' => 'staff.circles.read']);
        $this->staff->syncPermissions(['staff.circles.read']);

        $circle = factory(Circle::class)->create(['name' => 'テストサークル']);
        $form = factory(Form::class)->create(['requires_review' => false]);
        factory(Answer::class)->create(['form_id' => $form->id, 'circle_id' => $circle->id]);

        // 未提出のため一覧には出ない企画
        factory(Circle::class)->states('notSubmitted')->create();

        $response = $this->actingAsStaff()->get(route('staff.progress.index'));

        $response->assertOk();
        $response->assertSee('テストサークル');
        $response->assertSee('1/1', false);
        $response->assertSee(route('staff.circles.edit', ['circle' => $circle->id]), false);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function アクション一覧にはレビュー待ちの単位とneeds_staffの会話だけが含まれる()
    {
        Permission::create(['name' => 'staff.circles.read']);
        $this->staff->syncPermissions(['staff.circles.read']);

        // レビュー待ち（含まれる）
        $reviewCircle = factory(Circle::class)->create(['name' => 'レビュー待ち企画']);
        $reviewForm = factory(Form::class)->create(['requires_review' => true, 'name' => 'レビュー待ちフォーム']);
        factory(Answer::class)->create([
            'form_id' => $reviewForm->id,
            'circle_id' => $reviewCircle->id,
            'review_status' => Answer::REVIEW_STATUS_SUBMITTED,
        ]);

        // 未回答（含まれない）
        $todoCircle = factory(Circle::class)->create(['name' => '未回答企画']);
        factory(Form::class)->create(['requires_review' => true, 'name' => '未回答フォーム']);

        // needs_staffの会話（含まれる）
        $needsStaffCircle = factory(Circle::class)->create(['name' => '対応が必要な企画']);
        Thread::create(['circle_id' => $needsStaffCircle->id, 'status' => Thread::STATUS_NEEDS_STAFF]);

        // 解決済みの会話（含まれない）
        $resolvedCircle = factory(Circle::class)->create(['name' => '解決済みの企画']);
        Thread::create(['circle_id' => $resolvedCircle->id, 'status' => Thread::STATUS_RESOLVED]);

        $response = $this->actingAsStaff()->get(route('staff.progress.index'));

        $response->assertOk();

        // レンダリング結果に依らず、渡されたデータそのものを検証する
        $reviewEntries = $response->viewData('review_entries');
        $needsStaffThreads = $response->viewData('needs_staff_threads');

        $this->assertCount(1, $reviewEntries);
        $this->assertSame($reviewCircle->id, $reviewEntries->first()['circle']->id);
        $this->assertSame('レビュー待ちフォーム', $reviewEntries->first()['unit']->getLabel());

        $this->assertCount(1, $needsStaffThreads);
        $this->assertSame($needsStaffCircle->id, $needsStaffThreads->first()->circle_id);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 企画数が増えてもクエリ数は一定範囲に収まる()
    {
        Permission::create(['name' => 'staff.circles.read']);
        $this->staff->syncPermissions(['staff.circles.read']);

        $reviewForm = factory(Form::class)->create(['requires_review' => true]);
        $noReviewForm = factory(Form::class)->create(['requires_review' => false]);
        $document = factory(Document::class)->create();
        $version = factory(DocumentVersion::class)->create(['document_id' => $document->id]);

        for ($i = 0; $i < 30; $i++) {
            $circle = factory(Circle::class)->create();

            factory(Answer::class)->create([
                'form_id' => $reviewForm->id,
                'circle_id' => $circle->id,
                'review_status' => Answer::REVIEW_STATUS_SUBMITTED,
            ]);
            factory(Answer::class)->create(['form_id' => $noReviewForm->id, 'circle_id' => $circle->id]);
            DocumentApproval::create([
                'document_id' => $document->id,
                'circle_id' => $circle->id,
                'document_version_id' => $version->id,
                'status' => DocumentApproval::STATUS_PENDING,
            ]);
            Thread::create(['circle_id' => $circle->id, 'status' => Thread::STATUS_NEEDS_STAFF]);
        }

        DB::enableQueryLog();
        $response = $this->actingAsStaff()->get(route('staff.progress.index'));
        $queryCount = count(DB::getQueryLog());
        DB::disableQueryLog();

        $response->assertOk();
        $this->assertLessThan(20, $queryCount, "30企画に対してクエリが{$queryCount}回発行されました");
    }
}

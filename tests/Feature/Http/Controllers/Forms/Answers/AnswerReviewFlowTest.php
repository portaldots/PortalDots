<?php

namespace Tests\Feature\Http\Controllers\Forms\Answers;

use App\Eloquents\Answer;
use App\Eloquents\AnswerDetail;
use App\Eloquents\Circle;
use App\Eloquents\Form;
use App\Eloquents\Permission;
use App\Eloquents\Question;
use App\Eloquents\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AnswerReviewFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Circle $circle;
    private Form $form;
    private Question $textQuestion;
    private Question $fileQuestion;
    private User $staff;

    public function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Storage::fake('local');

        $this->user = factory(User::class)->create();
        $this->circle = factory(Circle::class)->create();
        $this->circle->users()->attach($this->user->id, ['is_leader' => true]);

        $this->form = factory(Form::class)->create([
            'requires_review' => true,
            'max_answers' => 1,
        ]);
        $this->textQuestion = factory(Question::class)->create([
            'form_id' => $this->form->id,
            'type' => 'text',
            'is_required' => false,
            'number_min' => null,
            'number_max' => null,
        ]);
        $this->fileQuestion = factory(Question::class)->create([
            'form_id' => $this->form->id,
            'type' => 'upload',
            'is_required' => false,
            'number_min' => null,
            'number_max' => null,
            'allowed_types' => 'pdf',
        ]);

        $this->staff = factory(User::class)->create(['is_staff' => true]);
        Permission::firstOrCreate(['name' => 'staff.forms.answers.read']);
        Permission::firstOrCreate(['name' => 'staff.forms.answers.edit']);
        $this->staff->syncPermissions(['staff.forms.answers.read', 'staff.forms.answers.edit']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 提出_差し戻し_再提出_完了までの一連の流れと過去のリビジョンの閲覧()
    {
        // 1. 企画が提出する(リビジョン1)
        $storeResponse = $this->actingAs($this->user)->post(route('forms.answers.store', $this->form), [
            'circle_id' => $this->circle->id,
            'answers' => [
                $this->textQuestion->id => '第1版の回答',
                $this->fileQuestion->id => UploadedFile::fake()->create('v1.pdf', 10, 'application/pdf'),
            ],
        ]);
        $storeResponse->assertRedirect();

        /** @var Answer $answer */
        $answer = Answer::sole();
        $this->assertSame(Answer::REVIEW_STATUS_SUBMITTED, $answer->review_status);
        $this->assertSame(1, $answer->revisions()->count());

        $originalFilePath = AnswerDetail::where('answer_id', $answer->id)
            ->where('question_id', $this->fileQuestion->id)
            ->value('answer');
        Storage::disk('local')->assertExists($originalFilePath);

        // 2. スタッフが差し戻す
        $returnResponse = $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->patch(route('staff.forms.answers.return', ['form' => $this->form, 'answer' => $answer]), [
                'review_note' => '記入内容に不備があります',
                'lock_version' => $answer->lock_version,
            ]);
        $returnResponse->assertRedirect();
        $answer->refresh();
        $this->assertSame(Answer::REVIEW_STATUS_RETURNED, $answer->review_status);
        $this->assertSame('記入内容に不備があります', $answer->review_note);

        // 差し戻し理由が企画側の画面に表示される
        $this->actingAs($this->user)
            ->get(route('forms.answers.edit', ['form' => $this->form, 'answer' => $answer]))
            ->assertOk()
            ->assertSee('記入内容に不備があります');

        // 3. 企画が値とファイルを変更して再提出する(リビジョン2)
        $updateResponse = $this->actingAs($this->user)->patch(
            route('forms.answers.update', ['form' => $this->form, 'answer' => $answer]),
            [
                'circle_id' => $this->circle->id,
                'lock_version' => $answer->lock_version,
                'answers' => [
                    $this->textQuestion->id => '第2版の回答',
                    $this->fileQuestion->id => UploadedFile::fake()->create('v2.pdf', 10, 'application/pdf'),
                ],
            ]
        );
        $updateResponse->assertRedirect();
        $answer->refresh();
        $this->assertSame(Answer::REVIEW_STATUS_SUBMITTED, $answer->review_status);
        $this->assertNull($answer->review_note);
        $this->assertSame(2, $answer->revisions()->count());

        $newFilePath = AnswerDetail::where('answer_id', $answer->id)
            ->where('question_id', $this->fileQuestion->id)
            ->value('answer');
        $this->assertNotSame($originalFilePath, $newFilePath);

        // 差し替え後も、リビジョン1が参照する旧ファイルは削除されていない
        Storage::disk('local')->assertExists($originalFilePath);
        Storage::disk('local')->assertExists($newFilePath);

        // 4. リビジョン1は、企画・スタッフの双方から元の内容のまま閲覧できる
        //
        // question-item は Vue コンポーネントで、value は v-bind (JSON) として
        // 埋め込まれるため、assertSee には json_encode 後(unicodeはエスケープ
        // される)の文字列を使う
        $revision1 = $answer->revisions()->where('revision', 1)->sole();
        $originalValueJson = trim(json_encode('第1版の回答'), '"');
        $updatedValueJson = trim(json_encode('第2版の回答'), '"');

        $circleRevisionResponse = $this->actingAs($this->user)->get(route('forms.answers.revisions.show', [
            'form' => $this->form, 'answer' => $answer, 'revision' => $revision1,
        ]));
        $circleRevisionResponse->assertOk();
        $circleRevisionResponse->assertSee($originalValueJson, false);
        $circleRevisionResponse->assertDontSee($updatedValueJson, false);

        $staffRevisionResponse = $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->get(route('staff.forms.answers.revisions.show', [
                'form' => $this->form, 'answer' => $answer, 'revision' => $revision1,
            ]));
        $staffRevisionResponse->assertOk();
        $staffRevisionResponse->assertSee($originalValueJson, false);

        // リビジョン1のファイルは、差し替え後も元のファイルのまま取得できる
        $circleFileResponse = $this->actingAs($this->user)->get(route('forms.answers.revisions.uploads.show', [
            'form' => $this->form, 'answer' => $answer, 'revision' => $revision1, 'question' => $this->fileQuestion,
        ]));
        $circleFileResponse->assertOk();
        $this->assertSame(
            realpath(Storage::path($originalFilePath)),
            $circleFileResponse->baseResponse->getFile()->getRealPath()
        );

        // 5. 他企画・スタッフ以外からはリビジョンを閲覧できない
        $otherUser = factory(User::class)->create();
        $otherCircle = factory(Circle::class)->create();
        $otherUser->circles()->attach($otherCircle->id, ['is_leader' => true]);

        $this->actingAs($otherUser)->get(route('forms.answers.revisions.show', [
            'form' => $this->form, 'answer' => $answer, 'revision' => $revision1,
        ]))->assertForbidden();

        $this->actingAs($otherUser)->get(route('forms.answers.revisions.uploads.show', [
            'form' => $this->form, 'answer' => $answer, 'revision' => $revision1, 'question' => $this->fileQuestion,
        ]))->assertNotFound();

        // 6. スタッフが完了にする
        $acceptResponse = $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->patch(route('staff.forms.answers.accept', ['form' => $this->form, 'answer' => $answer]), [
                'lock_version' => $answer->lock_version,
            ]);
        $acceptResponse->assertRedirect();
        $answer->refresh();
        $this->assertSame(Answer::REVIEW_STATUS_ACCEPTED, $answer->review_status);
        $this->assertSame($this->staff->id, $answer->reviewed_by);

        // 7. 完了後は企画側から編集できない(閲覧は可能)
        $this->actingAs($this->user)
            ->get(route('forms.answers.edit', ['form' => $this->form, 'answer' => $answer]))
            ->assertOk();

        $this->actingAs($this->user)->patch(
            route('forms.answers.update', ['form' => $this->form, 'answer' => $answer]),
            [
                'circle_id' => $this->circle->id,
                'lock_version' => $answer->lock_version,
                'answers' => [
                    $this->textQuestion->id => '完了後の変更',
                ],
            ]
        )->assertForbidden();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function lock_versionが古い場合はDBを変更せずに拒否される()
    {
        $storeResponse = $this->actingAs($this->user)->post(route('forms.answers.store', $this->form), [
            'circle_id' => $this->circle->id,
            'answers' => [$this->textQuestion->id => '最初の回答'],
        ]);
        $storeResponse->assertRedirect();

        /** @var Answer $answer */
        $answer = Answer::sole();
        $staleLockVersion = $answer->lock_version;

        // 別の操作で先に更新が進んだ状態を再現する
        $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->patch(route('staff.forms.answers.accept', ['form' => $this->form, 'answer' => $answer]), [
                'lock_version' => $answer->lock_version,
            ])->assertRedirect();

        $answer->refresh();
        $this->assertSame(Answer::REVIEW_STATUS_ACCEPTED, $answer->review_status);
        $updatedAt = $answer->reviewed_at;

        // 古い lock_version による差し戻しは、DBを変更せず拒否される
        $response = $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->patch(route('staff.forms.answers.return', ['form' => $this->form, 'answer' => $answer]), [
                'review_note' => '古い状態からの差し戻し',
                'lock_version' => $staleLockVersion,
            ]);
        $response->assertRedirect();

        $answer->refresh();
        $this->assertSame(Answer::REVIEW_STATUS_ACCEPTED, $answer->review_status);
        $this->assertTrue($answer->reviewed_at->eq($updatedAt));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function requiresReviewでないフォームは今まで通り動作しリビジョンも確認状況も作られない()
    {
        $normalForm = factory(Form::class)->create(['requires_review' => false]);
        $normalQuestion = factory(Question::class)->create([
            'form_id' => $normalForm->id,
            'type' => 'text',
            'is_required' => false,
            'number_min' => null,
            'number_max' => null,
        ]);

        $response = $this->actingAs($this->user)->post(route('forms.answers.store', $normalForm), [
            'circle_id' => $this->circle->id,
            'answers' => [$normalQuestion->id => '通常フォームへの回答'],
        ]);
        $response->assertRedirect();

        /** @var Answer $answer */
        $answer = Answer::sole();
        $this->assertNull($answer->review_status);
        $this->assertNull($answer->submitted_at);
        $this->assertSame(0, $answer->revisions()->count());

        $editResponse = $this->actingAs($this->user)
            ->get(route('forms.answers.edit', ['form' => $normalForm, 'answer' => $answer]));
        $editResponse->assertOk();
        $editResponse->assertDontSee('過去の提出');
        $editResponse->assertDontSee('確認中');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function リビジョンの閲覧はスタッフ権限のないユーザーからは拒否される()
    {
        $storeResponse = $this->actingAs($this->user)->post(route('forms.answers.store', $this->form), [
            'circle_id' => $this->circle->id,
            'answers' => [$this->textQuestion->id => '第1版の回答'],
        ]);
        $storeResponse->assertRedirect();

        /** @var Answer $answer */
        $answer = Answer::sole();
        $revision1 = $answer->revisions()->where('revision', 1)->sole();

        // スタッフではない一般ユーザー
        $nonStaff = factory(User::class)->create();
        $this->actingAs($nonStaff)
            ->get(route('staff.forms.answers.revisions.show', [
                'form' => $this->form, 'answer' => $answer, 'revision' => $revision1,
            ]))->assertForbidden();

        // スタッフだが staff.forms.answers.read 権限を持たないユーザー
        $staffWithoutPermission = factory(User::class)->create(['is_staff' => true]);
        $this->actingAs($staffWithoutPermission)
            ->withSession(['staff_authorized' => true])
            ->get(route('staff.forms.answers.revisions.show', [
                'form' => $this->form, 'answer' => $answer, 'revision' => $revision1,
            ]))->assertForbidden();
    }
}

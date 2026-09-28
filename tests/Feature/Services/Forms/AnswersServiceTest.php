<?php

namespace Tests\Feature\Services\Forms;

use App\Eloquents\Answer;
use App\Eloquents\Circle;
use App\Eloquents\Form;
use App\Eloquents\Question;
use App\Eloquents\User;
use App\Exceptions\Forms\DuplicateAnswerException;
use App\Exceptions\Forms\StaleAnswerException;
use App\Mail\Forms\AnswerConfirmationMailable;
use App\Services\Forms\AnswersService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class AnswersServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var AnswersSerivce
     */
    private $answersSerivce;

    public function setUp(): void
    {
        parent::setUp();
        $this->answersSerivce = App::make(AnswersService::class);
        // updateAnswerDetails 内のアクティビティログ記録に、ログイン中のユーザーが必要なため
        Auth::login(factory(User::class)->create());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function sendAll()
    {
        /** @var Collection */
        $staff = factory(User::class, 3)->state('staff')->create();
        $form_creator = $staff[1];

        // スタッフとしてフォームを作成する（アクティビティログにフォーム作成者を残す）
        Auth::login($form_creator);

        /** @var Form */
        $form = factory(Form::class)->create();

        // 異なるユーザーがフォームを編集する
        Auth::login($staff[0]);
        $form->name = 'フォーム情報編集 1回目';
        $form->save();
        Auth::login($staff[2]);
        $form->name = 'フォーム情報編集 2回目';
        $form->save();
        Auth::logout();

        /** @var Illuminate\Database\Eloquent\Collection */
        $circle_members = factory(User::class, 3)->create();

        Auth::login($circle_members[1]);

        /** @var Circle */
        $circle = factory(Circle::class)->create();

        /** @var Illuminate\Database\Eloquent\Collection */
        $questions = factory(Question::class, 5)->create([
            'form_id' => $form->id,
            'is_required' => false
        ]);

        $circle->users()->saveMany($circle_members);

        $form->questions()->saveMany($questions);

        /** @var Answer */
        $answer = factory(Answer::class)->create([
            'form_id' => $form->id,
            'circle_id' => $circle->id
        ]);

        Mail::fake();
        $this->answersSerivce->sendAll($answer, $circle_members[1], false);

        foreach ($circle_members as $recipient) {
            Mail::assertSent(AnswerConfirmationMailable::class, function ($mail) use ($recipient) {
                return $mail->hasTo($recipient->email);
            });
        }

        // フォーム作成者にもメールが届く
        Mail::assertSent(AnswerConfirmationMailable::class, function ($mail) use ($form_creator) {
            return $mail->hasTo($form_creator->email);
        });

        // フォームを編集したユーザーにはメールが届かない
        Mail::assertNotSent(AnswerConfirmationMailable::class, function ($mail) use ($staff) {
            return $mail->hasTo($staff[0]->email);
        });
        Mail::assertNotSent(AnswerConfirmationMailable::class, function ($mail) use ($staff) {
            return $mail->hasTo($staff[2]->email);
        });
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function createAnswer_requiresReviewなフォームでは提出状態でリビジョン1が作成される()
    {
        $form = factory(Form::class)->create(['requires_review' => true]);
        $circle = factory(Circle::class)->create();
        $staff = factory(User::class)->state('staff')->create();

        $answer = $this->answersSerivce->createAnswer($form, $circle, null, $staff);

        $answer->refresh();
        $this->assertSame(Answer::REVIEW_STATUS_SUBMITTED, $answer->review_status);
        $this->assertNotNull($answer->submitted_at);
        $this->assertSame(1, $answer->lock_version);

        $this->assertSame(1, $answer->revisions()->count());
        $revision = $answer->revisions()->first();
        $this->assertSame(1, $revision->revision);
        $this->assertSame($staff->id, $revision->submitted_by);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function createAnswer_requiresReviewでないフォームではリビジョンもreview_statusも作成されない()
    {
        $form = factory(Form::class)->create(['requires_review' => false]);
        $circle = factory(Circle::class)->create();

        $answer = $this->answersSerivce->createAnswer($form, $circle);

        $answer->refresh();
        $this->assertNull($answer->review_status);
        $this->assertNull($answer->submitted_at);
        $this->assertSame(0, $answer->lock_version);
        $this->assertSame(0, $answer->revisions()->count());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function createAnswer_requiresReviewかつmax_answersが1の場合2件目の作成は拒否される()
    {
        $form = factory(Form::class)->create(['requires_review' => true, 'max_answers' => 1]);
        $circle = factory(Circle::class)->create();

        $this->answersSerivce->createAnswer($form, $circle);

        $this->expectException(DuplicateAnswerException::class);
        $this->answersSerivce->createAnswer($form, $circle);

        $this->assertSame(1, Answer::where('form_id', $form->id)->where('circle_id', $circle->id)->count());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function updateAnswer_再提出のたびにリビジョンが増え提出状態に戻り差し戻し理由がクリアされる()
    {
        $form = factory(Form::class)->create(['requires_review' => true]);
        $circle = factory(Circle::class)->create();
        $answer = $this->answersSerivce->createAnswer($form, $circle);

        $staff = factory(User::class)->state('staff')->create();
        $this->answersSerivce->returnAnswer($answer, $staff, '記入内容に不備があります');
        $answer->refresh();
        $this->assertSame(Answer::REVIEW_STATUS_RETURNED, $answer->review_status);
        $this->assertSame('記入内容に不備があります', $answer->review_note);

        $updated = $this->answersSerivce->updateAnswer($form, $answer, null, null, false, $answer->lock_version);

        $updated->refresh();
        $this->assertSame(Answer::REVIEW_STATUS_SUBMITTED, $updated->review_status);
        $this->assertNull($updated->review_note);
        $this->assertSame(2, $updated->revisions()->count());
        $this->assertSame(3, $updated->lock_version);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function updateAnswer_lock_versionが一致しない場合は何も変更せずStaleAnswerExceptionをthrowする()
    {
        $form = factory(Form::class)->create(['requires_review' => true]);
        $circle = factory(Circle::class)->create();
        $answer = $this->answersSerivce->createAnswer($form, $circle);
        $originalLockVersion = $answer->lock_version;

        $this->expectException(StaleAnswerException::class);
        try {
            $this->answersSerivce->updateAnswer($form, $answer, null, null, false, $originalLockVersion - 1);
        } finally {
            $answer->refresh();
            $this->assertSame($originalLockVersion, $answer->lock_version);
            $this->assertSame(1, $answer->revisions()->count());
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function updateAnswer_同じlock_versionでの二重送信はリビジョンを1つしか作らない()
    {
        $form = factory(Form::class)->create(['requires_review' => true]);
        $circle = factory(Circle::class)->create();
        $answer = $this->answersSerivce->createAnswer($form, $circle);
        $lockVersion = $answer->lock_version;

        $this->answersSerivce->updateAnswer($form, $answer, null, null, false, $lockVersion);

        $this->expectException(StaleAnswerException::class);
        try {
            $this->answersSerivce->updateAnswer($form, $answer, null, null, false, $lockVersion);
        } finally {
            $answer->refresh();
            $this->assertSame(2, $answer->revisions()->count());
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function updateAnswer_スタッフによる修正はリビジョンを記録するがreview_statusを変更しない()
    {
        $form = factory(Form::class)->create(['requires_review' => true]);
        $circle = factory(Circle::class)->create();
        $answer = $this->answersSerivce->createAnswer($form, $circle);
        $staff = factory(User::class)->state('staff')->create();
        $this->answersSerivce->acceptAnswer($answer, $staff);
        $answer->refresh();

        $updated = $this->answersSerivce->updateAnswer($form, $answer, null, $staff, true);

        $updated->refresh();
        $this->assertSame(Answer::REVIEW_STATUS_ACCEPTED, $updated->review_status);
        $this->assertSame(2, $updated->revisions()->count());
        $latestRevision = $updated->revisions()->orderByDesc('revision')->first();
        $this->assertSame($staff->id, $latestRevision->submitted_by);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function acceptAnswer_完了にできレビュー情報が記録される()
    {
        $form = factory(Form::class)->create(['requires_review' => true]);
        $circle = factory(Circle::class)->create();
        $answer = $this->answersSerivce->createAnswer($form, $circle);
        $staff = factory(User::class)->state('staff')->create();

        $accepted = $this->answersSerivce->acceptAnswer($answer, $staff);

        $this->assertSame(Answer::REVIEW_STATUS_ACCEPTED, $accepted->review_status);
        $this->assertSame($staff->id, $accepted->reviewed_by);
        $this->assertNotNull($accepted->reviewed_at);
        $this->assertSame(2, $accepted->lock_version);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function returnAnswer_理由付きで差し戻せる()
    {
        $form = factory(Form::class)->create(['requires_review' => true]);
        $circle = factory(Circle::class)->create();
        $answer = $this->answersSerivce->createAnswer($form, $circle);
        $staff = factory(User::class)->state('staff')->create();

        $returned = $this->answersSerivce->returnAnswer($answer, $staff, '記入内容に不備があります');

        $this->assertSame(Answer::REVIEW_STATUS_RETURNED, $returned->review_status);
        $this->assertSame('記入内容に不備があります', $returned->review_note);
        $this->assertSame($staff->id, $returned->reviewed_by);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function acceptAnswer_lock_versionが一致しない場合はStaleAnswerExceptionをthrowする()
    {
        $form = factory(Form::class)->create(['requires_review' => true]);
        $circle = factory(Circle::class)->create();
        $answer = $this->answersSerivce->createAnswer($form, $circle);
        $staff = factory(User::class)->state('staff')->create();

        $this->expectException(StaleAnswerException::class);
        try {
            $this->answersSerivce->acceptAnswer($answer, $staff, $answer->lock_version - 1);
        } finally {
            $answer->refresh();
            $this->assertNull($answer->reviewed_at);
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function updateAnswer_確認ありのフォームで企画側の更新は読み込んだ版の指定がないと拒否される()
    {
        $form = factory(Form::class)->create(['requires_review' => true]);
        $circle = factory(Circle::class)->create();
        $answer = $this->answersSerivce->createAnswer($form, $circle);
        $answer->refresh();

        $this->expectException(StaleAnswerException::class);
        try {
            $this->answersSerivce->updateAnswer($form, $answer);
        } finally {
            $this->assertSame(1, $answer->revisions()->count());
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function updateAnswer_完了にした回答は企画側から更新できない()
    {
        $form = factory(Form::class)->create(['requires_review' => true]);
        $circle = factory(Circle::class)->create();
        $answer = $this->answersSerivce->createAnswer($form, $circle);
        $staff = factory(User::class)->state('staff')->create();
        $answer->refresh();
        $this->answersSerivce->acceptAnswer($answer, $staff, $answer->lock_version);
        $answer->refresh();

        $this->expectException(StaleAnswerException::class);
        try {
            $this->answersSerivce->updateAnswer($form, $answer, null, null, false, $answer->lock_version);
        } finally {
            $this->assertSame(Answer::REVIEW_STATUS_ACCEPTED, $answer->fresh()->review_status);
        }
    }
}

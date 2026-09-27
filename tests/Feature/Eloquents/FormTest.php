<?php

namespace Tests\Feature\Eloquents;

use App\Eloquents\Answer;
use App\Eloquents\Circle;
use App\Eloquents\Form;
use App\Eloquents\FormAssignment;
use App\Eloquents\Tag;
use App\Policies\FormPolicy;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FormTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function scopeByCircle_非公開または受付期間外のeveryoneフォームはpublicとopenを組み合わせても漏れない()
    {
        $circle = factory(Circle::class)->create();

        $privateForm = factory(Form::class)->create([
            'is_public' => false,
            'audience' => 'everyone',
            'open_at' => now()->subDay(),
            'close_at' => now()->addDay(),
        ]);

        $closedForm = factory(Form::class)->create([
            'is_public' => true,
            'audience' => 'everyone',
            'open_at' => now()->subMonths(2),
            'close_at' => now()->subMonth(),
        ]);

        $visibleIds = Form::byCircle($circle)->public()->open()->pluck('id');

        $this->assertFalse($visibleIds->contains($privateForm->id));
        $this->assertFalse($visibleIds->contains($closedForm->id));
    }

    public static function 公開範囲による回答可否_provider()
    {
        return [
            'everyone・関係ない企画' => ['everyone', 'unrelated', true],
            'everyone・タグ一致企画' => ['everyone', 'matching_tag', true],
            'everyone・送付済み企画' => ['everyone', 'assigned', true],
            'selected・関係ない企画' => ['selected', 'unrelated', false],
            'selected・タグ一致企画' => ['selected', 'matching_tag', true],
            'selected・送付済み企画' => ['selected', 'assigned', true],
        ];
    }

    #[\PHPUnit\Framework\Attributes\Test]
    #[\PHPUnit\Framework\Attributes\DataProvider('公開範囲による回答可否_provider')]
    public function 公開範囲による回答可否(string $audience, string $circleType, bool $expected)
    {
        $tag = factory(Tag::class)->create();
        $form = factory(Form::class)->create(['audience' => $audience]);
        $form->answerableTags()->attach($tag->id);

        $matchingCircle = factory(Circle::class)->create();
        $matchingCircle->tags()->attach($tag->id);

        $assignedCircle = factory(Circle::class)->create();
        FormAssignment::create(['form_id' => $form->id, 'circle_id' => $assignedCircle->id]);

        $unrelatedCircle = factory(Circle::class)->create();

        $circle = [
            'matching_tag' => $matchingCircle,
            'assigned' => $assignedCircle,
            'unrelated' => $unrelatedCircle,
        ][$circleType];

        $visible = Form::byCircle($circle)->pluck('id')->contains($form->id);
        $this->assertSame($expected, $visible, 'Form::byCircle の結果が期待と異なります');

        $policy = new FormPolicy();
        $this->assertSame($expected, $policy->view(null, $form, $circle), 'FormPolicy::view の結果が期待と異なります');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function notAnswered_対象企画のうち未回答の承認済み企画だけを返す()
    {
        $tag = factory(Tag::class)->create();
        $form = factory(Form::class)->create(['audience' => 'selected']);
        $form->answerableTags()->attach($tag->id);

        $taggedAnswered = factory(Circle::class)->create();
        $taggedAnswered->tags()->attach($tag->id);
        factory(Answer::class)->create(['form_id' => $form->id, 'circle_id' => $taggedAnswered->id]);

        $taggedUnanswered = factory(Circle::class)->create();
        $taggedUnanswered->tags()->attach($tag->id);

        $assignedUnanswered = factory(Circle::class)->create();
        FormAssignment::create(['form_id' => $form->id, 'circle_id' => $assignedUnanswered->id]);

        factory(Circle::class)->create(); // 対象ではない企画

        $notApprovedButTagged = factory(Circle::class)->states('notSubmitted')->create();
        $notApprovedButTagged->tags()->attach($tag->id);

        $result = $form->notAnswered()->pluck('id')->all();

        $this->assertEqualsCanonicalizing(
            [$taggedUnanswered->id, $assignedUnanswered->id],
            $result
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function effectiveDueDateFor_割当がなければclose_atが返る()
    {
        $circle = factory(Circle::class)->create();
        $form = factory(Form::class)->create(['close_at' => new Carbon('2026-10-08 23:59:59')]);

        $this->assertTrue($form->effectiveDueDateFor($circle)->equalTo($form->close_at));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function effectiveDueDateFor_割当のdue_atが優先される()
    {
        $circle = factory(Circle::class)->create();
        $form = factory(Form::class)->create(['close_at' => new Carbon('2026-10-31 23:59:59')]);
        FormAssignment::create([
            'form_id' => $form->id,
            'circle_id' => $circle->id,
            'due_at' => new Carbon('2026-10-01 00:00:00'),
        ]);

        $this->assertTrue($form->effectiveDueDateFor($circle)->equalTo(new Carbon('2026-10-01 00:00:00')));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function isOverdueFor_確認不要フォームは期限を過ぎて未回答の場合のみ期限切れ()
    {
        Carbon::setTestNow(new Carbon('2026-10-10 00:00:00'));

        $circle = factory(Circle::class)->create();

        $overdueForm = factory(Form::class)->create(['close_at' => new Carbon('2026-10-01 00:00:00')]);
        $this->assertTrue($overdueForm->isOverdueFor($circle));

        factory(Answer::class)->create(['form_id' => $overdueForm->id, 'circle_id' => $circle->id]);
        $this->assertFalse($overdueForm->fresh()->isOverdueFor($circle));

        $notYetDueForm = factory(Form::class)->create(['close_at' => new Carbon('2026-11-01 00:00:00')]);
        $this->assertFalse($notYetDueForm->isOverdueFor($circle));

        Carbon::setTestNow();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function isOverdueFor_確認必須フォームは差し戻されたまま期限を過ぎた場合に期限切れ()
    {
        Carbon::setTestNow(new Carbon('2026-10-10 00:00:00'));

        $circle = factory(Circle::class)->create();
        $form = factory(Form::class)->create([
            'close_at' => new Carbon('2026-10-01 00:00:00'),
            'requires_review' => true,
        ]);

        // 未提出
        $this->assertTrue($form->isOverdueFor($circle));

        // 差し戻されたまま
        $answer = factory(Answer::class)->create([
            'form_id' => $form->id,
            'circle_id' => $circle->id,
            'review_status' => Answer::REVIEW_STATUS_RETURNED,
        ]);
        $this->assertTrue($form->isOverdueFor($circle));

        // 確認中（提出済み）は期限切れではない
        $answer->update(['review_status' => Answer::REVIEW_STATUS_SUBMITTED]);
        $this->assertFalse($form->isOverdueFor($circle));

        // 完了した場合は期限切れではない
        $answer->update(['review_status' => Answer::REVIEW_STATUS_ACCEPTED]);
        $this->assertFalse($form->isOverdueFor($circle));

        Carbon::setTestNow();
    }
}

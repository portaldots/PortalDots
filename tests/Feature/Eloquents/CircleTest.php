<?php

namespace Tests\Feature\Eloquents;

use App\Eloquents\Answer;
use App\Eloquents\AnswerDetail;
use App\Eloquents\Circle;
use App\Eloquents\Form;
use App\Eloquents\ParticipationType;
use App\Eloquents\Question;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CircleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function getParticipationFormAnswer()
    {
        // 準備
        $participationForm = factory(Form::class)->create();
        $participationType = ParticipationType::factory()->create([
            'form_id' => $participationForm->id
        ]);
        $question = factory(Question::class)->create([
            'form_id' => $participationForm->id,
            'name' => '設問です',
            'type' => 'text'
        ]);

        $otherCircles = factory(Circle::class, 10)->create([
            'participation_type_id' => $participationType->id
        ]);
        $myCircle = factory(Circle::class)->create([
            'participation_type_id' => $participationType->id
        ]);

        $i = 0;
        foreach ($otherCircles as $otherCircle) {
            $answer = factory(Answer::class)->create([
                'form_id' => $participationForm->id,
                'circle_id' => $otherCircle->id,
            ]);
            factory(AnswerDetail::class)->create([
                'answer_id' => $answer->id,
                'question_id' => $question->id,
            ]);
            $i++;

            if ($i === 5) {
                $myAnswer = factory(Answer::class)->create([
                    'form_id' => $participationForm->id,
                    'circle_id' => $myCircle->id,
                ]);
            }
        }
        factory(AnswerDetail::class)->create([
            'answer_id' => $myAnswer->id,
            'question_id' => $question->id,
        ]);

        // テスト
        $result = $myCircle->getParticipationFormAnswer();
        $this->assertSame($myAnswer->id, $result->id);
    }
}

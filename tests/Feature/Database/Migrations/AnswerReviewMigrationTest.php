<?php

namespace Tests\Feature\Database\Migrations;

use App\Eloquents\Answer;
use App\Eloquents\Circle;
use App\Eloquents\Form;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnswerReviewMigrationTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function 既存の回答は最終更新日時を提出日時として引き継ぎreview_statusはNULLのまま()
    {
        $form = factory(Form::class)->create();
        $circle = factory(Circle::class)->create();
        $answer = factory(Answer::class)->create([
            'form_id' => $form->id,
            'circle_id' => $circle->id,
        ]);
        $updated_at = $answer->updated_at;

        // 既存データが入った状態でマイグレーションを再実行し、移行結果を確認する
        $migration = require database_path('migrations/2026_09_28_010100_add_review_columns_to_answers_table.php');
        $migration->down();
        $migration->up();

        $answer->refresh();

        $this->assertNull($answer->review_status);
        $this->assertNotNull($answer->submitted_at);
        $this->assertTrue($answer->submitted_at->eq($updated_at));
        $this->assertSame(0, $answer->lock_version);
    }
}

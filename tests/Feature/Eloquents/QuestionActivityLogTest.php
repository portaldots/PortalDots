<?php

namespace Tests\Feature\Eloquents;

use App\Eloquents\Question;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class QuestionActivityLogTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function updating_a_required_flag_records_old_and_new_values(): void
    {
        $question = factory(Question::class)->create(['is_required' => false]);
        Activity::query()->delete();

        $question->update(['is_required' => true]);

        $activity = Activity::query()->sole();

        $this->assertSame(true, $activity->properties->get('attributes')['is_required']);
        $this->assertSame(false, $activity->properties->get('old')['is_required']);
    }
}

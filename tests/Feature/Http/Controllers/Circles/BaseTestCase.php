<?php

namespace Tests\Feature\Http\Controllers\Circles;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\CarbonImmutable;
use App\Eloquents\Form;
use App\Eloquents\ParticipationType;

abstract class BaseTestCase extends TestCase
{
    use RefreshDatabase;

    protected ?ParticipationType $participationType;
    protected ?Form $participationForm;

    public function setUp(): void
    {
        parent::setUp();

        $this->participationForm = factory(Form::class)->create([
            'open_at' => new CarbonImmutable('2020-01-26 11:42:51'),
            'close_at' => new CarbonImmutable('2020-03-26 15:23:31'),
        ]);
        $this->participationType = ParticipationType::factory()->create([
            'form_id' => $this->participationForm->id,
            'users_count_min' => 3,
            'users_count_max' => 5,
        ]);
    }
}

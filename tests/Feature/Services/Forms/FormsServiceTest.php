<?php

namespace Tests\Feature\Services\Forms;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Eloquents\Form;
use App\Eloquents\Question;
use App\Eloquents\User;
use App\Services\Forms\FormsService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;

class FormsServiceTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->formsService = App::make(FormsService::class);
        $this->user = factory(User::class)->create();
        $this->form = factory(Form::class)->create([
            'name' => 'テスト申請',
            'is_public' => true,
        ]);
        $this->questions = factory(Question::class, 10)->create([
            'form_id' => $this->form->id,
        ]);
    }

    /**
     * @test
     */
    public function copyForm_申請の複製ができる()
    {
        $form = $this->formsService->copyForm($this->form, $this->user);

        $this->assertInstanceOf(Form::class, $form);

        $this->assertDatabaseHas('forms', [
            'name' => 'テスト申請のコピー',
            'is_public' => false,
            'created_by' => $this->user->id,
        ]);

        foreach ($this->questions as $question) {
            $question = $question->toArray();
            Arr::forget($question, 'id');
            Arr::set($question, 'form_id', $form->id);
            $this->assertDatabaseHas('questions', $question);
        }
    }
}

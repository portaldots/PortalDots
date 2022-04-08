<?php

namespace Tests\Feature\Services\Forms;

use App\Eloquents\Answer;
use App\Eloquents\Circle;
use App\Eloquents\Form;
use App\Eloquents\Question;
use App\Eloquents\User;
use App\Services\Forms\AnswerDetailsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AnswerDetailsServiceTest extends TestCase
{
    use RefreshDatabase;

    /** @var AnswerDetailsService */
    private $answerDetailsService;

    /** @var User */
    private $user;

    /** @var Circle */
    private $circle;

    public function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');

        $this->answerDetailsService = App::make(AnswerDetailsService::class);

        $this->user = factory(User::class)->create();
        $this->circle = factory(Circle::class)->create();

        $this->circle->users()->save($this->user);
    }

    /**
     * @test
     */
    public function updateAnswerDetails_ファイルの更新した時に古いファイルが削除される()
    {
        $form = factory(Form::class)->create();

        $file_upload = factory(Question::class)->create([
            'form_id' => $form->id,
            'type' => 'upload'
        ]);

        $answer = factory(Answer::class)->create([
            'form_id' => $form->id,
            'circle_id' => $this->circle->id
        ]);

        Auth::login($this->user);

        $old_file = UploadedFile::fake()->create('file.jpeg', 0, 'image/jpeg')->store('answer_details');

        $this->answerDetailsService->updateAnswerDetails($form, $answer, [$file_upload->id => $old_file]);

        // ファイルの存在を確認
        Storage::disk('local')->assertExists($old_file);

        $new_file = UploadedFile::fake()->create('update.png', 0, 'image/png')->store('answer_details');

        $this->answerDetailsService->updateAnswerDetails($form, $answer, [$file_upload->id => $new_file]);

        Storage::disk('local')->assertExists($new_file);
        Storage::disk('local')->assertMissing($old_file);
    }

    /**
     * @test
     */
    public function updateAnswerDetails_ファイルの削除した時に古いファイルが削除される()
    {
        $form = factory(Form::class)->create();

        $file_upload = factory(Question::class)->create([
            'form_id' => $form->id,
            'type' => 'upload'
        ]);

        $answer = factory(Answer::class)->create([
            'form_id' => $form->id,
            'circle_id' => $this->circle->id
        ]);

        Auth::login($this->user);

        $file = UploadedFile::fake()->create('file.jpeg', 0, 'image/jpeg')->store('answer_details');

        $this->answerDetailsService->updateAnswerDetails($form, $answer, [$file_upload->id => $file]);

        // ファイルの存在を確認
        Storage::disk('local')->assertExists($file);

        $this->answerDetailsService->updateAnswerDetails($form, $answer, []);

        Storage::disk('local')->assertMissing($file);
    }

    /**
     * @test
     */
    public function updateAnswerDetails_ファイルの更新をしていない時はアップロードされたファイルを削除しない()
    {
        $form = factory(Form::class)->create();

        $file_upload = factory(Question::class)->create([
            'form_id' => $form->id,
            'type' => 'upload'
        ]);

        $answer = factory(Answer::class)->create([
            'form_id' => $form->id,
            'circle_id' => $this->circle->id
        ]);

        Auth::login($this->user);

        $file = UploadedFile::fake()->create('file.jpeg', 0, 'image/jpeg')->store('answer_details');

        $this->answerDetailsService->updateAnswerDetails($form, $answer, [$file_upload->id => $file]);

        // ファイルの存在を確認
        Storage::disk('local')->assertExists($file);

        $this->answerDetailsService->updateAnswerDetails($form, $answer, [$file_upload->id => '__KEEP__']);

        Storage::disk('local')->assertExists($file);
    }
}

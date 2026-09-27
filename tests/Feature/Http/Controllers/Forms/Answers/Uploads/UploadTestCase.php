<?php

namespace Tests\Feature\Http\Controllers\Forms\Answers\Uploads;

use App\Eloquents\Answer;
use App\Eloquents\AnswerDetail;
use App\Eloquents\Circle;
use App\Eloquents\Form;
use App\Eloquents\Question;
use App\Eloquents\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

abstract class UploadTestCase extends TestCase
{
    use RefreshDatabase;

    protected $routeName;
    protected $form;
    protected $answer;
    protected $question;
    protected $detail;
    protected $user;

    public function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');

        $this->form = factory(Form::class)->create();
        $this->question = factory(Question::class)->create([
            'form_id' => $this->form->id,
            'type' => 'upload',
        ]);
        $circle = factory(Circle::class)->create();
        $this->answer = factory(Answer::class)->create([
            'form_id' => $this->form->id,
            'circle_id' => $circle->id,
        ]);
        $this->user = factory(User::class)->create();
        $this->user->circles()->attach($circle->id, ['is_leader' => true]);
        $this->detail = factory(AnswerDetail::class)->create([
            'answer_id' => $this->answer->id,
            'question_id' => $this->question->id,
            'answer' => 'answer_details/upload.txt',
        ]);
        Storage::put($this->detail->answer, 'uploaded content');
        $this->actingAs($this->user);
    }

    protected function getUpload(array $parameters = [])
    {
        return $this->get(route($this->routeName, array_merge([
            'form' => $this->form,
            'answer' => $this->answer,
            'question' => $this->question,
        ], $parameters)));
    }

    public function test_downloads_the_uploaded_file()
    {
        $response = $this->getUpload()->assertOk();

        $this->assertSame(
            realpath(Storage::path($this->detail->answer)),
            $response->baseResponse->getFile()->getRealPath()
        );
    }

    public function test_rejects_an_answer_from_another_form()
    {
        $this->getUpload(['form' => factory(Form::class)->create()])->assertNotFound();
    }

    public function test_rejects_a_question_from_another_form()
    {
        $this->question->form_id = factory(Form::class)->create()->id;
        $this->question->save();

        $this->getUpload()->assertNotFound();
    }

    /** @dataProvider nonUploadTypes */
    public function test_rejects_non_upload_questions(string $type)
    {
        $this->question->type = $type;
        $this->question->save();

        $this->getUpload()->assertNotFound();
    }

    public static function nonUploadTypes(): array
    {
        return array_map(fn ($type) => [$type], [
            'heading', 'text', 'textarea', 'number', 'radio', 'checkbox', 'select',
        ]);
    }

    public function test_rejects_a_missing_answer_detail()
    {
        $this->detail->delete();

        $this->getUpload()->assertNotFound();
    }

    public function test_returns_not_found_for_a_missing_file()
    {
        Storage::delete($this->detail->answer);

        $this->getUpload()->assertNotFound();
    }

    /** @dataProvider invalidPaths */
    public function test_rejects_paths_outside_the_upload_directory(string $path)
    {
        Storage::put('private.txt', 'private content');
        Storage::put('answer_details_backup/private.txt', 'private content');
        $this->detail->answer = $path;
        $this->detail->save();

        $this->getUpload()->assertNotFound();
    }

    public static function invalidPaths(): array
    {
        return [
            'another directory' => ['private.txt'],
            'parent directory' => ['answer_details/../private.txt'],
            'similar directory prefix' => ['answer_details/../answer_details_backup/private.txt'],
            'directory' => ['answer_details/'],
            'empty path' => [''],
        ];
    }

    public function test_rejects_a_symlink_to_a_file_outside_the_upload_directory()
    {
        Storage::put('private.txt', 'private content');
        Storage::delete($this->detail->answer);
        symlink(Storage::path('private.txt'), Storage::path($this->detail->answer));

        $this->getUpload()->assertNotFound();
    }
}

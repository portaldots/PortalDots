<?php

namespace Tests\Feature\Services\Forms;

use ZipArchive;
use Mockery;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\App;
use App\Eloquents\Form;
use App\Eloquents\Question;
use App\Eloquents\Answer;
use App\Eloquents\AnswerDetail;
use App\Services\Forms\DownloadZipService;
use App\Services\Forms\Exceptions\NoDownloadFileExistException;
use App\Services\Forms\Exceptions\ZipArchiveNotSupportedException;

class DownloadZipServiceTest extends TestCase
{
    // use RefreshDatabase;

    private $form;
    private $answer;
    private $answer_details = [];
    private $another_answer;
    private $another_answer_details = [];

    private const UPLOADS_NUMBER = 7;

    public function setUp(): void
    {
        parent::setUp();
        $this->form = factory(Form::class)->create();
        $this->questions = factory(Question::class, self::UPLOADS_NUMBER)->create([
            'form_id' => $this->form->id,
            'type' => 'upload',
            'number_max' => 1000000000,
            'allowed_types' => 'png|jpg|jpeg|gif',
            'options' => null,
        ]);

        // アップロードタイプ以外の設問もある程度用意
        factory(Question::class, 7)->create([
            'form_id' => $this->form->id,
        ]);

        // 回答
        $this->answer = factory(Answer::class)->create([
            'form_id' => $this->form->id,
        ]);

        $example_file = new File(base_path('tests/TestFile.png'));

        $count = 0;
        foreach ($this->questions as $question) {
            $filename = 'testfile_' . sha1($this->answer->id . '_' . $question->id) . '.png';
            $this->answer_details[] = factory(AnswerDetail::class)->create([
                'answer_id' => $this->answer->id,
                'question_id' => $question->id,
                'answer' => 'answer_details/' . $filename,
            ]);
            Storage::putFileAs('answer_details', $example_file, $filename);

            if (++$count > 4) {
                // 全設問に対してはアップロードしない
                break;
            }
        }

        $this->another_answer = factory(Answer::class)->create([
            'form_id' => $this->form->id,
        ]);

        foreach ($this->questions as $question) {
            $filename = 'testfile_' . sha1($this->another_answer->id . '_' . $question->id) . '.png';
            $this->another_answer_details[] = factory(AnswerDetail::class)->create([
                'answer_id' => $this->another_answer->id,
                'question_id' => $question->id,
                'answer' => 'answer_details/' . $filename,
            ]);
            Storage::putFileAs('answer_details', $example_file, $filename);
        }
    }

    public function tearDown(): void
    {
        foreach ($this->questions as $question) {
            $filename = 'testfile_' . sha1($this->answer->id . '_' . $question->id) . '.png';
            $another_filename = 'testfile_' . sha1($this->another_answer->id . '_' . $question->id) . '.png';

            Storage::delete('answer_details/' . $filename);
            Storage::delete('answer_details/' . $another_filename);
        }

        parent::tearDown();
        Mockery::close();
    }

    /**
     * @test
     */
    public function makeZip_アップロードされたファイルが全てZIPファイルに含まれるか()
    {
        $this->mock(ZipArchive::class, function ($mock) {
            $mock->shouldReceive('open')
                ->ordered()
                ->with(Mockery::any(), ZipArchive::CREATE)
                ->once()
                ->andReturn(true);

            $count = 0;
            foreach ($this->questions as $question) {
                $filename = 'testfile_' . sha1($this->answer->id . '_' . $question->id) . '.png';

                $mock->shouldReceive('addFile')
                    ->ordered()
                    ->with(Storage::path("answer_details/{$filename}"), "answer_details__{$filename}")
                    ->once();

                if (++$count > 4) {
                    // 全設問に対してはアップロードしない
                    break;
                }
            }

            foreach ($this->questions as $question) {
                $filename = 'testfile_' . sha1($this->another_answer->id . '_' . $question->id) . '.png';

                $mock->shouldReceive('addFile')
                    ->ordered()
                    ->with(Storage::path("answer_details/{$filename}"), "answer_details__{$filename}")
                    ->once();
            }

            $mock->shouldReceive('close')
                ->ordered()
                ->once();
        });

        $downloadZipService = App::make(DownloadZipService::class);

        $uploaded_file_paths = array_map(function ($answer_detail) {
            return $answer_detail->answer;
        }, $this->answer_details);

        $another_uploaded_file_paths = array_map(function ($answer_detail) {
            return $answer_detail->answer;
        }, $this->another_answer_details);

        // 存在しないファイルを途中に含めるテスト
        $no_exist_file_paths = [
            'answer_details/foobar1.png',
            'answer_details/foobar2.png',
        ];

        $downloadZipService->makeZip($this->form, array_merge(
            $uploaded_file_paths,
            $no_exist_file_paths,
            $another_uploaded_file_paths
        ));
    }

    /**
     * @test
     */
    public function makeZip_ZipArchiveがエラーの時に適切な例外が発生する()
    {
        $this->expectException(ZipArchiveNotSupportedException::class);

        $this->mock(ZipArchive::class, function ($mock) {
            $mock->shouldReceive('open')
                        ->ordered()
                        ->with(Mockery::any(), ZipArchive::CREATE)
                        ->once()
                        ->andReturn(false);
        });

        $uploaded_file_paths = array_map(function ($answer_detail) {
            return $answer_detail->answer;
        }, $this->answer_details);

        App::make(DownloadZipService::class)->makeZip($this->form, $uploaded_file_paths);
    }

    /**
     * @test
     */
    public function makeZip_第二引数が空の時に適切な例外が発生する()
    {
        $this->expectException(NoDownloadFileExistException::class);

        // 例外は open メソッドが呼ばれる前に throw するため、
        // メソッドは一切、モックに用意しない
        $this->mock(ZipArchive::class);

        App::make(DownloadZipService::class)->makeZip($this->form, []);
    }
}

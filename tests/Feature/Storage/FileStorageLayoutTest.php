<?php

namespace Tests\Feature\Storage;

use App\Contracts\FileStorageLayout;
use App\Eloquents\Answer;
use App\Eloquents\AnswerDetail;
use App\Eloquents\Circle;
use App\Eloquents\Form;
use App\Eloquents\Permission;
use App\Eloquents\Question;
use App\Eloquents\User;
use App\Services\Documents\DocumentsService;
use App\Services\Threads\ThreadsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * FileStorageLayout を別ディレクトリへ差し替えた場合に、新規保存・配信の
 * 両方がそのディレクトリを使い、他のディレクトリを指すパスは拒否されることを確認する
 */
class FileStorageLayoutTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');

        $this->app->bind(FileStorageLayout::class, function () {
            return new class implements FileStorageLayout {
                public function directoryFor(string $area): string
                {
                    return "tenants/7/{$area}";
                }
            };
        });
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 回答のアップロードファイルが差し替え先のディレクトリに保存されダウンロードできる()
    {
        $form = factory(Form::class)->create();
        $question = factory(Question::class)->create([
            'form_id' => $form->id,
            'type' => 'upload',
            'is_required' => false,
            'number_min' => null,
            'number_max' => null,
            'allowed_types' => 'pdf',
        ]);
        $circle = factory(Circle::class)->create();
        $user = factory(User::class)->create();
        $user->circles()->attach($circle->id, ['is_leader' => true]);

        $response = $this->actingAs($user)->post(route('forms.answers.store', $form), [
            'circle_id' => $circle->id,
            'answers' => [
                $question->id => UploadedFile::fake()->create('file.pdf', 1, 'application/pdf'),
            ],
        ]);
        $response->assertRedirect();

        $answer = Answer::sole();
        $path = AnswerDetail::where('answer_id', $answer->id)->where('question_id', $question->id)->value('answer');
        $this->assertStringStartsWith('tenants/7/answer_details/', $path);
        Storage::disk('local')->assertExists($path);

        $downloadResponse = $this->actingAs($user)->get(route('forms.answers.uploads.show', [
            'form' => $form,
            'answer' => $answer,
            'question' => $question,
        ]));
        $downloadResponse->assertOk();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 差し替え先のディレクトリの外を指すパスはダウンロードを拒否される()
    {
        $form = factory(Form::class)->create();
        $question = factory(Question::class)->create(['form_id' => $form->id, 'type' => 'upload']);
        $circle = factory(Circle::class)->create();
        $user = factory(User::class)->create();
        $user->circles()->attach($circle->id, ['is_leader' => true]);
        $answer = factory(Answer::class)->create(['form_id' => $form->id, 'circle_id' => $circle->id]);

        // 差し替え前のデフォルトディレクトリを指す、既定レイアウトの外のパス
        $outsidePath = 'answer_details/outside.txt';
        Storage::put($outsidePath, 'content');
        factory(AnswerDetail::class)->create([
            'answer_id' => $answer->id,
            'question_id' => $question->id,
            'answer' => $outsidePath,
        ]);

        $response = $this->actingAs($user)->get(route('forms.answers.uploads.show', [
            'form' => $form,
            'answer' => $answer,
            'question' => $question,
        ]));

        $response->assertNotFound();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 配布資料の版が差し替え先のディレクトリに保存されダウンロードできる()
    {
        $documentsService = App::make(DocumentsService::class);
        $staff = factory(User::class)->states('staff')->create();
        Permission::create(['name' => 'staff.documents.read']);
        $staff->syncPermissions(['staff.documents.read']);

        $document = $documentsService->createDocument(
            '出店の手引き',
            null,
            UploadedFile::fake()->create('第1版.pdf', 1, 'application/pdf'),
            true,
            false,
            null,
            'everyone',
            [],
            [],
            $staff
        );

        $this->assertStringStartsWith('tenants/7/documents/', $document->path);
        Storage::disk('local')->assertExists($document->path);

        $version = $document->versions()->sole();
        $response = $this->actingAs($staff)
            ->withSession(['staff_authorized' => true])
            ->get(route('staff.documents.versions.show', ['document' => $document, 'version' => $version]));

        $response->assertOk();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 会話の添付ファイルが差し替え先のディレクトリに保存されダウンロードできる()
    {
        $threadsService = App::make(ThreadsService::class);
        $circle = factory(Circle::class)->create();
        $member = factory(User::class)->create();
        $circle->users()->attach($member->id, ['is_leader' => true]);

        $thread = $threadsService->getOrCreateForCircle($circle);
        $entry = $threadsService->postCircleMessage(
            $thread,
            $member,
            '添付します',
            null,
            [UploadedFile::fake()->create('file.pdf', 1, 'application/pdf')],
            (string)Str::uuid()
        );

        $attachment = $entry->attachments()->sole();
        $this->assertStringStartsWith('tenants/7/thread_attachments/', $attachment->path);
        Storage::disk('local')->assertExists($attachment->path);

        $response = $this->actingAs($member)
            ->get(route('contacts.attachments.show', ['attachment' => $attachment]));

        $response->assertOk();
    }
}

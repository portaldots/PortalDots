<?php

namespace Tests\Feature\Http\Controllers\Documents;

use App\Eloquents\Document;
use App\Eloquents\User;
use App\Services\Documents\DocumentsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VersionsShowActionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var DocumentsService
     */
    private $documentsService;

    /**
     * @var User
     */
    private $user;

    public function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        $this->documentsService = App::make(DocumentsService::class);
        $this->user = factory(User::class)->create();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 過去の版をダウンロードできる()
    {
        $document = $this->documentsService->createDocument(
            '配布資料',
            null,
            UploadedFile::fake()->create('第1版.pdf', 1, 'application/pdf'),
            true,
            false,
            null
        );

        $this->documentsService->updateDocument(
            $document,
            '配布資料',
            null,
            UploadedFile::fake()->create('第2版.pdf', 1, 'application/pdf'),
            true,
            false,
            null
        );

        $firstVersion = $document->versions()->where('version', 1)->firstOrFail();

        $response = $this->actingAs($this->user)
            ->get(route('documents.versions.show', ['document' => $document, 'version' => $firstVersion]));

        $response->assertOk();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 別の配布資料の版を指定すると404になる()
    {
        // $document は第1版のみ、$otherDocument は第2版まで存在する状態を作り、
        // $document に対して他方にしかない版番号を指定したときに404になることを確認する
        $document = $this->documentsService->createDocument(
            '配布資料A',
            null,
            UploadedFile::fake()->create('A.pdf', 1, 'application/pdf'),
            true,
            false,
            null
        );
        $otherDocument = $this->documentsService->createDocument(
            '配布資料B',
            null,
            UploadedFile::fake()->create('B1.pdf', 1, 'application/pdf'),
            true,
            false,
            null
        );
        $this->documentsService->updateDocument(
            $otherDocument,
            '配布資料B',
            null,
            UploadedFile::fake()->create('B2.pdf', 1, 'application/pdf'),
            true,
            false,
            null
        );
        $otherDocumentsSecondVersion = $otherDocument->versions()->where('version', 2)->firstOrFail();

        $response = $this->actingAs($this->user)
            ->get(route('documents.versions.show', [
                'document' => $document,
                'version' => $otherDocumentsSecondVersion,
            ]));

        $response->assertStatus(404);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 非公開の配布資料は版もダウンロードできない()
    {
        $document = $this->documentsService->createDocument(
            '配布資料',
            null,
            UploadedFile::fake()->create('配布資料.pdf', 1, 'application/pdf'),
            false,
            false,
            null
        );
        $version = $document->versions()->where('version', 1)->firstOrFail();

        $response = $this->actingAs($this->user)
            ->get(route('documents.versions.show', ['document' => $document, 'version' => $version]));

        $response->assertStatus(404);
    }
}

<?php

namespace Tests\Feature\Services\Documents;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\Documents\DocumentsService;
use Tests\TestCase;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use App\Eloquents\User;

class DocumentsServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var DocumentsService
     */
    private $documentsService;

    /**
     * @var User
     */
    private $staff;

    public function setUp(): void
    {
        parent::setUp();
        $this->documentsService = App::make(DocumentsService::class);
        $this->staff = factory(User::class)->state('staff')->create();
    }

    /**
     * @test
     */
    public function createDocument()
    {
        Storage::fake('local');

        $filesize = 1;  // 単位 : KiB
        $file = UploadedFile::fake()->create('第２回.pdf', $filesize, 'application/pdf');

        $this->documentsService->createDocument(
            '第２回会議資料',
            '第２回会議にて配布した資料のPDFバージョンです',
            $file,
            true,
            false,
            'メモです'
        );

        Storage::disk('local')->assertExists("documents/{$file->hashName()}");

        $this->assertDatabaseHas('documents', [
            'name' => '第２回会議資料',
            'description' => '第２回会議にて配布した資料のPDFバージョンです',
            'path' => "documents/{$file->hashName()}",
            'size' => $filesize * 1024, // 単位 : バイト
            'extension' => 'pdf',
            'is_public' => true,
            'is_important' => false,
            'notes' => 'メモです'
        ]);
    }

    /**
     * @test
     */
    public function updateDocument_ファイルはアップデートせずに更新できる()
    {
        $document = $this->documentsService->createDocument(
            '第２回会議資料',
            '第２回会議にて配布した資料のPDFバージョンです',
            UploadedFile::fake()->create('第２回.pdf', 1, 'application/pdf'),
            true,
            false,
            'メモです'
        );

        $this->documentsService->updateDocument(
            $document,
            'updated filename',
            'updated description',
            null,
            false,
            true,
            'updated notes'
        );

        $this->assertDatabaseHas('documents', [
            'name' => 'updated filename',
            'description' => 'updated description',
            'is_public' => false,
            'is_important' => true,
            'notes' => 'updated notes'
        ]);
    }

    /**
     * @test
     */
    public function updateDocument_ファイルのアップデートができる()
    {
        Storage::fake('local');
        $oldFile = UploadedFile::fake()->create('第２回.pdf', 1, 'application/pdf');

        $document = $this->documentsService->createDocument(
            '第２回会議資料',
            '第２回会議にて配布した資料のPDFバージョンです',
            $oldFile,
            true,
            false,
            'メモです'
        );

        $this->documentsService->updateDocument(
            $document,
            'updated filename',
            'updated description',
            UploadedFile::fake()->create('update.jpeg', 1, 'image/jpeg'),
            false,
            true,
            'updated notes'
        );

        Storage::disk('local')->assertMissing("document/{$oldFile->hashName()}");

        $this->assertDatabaseHas('documents', [
            'name' => 'updated filename',
            'description' => 'updated description',
            'extension' => 'jpeg',
            'is_public' => false,
            'is_important' => true,
            'notes' => 'updated notes'
        ]);
    }

    /**
     * @test
     */
    public function deleteDocument_ファイルの削除ができる()
    {
        Storage::fake('local');
        $file = UploadedFile::fake()->create('削除されちゃう.pdf', 1, 'application/pdf');

        $document = $this->documentsService->createDocument(
            '削除される資料',
            '削除される資料です。悲しいね。',
            $file,
            true,
            false,
            'ドロン'
        );

        Storage::disk('local')->assertExists("documents/{$file->hashName()}");

        $this->documentsService->deleteDocument($document);

        Storage::disk('local')->assertMissing("documents/{$file->hashName()}");
        $this->assertDatabaseMissing('documents', [
            'id' => $document->id,
            'name' => '削除される資料です'
        ]);
    }
}

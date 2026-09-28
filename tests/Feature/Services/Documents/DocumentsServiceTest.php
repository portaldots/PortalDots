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

    #[\PHPUnit\Framework\Attributes\Test]
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

    #[\PHPUnit\Framework\Attributes\Test]
    public function createDocument_第1版が作成されアップロードしたスタッフが記録される()
    {
        $document = $this->documentsService->createDocument(
            '第２回会議資料',
            '第２回会議にて配布した資料のPDFバージョンです',
            UploadedFile::fake()->create('第２回.pdf', 1, 'application/pdf'),
            true,
            false,
            'メモです',
            'everyone',
            [],
            [],
            $this->staff
        );

        $this->assertSame(1, $document->versions()->count());

        $version = $document->versions()->first();
        $this->assertSame(1, $version->version);
        $this->assertSame($document->path, $version->path);
        $this->assertSame($this->staff->id, $version->uploaded_by);
    }

    #[\PHPUnit\Framework\Attributes\Test]
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
        $this->assertSame(1, $document->versions()->count());
    }

    #[\PHPUnit\Framework\Attributes\Test]
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

        $newFile = UploadedFile::fake()->create('update.jpeg', 1, 'image/jpeg');
        $this->documentsService->updateDocument(
            $document,
            'updated filename',
            'updated description',
            $newFile,
            false,
            true,
            'updated notes',
            'everyone',
            [],
            [],
            $this->staff
        );

        // 更新前のファイルは削除されず、新しいファイルが第2版として追加される
        Storage::disk('local')->assertExists("documents/{$oldFile->hashName()}");
        Storage::disk('local')->assertExists("documents/{$newFile->hashName()}");

        $document->refresh();

        $this->assertDatabaseHas('documents', [
            'name' => 'updated filename',
            'description' => 'updated description',
            'path' => "documents/{$newFile->hashName()}",
            'extension' => 'jpeg',
            'is_public' => false,
            'is_important' => true,
            'notes' => 'updated notes'
        ]);

        $this->assertSame(2, $document->versions()->count());

        $latestVersion = $document->versions()->orderBy('version', 'desc')->first();
        $this->assertSame(2, $latestVersion->version);
        $this->assertSame("documents/{$newFile->hashName()}", $latestVersion->path);
        $this->assertSame($this->staff->id, $latestVersion->uploaded_by);

        $firstVersion = $document->versions()->where('version', 1)->firstOrFail();
        $this->assertSame("documents/{$oldFile->hashName()}", $firstVersion->path);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function updateDocument_連続してファイルをアップデートすると版が2と3になる()
    {
        Storage::fake('local');

        $document = $this->documentsService->createDocument(
            '第２回会議資料',
            null,
            UploadedFile::fake()->create('第１版.pdf', 1, 'application/pdf'),
            true,
            false,
            null
        );

        $this->documentsService->updateDocument(
            $document,
            '第２回会議資料',
            null,
            UploadedFile::fake()->create('第２版.pdf', 1, 'application/pdf'),
            true,
            false,
            null
        );
        $this->documentsService->updateDocument(
            $document,
            '第２回会議資料',
            null,
            UploadedFile::fake()->create('第３版.pdf', 1, 'application/pdf'),
            true,
            false,
            null
        );

        $versions = $document->versions()->reorder('version', 'asc')->pluck('version')->all();
        $this->assertSame([1, 2, 3], $versions);
    }

    #[\PHPUnit\Framework\Attributes\Test]
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

    #[\PHPUnit\Framework\Attributes\Test]
    public function deleteDocument_すべての版のファイルが削除される()
    {
        Storage::fake('local');
        $firstFile = UploadedFile::fake()->create('第１版.pdf', 1, 'application/pdf');
        $secondFile = UploadedFile::fake()->create('第２版.pdf', 1, 'application/pdf');

        $document = $this->documentsService->createDocument(
            '削除される資料',
            null,
            $firstFile,
            true,
            false,
            null
        );
        $this->documentsService->updateDocument(
            $document,
            '削除される資料',
            null,
            $secondFile,
            true,
            false,
            null
        );

        Storage::disk('local')->assertExists("documents/{$firstFile->hashName()}");
        Storage::disk('local')->assertExists("documents/{$secondFile->hashName()}");

        $this->documentsService->deleteDocument($document);

        Storage::disk('local')->assertMissing("documents/{$firstFile->hashName()}");
        Storage::disk('local')->assertMissing("documents/{$secondFile->hashName()}");
        $this->assertDatabaseMissing('document_versions', [
            'document_id' => $document->id,
        ]);
    }
}

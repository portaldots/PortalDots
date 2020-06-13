<?php

namespace Tests\Feature\Services\Documents;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Services\Documents\DocumentsService;
use Tests\TestCase;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use App\Eloquents\User;
use App\Eloquents\Schedule;

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

        $schedule = factory(Schedule::class)->create();

        $document = $this->documentsService->createDocument(
            '第２回会議資料',
            '第２回会議にて配布した資料のPDFバージョンです',
            $file,
            $this->staff,
            true,
            false,
            $schedule,
            'メモです'
        );

        Storage::disk('local')->assertExists("documents/{$file->hashName()}");

        $this->assertDatabaseHas('documents', [
            'name' => '第２回会議資料',
            'description' => '第２回会議にて配布した資料のPDFバージョンです',
            'path' => "documents/{$file->hashName()}",
            'size' => $filesize * 1024, // 単位 : バイト
            'extension' => 'pdf',
            'created_by' => $this->staff->id,
            'updated_by' => $this->staff->id,
            'is_public' => true,
            'is_important' => false,
            'schedule_id' => $schedule->id,
            'notes' => 'メモです'
        ]);
    }

    /**
     * @test
     */
    public function updateDocument()
    {
        $schedule = factory(Schedule::class)->create();

        $document = $this->documentsService->createDocument(
            '第２回会議資料',
            '第２回会議にて配布した資料のPDFバージョンです',
            UploadedFile::fake()->create('第２回.pdf', 1, 'application/pdf'),
            $this->staff,
            true,
            false,
            $schedule,
            'メモです'
        );

        $this->documentsService->updateDocument(
            $document,
            'updated filename',
            'updated description',
            $this->staff,
            false,
            true,
            null,
            'updated notes'
        );

        $this->assertDatabaseHas('documents', [
            'name' => 'updated filename',
            'description' => 'updated description',
            'created_by' => $this->staff->id,
            'updated_by' => $this->staff->id,
            'is_public' => false,
            'is_important' => true,
            'schedule_id' => null,
            'notes' => 'updated notes'
        ]);
    }
}

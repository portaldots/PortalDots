<?php

namespace Tests\Feature\Http\Controllers\Staff\Documents;

use App\Eloquents\Document;
use App\Eloquents\Permission;
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
     * @var Document
     */
    private $document;

    /**
     * @var User
     */
    private $staff;

    public function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        $this->documentsService = App::make(DocumentsService::class);

        $this->document = $this->documentsService->createDocument(
            '配布資料',
            null,
            UploadedFile::fake()->create('第1版.pdf', 1, 'application/pdf'),
            true,
            false,
            null
        );
        $this->documentsService->updateDocument(
            $this->document,
            '配布資料',
            null,
            UploadedFile::fake()->create('第2版.pdf', 1, 'application/pdf'),
            true,
            false,
            null
        );

        $this->staff = factory(User::class)->states('staff')->create();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 権限があれば過去の版をダウンロードできる()
    {
        Permission::create(['name' => 'staff.documents.read']);
        $this->staff->syncPermissions(['staff.documents.read']);

        $version = $this->document->versions()->where('version', 1)->firstOrFail();

        $response = $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->get(route('staff.documents.versions.show', ['document' => $this->document, 'version' => $version]));

        $response->assertOk();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 権限がない場合は過去の版をダウンロードできない()
    {
        $version = $this->document->versions()->where('version', 1)->firstOrFail();

        $response = $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->get(route('staff.documents.versions.show', ['document' => $this->document, 'version' => $version]));

        $response->assertForbidden();
    }
}

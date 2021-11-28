<?php

namespace Tests\Feature\Exports;

use App\Eloquents\Document;
use App\Eloquents\User;
use App\Exports\DocumentsExport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

class DocumentsExportTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var DocumentsExport
     */
    private $documentsExport;

    /**
     * @var User
     */
    private $user;

    /**
     * @var Document
     */
    private $document;

    public function setUp(): void
    {
        parent::setUp();

        $this->documentsExport = App::make(DocumentsExport::class);

        $this->user = factory(User::class)->create();

        $this->document = factory(Document::class)->create([
            'name' => '見たくなる資料',
            'description' => '第5回井戸端会議で配布した資料です。',
            'path' => 'documents/idobata.pdf',
            'size' => 64,
            'extension' => 'pdf',
            'is_public' => true,
            'is_important' => true,
        ]);
    }

    /**
     * @test
     */
    public function map_配布資料の情報のフォーマットが正常に行われる()
    {
        $this->assertEquals(
            [
                $this->document->id,
                '見たくなる資料',
                'idobata.pdf',
                64,
                'pdf',
                '第5回井戸端会議で配布した資料です。',
                'はい',
                'はい',
                $this->document->notes,
                $this->document->created_at,
                $this->document->updated_at,
            ],
            $this->documentsExport->map($this->document)
        );
    }
}

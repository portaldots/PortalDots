<?php

namespace Tests\Feature\GridMakers;

use App\Eloquents\Document;
use App\GridMakers\DocumentsGridMaker;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

class DocumentsGridMakerTest extends TestCase
{
    /**
     * @var DocumentsGridMaker
     */
    private $documentsGridMaker;

    public function setUp(): void
    {
        parent::setUp();

        $this->documentsGridMaker = App::make(DocumentsGridMaker::class);
    }

    /**
     * @test
     */
    public function map()
    {
        $document = factory(Document::class)->make([
            'extension' => 'pdf',
            'created_at' => '2020-02-02 02:02:02',
            'updated_at' => '2020-02-02 02:02:02',
        ]);

        $result = $this->documentsGridMaker->map($document);

        $this->assertSame('PDF', $result['extension']);
        $this->assertSame('2020/02/02 02:02:02', $result['created_at']);
        $this->assertSame('2020/02/02 02:02:02', $result['updated_at']);
    }
}

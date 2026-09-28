<?php

namespace Tests\Feature\GridMakers;

use App\Eloquents\Document;
use App\Eloquents\DocumentVersion;
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

    #[\PHPUnit\Framework\Attributes\Test]
    public function map()
    {
        $document = factory(Document::class)->make([
            'extension' => 'pdf',
            'audience' => 'selected',
            'created_at' => '2020-02-02 02:02:02',
            'updated_at' => '2020-02-02 02:02:02',
        ]);
        $document->setRelation('versions', collect([
            new DocumentVersion(['version' => 2]),
            new DocumentVersion(['version' => 1]),
        ]));

        $result = $this->documentsGridMaker->map($document);

        $this->assertSame('PDF', $result['extension']);
        $this->assertSame('selected', $result['audience']);
        $this->assertSame('2020/02/02 02:02:02', $result['created_at']);
        $this->assertSame('2020/02/02 02:02:02', $result['updated_at']);
        $this->assertSame(2, $result['version']);
    }
}

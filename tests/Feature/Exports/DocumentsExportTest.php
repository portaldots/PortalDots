<?php

namespace Tests\Feature\Exports;

use App\Eloquents\Document;
use App\Eloquents\Schedule;
use App\Eloquents\User;
use App\Exports\DocumentsExport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
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
     * @var Schedule
     */
    private $schedule;

    /**
     * @var Document
     */
    private $document;

    public function setUp(): void
    {
        parent::setUp();

        $this->documentsExport = App::make(DocumentsExport::class);

        $this->user = factory(User::class)->create();

        $this->schedule = factory(Schedule::class)->create([
            'name' => '第5回井戸端会議',
            'place' => '井戸の横',
        ]);

        $this->document = factory(Document::class)->create([
            'name' => '見たくなる資料',
            'description' => '第5回井戸端会議で配布した資料です。',
            'path' => 'documents/idobata.pdf',
            'size' => 64,
            'extension' => 'pdf',
            'is_public' => true,
            'is_important' => true,
            'schedule_id' => $this->schedule->id,
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
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
                "第5回井戸端会議(ID:{$this->schedule->id})",
                '第5回井戸端会議で配布した資料です。',
                'はい',
                'はい',
                $this->document->notes,
                $this->document->created_at,
                "{$this->user->name}(ID:{$this->user->id},{$this->user->student_id})",
                $this->document->updated_at,
                "{$this->user->name}(ID:{$this->user->id},{$this->user->student_id})",
            ],
            $this->documentsExport->map($this->document)
        );
    }
}

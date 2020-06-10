<?php

namespace Tests\Feature\Http\Controllers\Staff\Documents;

use App\Eloquents\Document;
use App\Eloquents\User;
use App\Services\Documents\DocumentsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Mockery;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class StoreActionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var User
     */
    private $staff;

    public function setUp(): void
    {
        parent::setUp();
        $this->staff = factory(User::class)->states('staff')->create();
    }

    /**
     * @test
     */
    public function DocumentsServiceのcreateFormが呼び出される()
    {
        Storage::fake('local');

        $filesize = 1;  // 単位 : KiB
        $file = UploadedFile::fake()->create('配布資料.pdf', $filesize, 'application/pdf');

        $document = factory(Document::class)->create([
            'path' => "documents/{$file->hashName()}.pdf",
            'size' => $filesize * 1024, // 単位 : バイト
            'extension' => 'pdf',
        ]);

        $this->mock(DocumentsService::class, function ($mock) use ($document) {
            $mock->shouldReceive('createDocument')->once()->with(
                'document name',
                'document description',
                Mockery::any(),
                Mockery::on(function ($arg) {
                    return $this->staff->id === $arg->id && $this->staff->name === $arg->name;
                }),
                false,
                true,
                null,
                'notes'
            )->andReturn($document);
        });

        $responce = $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->post(route('staff.documents.store'), [
                'name' => 'document name',
                'description' => 'document description',
                'file' => $file,
                'is_public' => '0',
                'is_important' => '1',
                'schedule_id' => null,
                'notes' => 'notes',
            ]);

        $responce->assertSessionHasNoErrors();

        $responce->assertRedirect(route('staff.documents.create'));
    }
}

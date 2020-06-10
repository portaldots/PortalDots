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

class UpdateActionTest extends TestCase
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
    public function DocumentsServiceのupdateFormが呼び出される()
    {
        $document = factory(Document::class)->create();

        $this->mock(DocumentsService::class, function ($mock) use ($document) {
            $mock->shouldReceive('updateDocument')->once()->with(
                $document,
                'document name',
                'document description',
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
            ->patch(route('staff.documents.update', ['document' => $document]), [
                'name' => 'document name',
                'description' => 'document description',
                'is_public' => '0',
                'is_important' => '1',
                'schedule_id' => null,
                'notes' => 'notes',
            ]);

        $responce->assertSessionHasNoErrors();

        $responce->assertRedirect(route('staff.documents.edit', ['document' => $document]));
    }
}

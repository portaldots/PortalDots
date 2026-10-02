<?php

namespace Tests\Feature\Http\Controllers\Staff\Documents\Approvals;

use App\Eloquents\Circle;
use App\Eloquents\Document;
use App\Eloquents\Permission;
use App\Eloquents\User;
use App\Services\Documents\DocumentApprovalsService;
use App\Services\Documents\DocumentsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DestroyActionTest extends TestCase
{
    use RefreshDatabase;

    private User $staff;
    private Document $document;
    private Circle $circle;

    public function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');

        $this->staff = factory(User::class)->states('staff')->create();
        Permission::create(['name' => 'staff.documents.edit']);
        $this->staff->syncPermissions(['staff.documents.edit']);

        $this->document = App::make(DocumentsService::class)->createDocument(
            '配布資料',
            null,
            UploadedFile::fake()->create('第１版.pdf', 1, 'application/pdf'),
            true,
            false,
            null,
            'everyone',
            [],
            [],
            $this->staff
        );
        $this->circle = factory(Circle::class)->create();
        App::make(DocumentApprovalsService::class)
            ->requestForCircles($this->document, [$this->circle->id], $this->staff);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 確認依頼を取り消せる()
    {
        $response = $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->delete(route('staff.documents.approvals.destroy', [
                'document' => $this->document,
                'circle' => $this->circle,
            ]));

        $response->assertRedirect(route('staff.documents.edit', ['document' => $this->document]));
        $this->assertDatabaseMissing('document_approvals', [
            'document_id' => $this->document->id,
            'circle_id' => $this->circle->id,
        ]);
    }
}

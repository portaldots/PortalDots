<?php

namespace Tests\Feature\Http\Controllers\Staff\Documents\Approvals;

use App\Eloquents\Circle;
use App\Eloquents\Document;
use App\Eloquents\DocumentApproval;
use App\Eloquents\Permission;
use App\Eloquents\User;
use App\Services\Documents\DocumentsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StoreActionTest extends TestCase
{
    use RefreshDatabase;

    private User $staff;
    private Document $document;

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
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 閲覧可能な企画へ確認を依頼できる()
    {
        $circle = factory(Circle::class)->create();

        $response = $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->post(route('staff.documents.approvals.store', ['document' => $this->document]), [
                'circles' => [$circle->id],
            ]);

        $response->assertRedirect(route('staff.documents.edit', ['document' => $this->document]));
        $this->assertDatabaseHas('document_approvals', [
            'document_id' => $this->document->id,
            'circle_id' => $circle->id,
            'status' => DocumentApproval::STATUS_PENDING,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function この配布資料を閲覧できない企画へは依頼できない()
    {
        $viewableCircle = factory(Circle::class)->create();
        $document = App::make(DocumentsService::class)->createDocument(
            '限定公開の配布資料',
            null,
            UploadedFile::fake()->create('第１版.pdf', 1, 'application/pdf'),
            true,
            false,
            null,
            'selected',
            [],
            [$viewableCircle->id],
            $this->staff
        );
        $notViewableCircle = factory(Circle::class)->create();

        $response = $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->post(route('staff.documents.approvals.store', ['document' => $document]), [
                'circles' => [$notViewableCircle->id],
            ]);

        $response->assertSessionHasErrors(['circles.0']);
        $this->assertDatabaseMissing('document_approvals', [
            'document_id' => $document->id,
            'circle_id' => $notViewableCircle->id,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function 権限がないスタッフは依頼できない()
    {
        $staffWithoutPermission = factory(User::class)->states('staff')->create();
        $circle = factory(Circle::class)->create();

        $response = $this->actingAs($staffWithoutPermission)
            ->withSession(['staff_authorized' => true])
            ->post(route('staff.documents.approvals.store', ['document' => $this->document]), [
                'circles' => [$circle->id],
            ]);

        $response->assertForbidden();
    }
}

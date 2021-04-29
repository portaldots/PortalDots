<?php

namespace Tests\Feature\Http\Controllers\Staff\Documents;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\Eloquents\Document;
use App\Eloquents\Permission;
use App\Eloquents\User;

class ShowActionTest extends TestCase
{
    use RefreshDatabase;

    /** @var Document */
    private $document;

    /** @var User */
    private $staff;

    public function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        // 配布資料
        $file = UploadedFile::fake()->create('ファイル.pdf', 1);
        $this->document = factory(Document::class)->create([
            'path' => $file->store('documents'),
            'size' => $file->getSize(),
            'extension' => $file->getClientOriginalExtension(),
        ]);

        // スタッフ
        $this->staff = factory(User::class)->states('staff')->create();
    }

    /**
     * @test
     */
    public function ダウンロードできる()
    {
        Permission::create(['name' => 'staff.documents.read']);
        $this->staff->syncPermissions(['staff.documents.read']);

        $response = $this->actingAs($this->staff)
            ->withSession(['staff_authorized' => true])
            ->get(route('staff.documents.show', [
                'document' => $this->document
            ]));

        $response->assertOk();
    }

    /**
     * @test
     */
    public function 権限がない場合はダウンロードできない()
    {
        $response = $this->actingAs(factory(User::class)->create())
            ->get(route('staff.documents.show', [
                'document' => $this->document
            ]));

        $response->assertForbidden();
    }

    /**
     * @test
     */
    public function スタッフ以外はダウンロードできない()
    {
        $response = $this->actingAs(factory(User::class)->create())
            ->get(route('staff.documents.show', [
                'document' => $this->document
            ]));

        $response->assertForbidden();
    }
}
